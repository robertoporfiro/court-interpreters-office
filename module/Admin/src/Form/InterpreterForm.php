<?php
/**
 * module/Admin/src/Form/InterpreterForm.php.
 */

namespace InterpretersOffice\Admin\Form;

use InterpretersOffice\Form\PersonForm;
use InterpretersOffice\Admin\Form;

/**
 * extends PersonForm.
 *
 * @see PersonForm
 */
class InterpreterForm extends PersonForm
{

    /**
     * the name of our form.
     *
     * @var string 'judge-form'
     */
    protected $formName = 'interpreter-form';

    /**
     * name of Fieldset class to instantiate and add to the form.
     *
     * @var string
     */
    protected $fieldsetClass = Form\InterpreterFieldset::class;
}
