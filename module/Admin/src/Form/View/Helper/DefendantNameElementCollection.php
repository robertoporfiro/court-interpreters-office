<?php /** module/Admin/src/Form/View/Helper/DefendantNameElementCollection.php */

namespace InterpretersOffice\Admin\Form\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Zend\Form\Element\Collection;

/**
 * helper for rendering defendantNames
 */
class DefendantNameElementCollection extends AbstractHelper
{
    /**
     * markup template
     * 
     * @var string
     */
    protected $template = <<<TEMPLATE
        <li class="list-group-item defendant py-1">
            <input name="event[defendantNames][%d][defendantName]" type="hidden" value="%d">
            <span class="align-middle">%s</span>            
            <button class="btn btn-warning btn-sm btn-remove-item float-right" title="remove this defendant">
            <span class="fas fa-times" aria-hidden="true"></span>
            <span class="sr-only">remove this defendant
            </button>
        </li>           
TEMPLATE;
    
    /**
     * invoke
     * 
     * @param Collection $collection
     * @return string
     */
    public function __invoke(Collection $collection)
    {
        return $this->render($collection);
    }

    /**
     * renders markup
     * 
     * @param Collection $collection
     * @return string
     */
    public function render(Collection $collection)
    {
        
        if (! $collection->count()) { return ''; }
        // to do: deal with possible undefined $form
        $form = $this->getView()->form;
        $entity = $form->getObject();
        $defendantNames = $entity->getDefendantNames();
        $markup = '';        
        foreach ($defendantNames as $i => $defendantEvent) {
            $defendant = $defendantEvent->getDefendant();
            $name = $defendant->getSurnames().', '.$defendant->getGivenNames();
            $markup .= sprintf($this->template,$i,$defendant->getId(),$name);          
        }
        return $markup;
    }
    
    /**
     * gets template
     * 
     * @return string
     */
    public function getTemplate()
    {
        return $this->template;
    }
    
    /**
     * renders defendantNames fieldset from array data
     * 
     * @param array $data
     * @return string
     */
    public function fromArray(Array $data)
    {
        if (! isset($data['name'])) {
            $data['name'] = '__NAME__';
        }
        $markup = sprintf($this->template,$data['index'],
                $data['id'],$data['name']);
        return $markup;
    }
    
    /**
     * input filter spec for xhr/defendantName template helper
     * 
     * @return Array
     */
    public function getInputFilterSpecification()
    {
         return 
         [   
            'id' => [
                'name'=> 'id',
                'required' => true,
                'allow_empty' => false,
                'validators' => [
                    ['name'=>'Digits'],
                ],
            ],
            'index' => [
                'name' => 'index',
                'required' => true,
                'allow_empty' => false,
                'validators' => [
                    ['name'=>'Digits'],
                ]
            ],
            'name' => [
                'name' => 'name',
                'required' => false,
                'allow_empty' => true,
                'validators' => [
                    [ 'name'=>'StringLength',
                        'options' => [
                            'max' => 152,
                            'min' => 5,
                        ],
                    ],
                ],
            ],
        ];
    }
}
