<?php /** module/Admin/src/Service/ReportService.php */

declare(strict_types=1);

namespace InterpretersOffice\Admin\Service;
use Laminas\InputFilter\Factory;
use Laminas\InputFilter\InputFilter;
use Laminas\Validator;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query\ResultSetMapping;
use Laminas\Paginator\Paginator as LaminasPaginator;
use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator as DoctrineAdapter;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;
use InterpretersOffice\Entity;

/*
"SELECT j.lastname AS judge, SDNY_DATE_DIFF(e.submission_date,e.submission_time,e.date, e.time) AS days_notice 
FROM InterpretersOffice\Entity\Event e INNER JOIN e.judge j INNER JOIN e.event_type t INNER JOIN t.category c WHERE c.category = 'in'
AND j.lastname = 'Castel' AND e.date >= '2020-06-01'"
*/

/**
 * generates reports
 */
class ReportService 
{
    
    /* SELECT l.name language, COUNT(ie.event_id) AS total FROM languages l 
        JOIN events e ON l.id = e.language_id 
        JOIN interpreters_events ie ON ie.event_id = e.id 
        GROUP BY l.name ORDER BY `total` DESC;
    */

    

    /*
 $qb->select(['l.name','SUM(CASE WHEN e.cancellation_reason IS NOT NULL THEN 1 ELSE 0 END) AS cancelled','COUNT(e.id) AS total'])
        ->from('InterpretersOffice\Entity\Event', 'e')->join('e.language','l',NULL)->groupBy('l.name');

    */
    const REPORT_USAGE_BY_LANGUAGE = 1;
    const REPORT_USAGE_BY_INTERPRETER = 2;
    const REPORT_CANCELLATIONS_BY_JUDGE = 3;
    const REPORT_BELATED_BY_JUDGE = 4;
    const REPORT_NON_SPANISH_WITH_LANGUAGE_CREDENTIAL = 5;

    /**
     * report id => label
     * @var array     
     */
    private static $reports = [
        self::REPORT_USAGE_BY_LANGUAGE => 'interpreter usage by language',
        self::REPORT_USAGE_BY_INTERPRETER => 'interpreter usage by interpreter',
        self::REPORT_CANCELLATIONS_BY_JUDGE => 'belated cancellations per judge',
        self::REPORT_BELATED_BY_JUDGE => 'belated in-court requests per judge',
        self::REPORT_NON_SPANISH_WITH_LANGUAGE_CREDENTIAL => 'non-Spanish events including language credential',
    ];

    /**
     * date ranges for form
     * @var array
     */
    private static $date_range_options = [
        'YTD' =>  'year to date',
        'QTD'=>   'current quarter to date',
        'PY'=>    'previous year',
        'PQ'=>    'previous quarter',
        'FYTD'=>  'fiscal year to date',
        'PFY'=>   'previous fiscal year',
        'CUSTOM'=>'custom...'
    ];

    /**
     * entity manager
     * @var EntityManagerInterface $em
     */
    private $em;
    
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function createReport(Array $options) {
        if (!isset($options['report'])) {
            throw new \RuntimeException(sprintf('missing report "report" option in %s',__FUNCTION__));
        }
        $from = new \DateTime($options['date-from']);
        $to = new \DateTime($options['date-to']);
        $qb = $this->em->createQueryBuilder();
        switch($options['report']) {
            case self::REPORT_USAGE_BY_LANGUAGE:                
                $data = $this->createLanguageUsageQuery($qb)
                    ->where($qb->expr()->between('e.date',':from',':to'))
                    ->andWhere('l.name != :bullshit')
                    ->setParameters([':from'=>$from, ':to' => $to, ':bullshit'=>'CART'])
                    ->getQuery()->getResult();
                $totals = ['completed' => 0,'cancelled'=> 0];
                foreach($data as $i => $record) {
                   $record['completed'] = $record['total'] - $record['cancelled'];
                   $totals['completed'] += $record['completed'];
                   $totals['cancelled'] += $record['cancelled'];
                   $data[$i] = $record;
                }
            break;

            case self::REPORT_USAGE_BY_INTERPRETER:
                $data = $this->createInterpreterUsageQuery($qb)
                ->where($qb->expr()->between('e.date',':from',':to'))
                    ->andWhere('l.name != :bullshit')
                    ->setParameters([':from'=>$from, ':to' => $to, ':bullshit'=>'CART'])
                    ->getQuery()->getResult();
            break;

            case self::REPORT_BELATED_BY_JUDGE:
                // this one is a little different
                try {
                    $data = $this->createBelatedInCourtByJudgeReport($from,$to);
                } catch (\Exception $e) {
                    return ['error' => $e->getMessage(),'report_type' => self::$reports[$options['report']], 'data'=> null];
                }

            break;

            case self::REPORT_NON_SPANISH_WITH_LANGUAGE_CREDENTIAL:
                $qb = $this->createNonSpanishReport($qb)
                ->andWhere($qb->expr()->between('e.date',':from',':to'))
                ->setParameters([':from'=>$from, ':to' => $to, ]);
                $query = $qb->getQuery();              
                $adapter = new DoctrineAdapter(new ORMPaginator($query));
                $paginator = new LaminasPaginator($adapter);        
                $paginator->setCurrentPageNumber($options['page']??1)->setItemCountPerPage(20);
                $data = $paginator->getCurrentItems();
                $pages = $paginator->getPages();

            break;
            default:
                $data = [];
        }

        return [            
            'from' => $from->format('Y-m-d'),
            'to' => $to->format('Y-m-d'),
            'totals' => $totals??[],
            'pages' => $pages ?? null,
            'report_type' => self::$reports[$options['report']],
            'data' => $data,
        ];
    }

