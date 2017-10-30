<?php
/**
 * module/Admin/src/Controller/LanguagesController.php.
 */

namespace InterpretersOffice\Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Doctrine\ORM\EntityManagerInterface;
use Zend\Authentication\AuthenticationServiceInterface;

use InterpretersOffice\Admin\Form;

use InterpretersOffice\Entity;

/**
 *  EventsController
 *
 */

/*
 SELECT e.id, e.date, e.time, t.name type, l.name language, 
 COALESCE(j.lastname, a.name) AS judge, p.name AS place, 
 COALESCE(s.lastname,as.name) submitter, submission_datetime FROM events e 
 JOIN event_types t ON e.eventType_id = t.id 
 JOIN languages l ON e.language_id = l.id 
 LEFT JOIN people j ON j.id = e.judge_id 
 LEFT JOIN anonymous_judges a ON a.id = e.anonymous_judge_id 
 LEFT JOIN people s ON e.submitter_id = s.id
 LEFT JOIN hats AS `as` ON e.anonymous_submitter_id = as.id
 LEFT JOIN locations p ON e.location_id = p.id;
 */
class EventsController extends AbstractActionController
{
    
    /**
     * entity manager
     * 
     * @var EntityManagerInterface
     */
    protected $entityManager;
    
    /**
     * authentication service
     * 
     * @var AuthenticationServiceInterface 
     */
    protected $auth;
    
    /**
     * constructor
     *
     * @param EntityManagerInterface $em
     * @param AuthenticationServiceInterface $auth
     */
    public function __construct(EntityManagerInterface $em, 
            AuthenticationServiceInterface $auth)
    {
        $this->entityManager = $em;
        $this->auth = $auth;
    }
    /**
     * index action
     *
     */
    public function indexAction()
    {
       
        return ['title' => 'schedule'];
    }

    /**
     * adds a new event
     */
    public function addAction()
    {
        
        
        $form = new Form\EventForm(
            $this->entityManager,
            [   'action' => 'create',
                'auth_user_role'=> $this->auth->getIdentity()->role,
                'object' => null,
            ]
        );

        $request = $this->getRequest();
        $form
             ->setAttribute('action', $request->getRequestUri());
        $event = new Entity\Event();
        
        $form->bind($event);
        // test
        $shit = $form->get("event");
        $shit->get("date")->setValue('10/27/2017');
        $shit->get("time")->setValue('10:00 am');
        $shit->get("judge")->setValue(948);
        $shit->get("language")->setValue(62);
        $shit->get("parent_location")->setValue(6);
        $shit->get("location")->setValue(11);
        $shit->get("eventType")->setValue(1);
        $shit->get("docket")->setValue("2016-CR-0345");
        $shit->get("anonymousSubmitter")->setValue("6");
        $shit->get("submission_date")->setValue("10/24/2017");
        $shit->get("submission_time")->setValue("10:17 am");
        $shit->get("submission_datetime")->setValue('2017-10-24 10:17:00');
        // end test
        $viewModel = (new ViewModel())
            ->setTemplate('interpreters-office/admin/events/form')
            ->setVariables([                
                'form'  => $form,
                ]);
        if ($request->isPost()) {
            $data = $request->getPost();
            //printf('<pre>%s</pre>',print_r($data->get('event'),true)); return false;
            $this->preValidate($data,$form);
            $form->setData($data);
            //printf('<pre>%s</pre>',print_r($data->get('event'),true)); return false;
            if (! $form->isValid()) {
                echo "validation failed ... ";
                //var_dump($form->getMessages()['event']);
                return $viewModel;
            } else {
              
                echo "validation OK... ";
                /* // shit is not working here!
                $collection = $event->getInterpretersAssigned();
                echo $collection->count(), " is the number of elements in the entity collection!...";
                $shit = $form->get('event')->get('interpretersAssigned');
                echo gettype($shit), " ... ";
                if (is_object($shit)) {
                    echo get_class($shit);
                    echo " is the class, and number of items is: ", count($shit);
                }
                echo "<br>"; $defendantCollection = $event->getDefendants();
                echo "number of defendants is: ",$defendantCollection->count();
                return false;
                //$this->postValidate($event,$form);
                //\Doctrine\Common\Util\Debug::dump($event);
                 */
                $this->entityManager->persist($event);
                $this->entityManager->flush();
                echo "YAY!!!!!!";
            }
            
        }
        return $viewModel;
    }
    
