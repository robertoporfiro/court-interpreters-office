<?php
/** module/Admin/src/Form/EventForm.php */

namespace InterpretersOffice\Requests\Form;

use InterpretersOffice\Admin\Form\AbstractEventFieldset;
use InterpretersOffice\Form\ObjectManagerAwareTrait;


use Zend\Form\Form as ZendForm;
use Doctrine\Common\Persistence\ObjectManager;
use InterpretersOffice\Form\CsrfElementCreationTrait;

/**
 * form for Request entity
 *
 */
class RequestForm extends ZendForm
{

     use CsrfElementCreationTrait;


     /**
     * name of Fieldset class to instantiate and add to the form.
     *
     *
     * @var string
     */
    protected $fieldsetClass = RequestFieldset::class;

    /**
     * name of the form
     *
     * @var string
     */
    protected $formName = 'request-form';

    /**
     * date/time properties
     *
     * @var array
     */
    protected $datetime_props = ['date','time',];



     /**
     * constructor.
     *
     * @param ObjectManager $objectManager
     * @param array         $options
     */
    public function __construct(ObjectManager $objectManager, $options = null)
    {
        parent::__construct($this->formName, $options);
        $fieldset = new $this->fieldsetClass($objectManager, $options);
        $this->add($fieldset);
        
        if ("update" == $this->options['action']) {
            $this->add([
                'type' => 'Hidden',
                'name' => 'modified',
                'attributes' => ['id' => 'modified'],
            ]);
        }
        $this->addCsrfElement();
    }


}