    /**
     * reports non-Spanish events for date range
     * 
     * @param QueryBuilder $qb
     * @return QueryBuilder     
     */
    public function createNonSpanishReport(QueryBuilder $qb) : QueryBuilder
    {
       /*
       If you don't use this PARTIAL... syntax you get:
       "Not all identifier properties can be found in the ResultSetMapping: id"
       but if you DO use it, the resulting JS object is empty. Hence:
       */
        $qb->select(['PARTIAL e.{id}', 'e.date','e.time', 
            "CASE WHEN e.docket <> '' THEN SUBSTRING(e.docket, 3) ELSE '' END AS docket",
        'e.id',
         'COALESCE(j.lastname, aj.name) AS judge', 
            "CONCAT(i.lastname, ', ', i.firstname) AS interpreter",
            'l.name AS language','lc.abbreviation AS rating','t.name AS type',
            'CASE WHEN e.cancellation_reason IS NULL THEN false ELSE true END AS cancelled'
        ])->from('InterpretersOffice\Entity\Event', 'e')
        ->join('e.language','l')
        ->join('e.interpreterEvents','ie')
        ->join('ie.interpreter', 'i')
        ->join('i.interpreterLanguages','il','WITH','e.language = il.language')
        ->join('il.languageCredential','lc')
        ->join('e.event_type','t')->where("l.name <> 'Spanish'")
        ->leftJoin('e.judge','j')->leftJoin('e.anonymous_judge','aj')
        ->orderBy('e.date','ASC');

        return $qb;
        
    }

    /**
     * language-usage query
     * 
     * @param QueryBuilder $qb
     * @return QueryBuilder
     */
    public function createLanguageUsageQuery(QueryBuilder $qb) : QueryBuilder
    {
    /*
    SELECT l.name language, SUM(IF(c.category="in",1,0)) as `in-court`, SUM(IF(c.category = "out",1,0)) as `ex-court`, 
        COUNT(ie.event_id) AS total FROM languages l JOIN events e ON l.id = e.language_id 
        JOIN interpreters_events ie ON  ie.event_id = e.id JOIN event_types t ON e.event_type_id = t.id  
        JOIN event_categories c ON t.category_id = c.id  
        GROUP BY l.name ORDER BY `total` DESC LIMIT 20;
    */
        return $qb->select(['l.name AS language',
        'SUM(CASE WHEN e.cancellation_reason IS NOT NULL THEN 1 ELSE 0 END) AS cancelled',
        'SUM(CASE WHEN c.category = \'in\' THEN 1 ELSE 0 END) AS in_court',
        'SUM(CASE WHEN c.category = \'out\' THEN 1 ELSE 0 END) AS ex_court',
        'COUNT(ie.event) AS total'])
        ->from('InterpretersOffice\Entity\Event', 'e')
        ->join('e.interpreterEvents','ie')
        ->join('e.language','l')
        ->join('e.event_type','t')
        ->join('t.category','c')
        ->orderBy('total','DESC')
        ->groupBy('l.name');      
    }

    /**
     * belated in-court request query
     * 
     * @return array
     */
    public function createBelatedInCourtByJudgeReport(\DateTimeInterface $from, \DateTimeInterface $to): array
    {
        $expr = 'SDNY_DATE_DIFF(e.submission_date,e.submission_time,e.date, e.time)';
        $rsm = new ResultSetMapping();
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('judge','judge')
        ->addScalarResult('sub_1','sub_1')
        ->addScalarResult('sub_2','sub_2')
        ->addScalarResult('total','total');
        $expr = 'SDNY_DATE_DIFF(e.submission_date, e.submission_time, e.date, e.time)';
        $sql = "SELECT e.id event_id, j.lastname AS judge,     
        SUM(CASE WHEN $expr < 1 THEN 1 ELSE 0 END) AS sub_1,
        SUM(CASE WHEN $expr < 2 AND $expr >= 1 THEN 1 ELSE 0 END) AS sub_2,
        COUNT(e.id) AS total
        FROM events e JOIN people j ON j.id = e.judge_id 
        JOIN event_types t ON e.event_type_id = t.id 
        JOIN event_categories c ON t.category_id = c.id 
        WHERE c.category = 'in' 
        AND e.date BETWEEN '{$from->format('Y-m-d')}' AND '{$to->format('Y-m-d')}' 
        GROUP BY j.id ORDER BY judge";
        $query = $this->em->createNativeQuery($sql,$rsm);

        return $query->getResult();
    }