    protected function preValidate(\Zend\Stdlib\Parameters $data, 
            Form\EventForm $form)
    {
        $event = $data->get('event');
        if (!$event['judge'] && empty($event['anonymousJudge'])) {
            $validator = new \Zend\Validator\NotEmpty([
                'messages' => ['isEmpty' => "judge is required"],
                'break_chain_on_failure' => true,
            ]);
            $judge_input = $form->getInputFilter()->get('event')->get('judge');
            $judge_input->setAllowEmpty(false);
            $judge_input->getValidatorChain()->attach($validator);
        }
        if (empty($event['submitter']) && empty($event['anonymousSubmitter'])) {
            $validator = new \Zend\Validator\NotEmpty([
                'messages' => ['isEmpty' => "identity or description of submitter is required"],
                'break_chain_on_failure' => true,
            ]);
            $submitter_input = $form->getInputFilter()->get('event')->get('submitter');
            $submitter_input->setAllowEmpty(false);
            $submitter_input->getValidatorChain()->attach($validator);
            
        }
       // $shit_changed = false;
        // if NO submitter but YES anonymous submitter
        if (empty($event['submitter']) && !empty($event['anonymousSubmitter'])) {
            $event['submitter'] = null;
           // $shit_changed = true;
        // if anonymous submitter AND submitter, make anonymous NULL
        } elseif (!empty($event['anonymousSubmitter'])&& !empty($event['submitter'])) {
            $event['anonymousSubmitter'] = null;
           // $shit_changed = true;
        }
        if (!empty($event['submission_date']) && !empty($event['submission_time'])) {
            
            $event['submission_datetime'] = "$event[submission_date] $event[submission_time]";
            //exit(" I FUCKING SET THE SHIT: ".$event['submission_datetime']);
        }
        //if ($shit_changed) { // because $event is a copy, not a reference!
            $data->set('event',$event);
        //}
        
        
    }

    /**
     * edits an event
     *
     *
     */
    public function editAction()
    {
        $id = $this->params()->fromRoute('id');
        $event = $this->entityManager->find(Entity\Event::class,$id);
        if (! $event) {
             return (new ViewModel())
            ->setTemplate('interpreters-office/admin/events/form')
            ->setVariables([               
                'errorMessage'  => 
                "event with id $id was not found in the database."
             ]);
        }
        $form = new Form\EventForm(
            $this->entityManager,
            ['action' => 'update','object'=>$event,]
        );
        
        $request = $this->getRequest();
        $form->setAttribute('action', $request->getRequestUri());        
        $form->bind($event);
        //$e = $form->get('event')->get('eventType');
        //$renderer = $this->getEvent()->getApplication()->getServiceManager()->get("ViewRenderer");
        //$html = $renderer->formElement($e);
        //echo $renderer->escapeHtml($html);
        
        if ($this->getRequest()->isPost()) {
            $data = $request->getPost();
            $this->preValidate($data,$form);
            $form->setData($data);
            if ($form->isValid()) {
                 printf("<pre><%s</pre>",print_r($_POST['event'],true));
                try { 
                    $this->entityManager->flush();
                    echo "yay! shit is valid and has been saved.";
                } catch (\Exception $e) {
                    echo $e->getMessage();
                }
                
            } else {
                echo "shit is NOT valid...";
                printf("<pre><%s</pre>",print_r($form->getMessages(),true));
                printf("<pre><%s</pre>",print_r($_POST['event'],true));
            }
        }
        
        $viewModel = (new ViewModel())
            ->setTemplate('interpreters-office/admin/events/form')
            ->setVariables([               
                'form'  => $form,
             ]);

        return $viewModel;
    }
    
    /**
     * generates markup for an interpreter
     * 
     * @return Zend\Http\PhpEnvironment\Response
     * @throws \RuntimeException
     */
    public function interpreterTemplateAction()
    {
        $helper = new Form\View\Helper\InterpreterElementCollection();
        $factory = new \Zend\InputFilter\Factory();
        $inputFilter = $factory->createInputFilter(                
            $helper->getInputFilterSpecification()
        );
        $data = $this->params()->fromQuery();
        $inputFilter->setData($data);
        if (! $inputFilter->isValid()) {
            throw new \RuntimeException(
                "bad input parameters: "
                    .json_encode($inputFilter->getMessages(),\JSON_PRETTY_PRINT)
            );
        }        
        $html = $helper->fromArray($data);
        return $this->getResponse()->setContent($html);
    }
    
    
    /**
     * gets interpreter options for populating select
     * 
     * @return JsonModel
     */
    public function interpreterOptionsAction()
    {
        /** @var  \InterpretersOffice\Entity\Repository\InterpreterRepository $repository */
        $repository = $this->entityManager->getRepository(Entity\Interpreter::class);        
        $language_id = $this->params()->fromQuery('language_id');
        if (! $language_id) {
            $result = ['error' => 'missing language id parameter'];
        } else {
            $result = $repository->getInterpreterOptionsForLanguage($language_id);
        }
        return new JsonModel($result);        
    }
}
