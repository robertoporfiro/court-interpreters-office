<?php

/** module/Admin/src/Controller/UsersController.php */

namespace InterpretersOffice\Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Doctrine\ORM\EntityManagerInterface;
use InterpretersOffice\Entity\User;

use Zend\Permissions\Acl\AclInterface;

use Zend\EventManager\EventManagerInterface;

//use InterpretersOffice\Service\Authentication\AuthenticationAwareInterface;

use Zend\Authentication\AuthenticationService;

use Zend\Session\Container as Session;

use InterpretersOffice\Admin\Form\UserForm;
use InterpretersOffice\Entity;

/**
 * controller for admin/users.
 *
 * things we need to do here:
 *
 *   * supply a way to browse and edit existing users
 *   * add new user: encourage (require?) looking up existing person first.
 *     autocompletion?
 *    
 */
class UsersController extends AbstractActionController //implements AuthenticationAwareInterface

{

    /**
     * entity manager.
     *
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var AclInterface
     */
    protected $acl;

    /**
     * @var AuthenticationService
     * 
     */
    protected $auth;
    
    /**
     * the role of the currently authenticated user
     * @var string $auth_user_role
     */
    protected $auth_user_role;
    /**
     * constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)//, AclInterface $acl
    {
        $this->entityManager = $entityManager;
        $this->auth_user_role = (new Session('Authentication'))->role;
        
    }
    /**
     * implements AuthenticationAwareInterface
     * 
     * @param AuthenticationService $auth
     * 
     */
    public function setAuthenticationService(AuthenticationService $auth)
    {
        $this->auth = $auth;
    }


   /**
    * attaches event handlers
    * 
    * NOTE: we might take this out after all (not necessary)
    *  
    * https://mwop.net/blog/2012-07-30-the-new-init.html
    */
    public function setEventManager(EventManagerInterface $events)
    {
        
        $acl = $this->acl;
        // maybe not... to be continued
        $events->attach('update-role', function ($e) use ($acl) {
           
            // $params = $e->getParams();
            // etc
            
            }
        );
        return parent::setEventManager($events);
    }
    /**
     * add a new user
     */
    public function addAction()
    {
        $viewModel = new ViewModel(['title' => 'add a user']);
        $viewModel->setTemplate('interpreters-office/admin/users/form');
        $request = $this->getRequest();
        $form = new UserForm($this->entityManager,[
            'action'=>'create',
            'auth_user_role' => $this->auth_user_role,           
            ]
         );
        $viewModel->form = $form;
        /* populate the person fieldset???
        $user = new Entity\User();
        $user->setPerson($this->entityManager->find('InterpretersOffice\Entity\Interpreter', 3));
        $form->bind($user);
        echo $person->getFirstname();
        $form->get('user')->get('person')->setObject($user->getPerson());         
         */
        if ($request->isPost()) {
            $form->setData($request->getPost());
            if (!$form->isValid()) {
                echo "not valid.<pre>"; 
                print_r($form->getMessages());
                echo "</pre>";
                return $viewModel;
            }
        }
        return $viewModel;
    }

    /**
     * index action.
     *
     * @return ViewModel
     */
    public function indexAction()
    {
        //echo "it works"; return false;
        
        return new ViewModel(['title' => 'admin | users','role'=>$this->role]);
    }
}
