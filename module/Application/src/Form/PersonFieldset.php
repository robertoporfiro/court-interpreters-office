<?php 

namespace Application\Form;

use Zend\Form\Fieldset;
use Zend\InputFilter\InputFilterProviderInterface;
use DoctrineModule\Persistence\ObjectManagerAwareInterface;
use Doctrine\Common\Persistence\ObjectManager;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;

use Application\Entity;

class PersonFieldset extends Fieldset implements InputFilterProviderInterface, ObjectManagerAwareInterface
{
	

	/** @var ObjectManager */
	protected $objectManager;

	protected $elements = [
		'lastname' => [
			'type' => 'Zend\Form\Element\Text',
			'name' => 'lastname',
			'options'=>[
				'label' => 'last name'
			],
		],
		'firstname' => [
			'type' => 'Zend\Form\Element\Text',
			'name' => 'firstname',
			'options'=>[
				'label' => 'first name',
			],
		],

	];

	public function __construct(ObjectManager $objectManager, $options = []) 
	{
		parent::__construct('person-fieldset',$options);
		$this->objectManager = $objectManager;
		foreach ($this->elements as $element) {
			$this->add($element);
		}

	}

		


	public function getInputFilterSpecification()
	{

		return [
			'lastname' => [
				'validators' => [

				],
				'filters' => [

				],
			],
			'firstname' => [
				'validators' => [
					[
						'name' => 'NotEmpty',
						'options' => [
							'messages' => [
								'isEmpty' => 'first name is required'
							],
						],
						'break_chain_on_failure' => true,
					],
					[
                        'name' => 'Application\Form\Validator\ProperName',
                        'options' => ['type' => 'first'],
                        
                    ],
				],
				'filters' => [

				],
			],


		];

	}
	public function setObjectManager(ObjectManager $objectManager) {
        $this->objectManager = $objectManager;
    }

    public function getObjectManager() {
        return $this->objectManager;
    }

}