    public function createInterpreterUsageQuery(QueryBuilder $qb) : QueryBuilder {
        /* "SELECT CONCAT(i.lastname, ', ',i.firstname) AS interpreter, l.name AS language, 
         COUNT(ie.interpreter) AS events FROM InterpretersOffice\Entity\InterpreterEvent ie 
        JOIN ie.interpreter i JOIN ie.event e JOIN e.language l WHERE e.date > '2019-12-31' GROUP BY i, l"
        */
        return $qb->select([
            'i.id','h.name AS hat',
            "CONCAT(i.lastname, ', ',i.firstname) AS interpreter",
            'l.name AS language', 'COUNT(ie.interpreter) AS events',
        ])->from(Entity\InterpreterEvent::class,'ie')
        ->join('ie.interpreter','i')->join('ie.event','e')
        ->join('i.hat','h')
        ->join('e.language','l')->groupBy('i.id','l.name')->orderBy('events','DESC');

    }

    /**
     * gets report ids => labels
     * 
     * @return array
     */
    public function getReports() : array
    {
        return self::$reports;
    }

    /**
     * gets date-range options
     * 
     * @return array
     */
    public function getDateRangeOptions() : array
    {
        return self::$date_range_options;
    }

    /**
     * gets inputfilter
     * 
     * @return InputFilter
     */
    public function getInputFilter() : InputFilter
    {
        return (new Factory())->createInputFilter($this->getInputFilterSpecification());
    }


    /**
     * gets inputfilter specification
     * 
     * @return array
     */
    public function getInputFilterSpecification() : array
    {
        return [
            'report' => [
                'name' => 'report',
                'required' => true,
                'validators' => [
                    [
                        'name' => Validator\NotEmpty::class,
                        'options' => [
                            'messages' => [
                                Validator\NotEmpty::IS_EMPTY => "report type is required",
                            ]
                        ],
                        'break_chain_on_failure' => true,
                    ],
                    [
                        'name' => Validator\InArray::class,
                        'options' => [
                            'haystack' => array_keys(self::$reports),
                            'messages' => [
                                Validator\InArray::NOT_IN_ARRAY =>
                                 'invalid type: %value%',
                            ],
                        ],
                        'break_chain_on_failure' => true,
                    ],                   
                ],
                'filters' => [],
            ],
            'date-range' => [
                'name' => 'date-range',
                'required' => false, 
            ],
            'date-from' => [
                'name' => 'date-from',
                'required' => true,
                'validators' => [
                    [
                        'name' => Validator\NotEmpty::class,
                        'options' => [
                            'messages' => [
                                Validator\NotEmpty::IS_EMPTY => '"from" date is required',
                            ]
                        ],
                        'break_chain_on_failure' => true,
                    ],
                    [
                        'name' => 'Date',
                        'options' => [
                            'format'=>'m/d/Y',
                            'messages'=> ['dateInvalidDate'=>'date is invalid',]
                        ],
                        'break_chain_on_failure' => true,
                    ],
                ],
                'filters' => [],
            ],
            'page' => [
                'name' => 'page',
                'required' => false,
                'filters' => [
                    ['name'=>\Laminas\Filter\ToInt::class],
                ],
            ],
            'date-to' => [
                'name' => 'date-to',
                'required' => true,
                'validators' => [
                    [
                        'name' => Validator\NotEmpty::class,
                        'options' => [
                            'messages' => [
                                Validator\NotEmpty::IS_EMPTY => '"to" date is required',
                            ]
                        ],
                        'break_chain_on_failure' => true,
                    ],
                    [
                        'name' => 'Date',
                        'options' => [
                            'format'=>'m/d/Y',
                            'messages'=>['dateInvalidDate'=>'date is invalid']
                        ],
                        'break_chain_on_failure' => true,
                    ],
                    [
                        'name' => Validator\Callback::class,
                        'options' => [
                            'callBack' => function ($value, $context) {
                                $to = preg_replace('|(\d\d)/(\d\d)/(\d{4})|',"$3-$1-$2",$value);
                                $from = preg_replace('|(\d\d)/(\d\d)/(\d{4})|',"$3-$1-$2",$context['date-from']);
                                if (! preg_match('/\d{4}-\d\d-\d\d/',$from)) {
                                    return true; // not our problem
                                }
                                return $to >= $from;
                            },
                            'messages' => [
                                'callbackValue' => '"to" date cannot precede "from"',
                            ],
                        ],
                    ],
                ],
                'filters' => [],
            ],
        ];
    }
}
