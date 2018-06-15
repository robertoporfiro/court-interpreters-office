<?php
/**
 * module/InterpretersOffice/src/Controller/AccountController.php.
 */

namespace InterpretersOffice\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Doctrine\Common\Persistence\ObjectManager;
use InterpretersOffice\Entity;
use InterpretersOffice\Form\User\RegistrationForm;
use Zend\Authentication\AuthenticationServiceInterface;
use Zend\Form\FormInterface;

/**
 *  AccountController.
 *
 *  For registration, password reset and email verification.
 *  Very much incomplete.
 */

class AccountController extends AbstractActionController
{
    /**
     * objectManager instance.
     *
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * authentication service
     *
     * @var AuthenticationServiceInterface
     */
    protected $auth;
    /**
     * constructor.
     *
     * @param ObjectManager $objectManager
     * @param AuthenticationServiceInterface
     */
    public function __construct(ObjectManager $objectManager, AuthenticationServiceInterface $auth)
    {
        $this->objectManager = $objectManager;
        $this->auth = $auth;
    }

    /**
     * gets the 'submitter' role
     * @return Entity\Role
     */
    protected function getDefaultRole()
    {
        return $this->objectManager->getRepository(Entity\Role::class)
            ->findOneBy(['name' => 'submitter']);
    }

    /**
     * index action
     * @return ViewModel
     */
    public function indexAction()
    {
        return new ViewModel();
    }
    /**
     * partial validation
     *
     * @return JsonModel
     */
    public function validateAction()
    {
        $params = $this->params()->fromPost();

        if (! isset($params['user']) or ! isset($params['user']['person'])) {
            return new JsonModel(['valid'=>false,
                'error'=>'malformed input data']);
        }
        $shit = 'whatever';
        // it's a 3-step form. the first two are handled as partial validation
        $form_step = $this->params()->fromQuery('step');
        $form = new RegistrationForm($this->objectManager, [
            'action' => 'create','auth_user_role' => 'anonymous',
            ]);
        $validation_group = [
            'csrf',//????
            'user'=> [ 'person'=> array_keys($params['user']['person'])]
        ];
        $shit = $validation_group;
        if ($form_step == 'fieldset-hat') {
            $shit = $form->preValidate($params['user']);
            $validation_group['user'][] = 'judges';
        }
        $form->setValidationGroup($validation_group);

        $form->setData($params);
        if (! $form->isValid()) {
            //$messages = $form->getMessages()['user'];
            return new JsonModel([
                'validation_errors'=> $form->getFlattenedErrorMessages(),
                'debug'=>$shit]);
        }
        return new JsonModel(['valid'=>true, 'debug'=>$shit]);
    }

    /**
     * registers a new user account
     *
     * @return ViewModel
     */
    public function registerAction()
    {

        $form = new RegistrationForm($this->objectManager, [
            'action' => 'create','auth_user_role' => 'anonymous',
            ]);
        if (! $this->getRequest()->isPost()) {
            return new ViewModel(['form' => $form]);
        }
        // handle POST
        $user = new Entity\User();
        $request = $this->getRequest();
        $form->bind($user);
        $input = $request->getPost();
        $data = $input->get('user');

        $form->setData($input);
        if (! $form->isValid()) {
            return new JsonModel(
                ['validation_errors' => $form->getFlattenedErrorMessages()]
            );
        }
        try {

            $user->setRole($this->getDefaultRole());
            $this->objectManager->persist($user);
            $this->objectManager->persist($user->getPerson());
            $this->objectManager->flush();

        } catch (\Exception $e) {
            return new JsonModel(
                [   'validation_errors' => null,
                    'data'=>$data,
                    'status' => 'error',
                    'exception' => get_class($e),
                    'message'=> $e->getMessage(),
                ]
            );
        }

        // else ...
        return new JsonModel(
            ['validation_errors' => null, 'data'=>$data, 'status'=>'success']
        );
    }

    /**
     * email verification
     * s
     * @return ViewModel
     */
    public function verifyEmailAction()
    {
        return new ViewModel();
    }

    /**
     * handles password-reset requests
     *
     * @return ViewModel
     */
    public function requestPasswordAction()
    {

        return new ViewModel();
    }

    /**
     * handles actual resetting of the user's password
     *
     * @return ViewModel
     */
    public function resetPasswordAction()
    {
        return new ViewModel();
    }
    /**
     * edit account profile
     *
     * @return ViewModel
     */
    public function editAction()
    {
        if (! $this->auth->hasIdentity()) {
            $this->redirect()->toRoute('auth');
            return;
        }
        return new ViewModel();
    }
}
