<?php

/** module/InterpretersOffice/src/Form/PersonForm.php */

namespace InterpretersOffice\Form;

use Laminas\Form\Form as LaminasForm;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Form for a Person entity.
 */
class PersonForm extends LaminasForm
{
    use CsrfElementCreationTrait;

    /**
     * name of the form.
     *
     * @var string
     */
    protected $formName = 'person-form';

    /**
     * name of Fieldset class to instantiate and add to the form.
     *
     * the idea is that subclasses can override this with the classname
     * of a Fieldset that extends PersonFieldset
     *
     * @var string
     */
    protected $fieldsetClass = PersonFieldset::class;

    /**
     * constructor.
     *
     * @param ObjectManager $objectManager
     * @param array         $options
     */
    public function __construct(ObjectManager $objectManager, $options = null)
    {
        parent::__construct($this->formName, $options);
        $this->options = $options;
        $fieldset = new $this->fieldsetClass($objectManager, $options);
        $this->add($fieldset);
        $this->addCsrfElement();
    }
}
