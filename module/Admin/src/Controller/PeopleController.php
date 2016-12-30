<?php

/** module/Admin/src/Controller/PeopleController */

namespace InterpretersOffice\Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use InterpretersOffice\Form\PersonForm;

use Doctrine\ORM\EntityManagerInterface;

use InterpretersOffice\Entity;

/**
 * controller for admin/people.
 */
class PeopleController extends AbstractActionController
{

    /**
     * entity manager.
     *
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * constructor.
     *
     * @param EntityManagerInterface $entityManager
     *
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * index action.
     *
     * @return ViewModel
     */
    public function indexAction()
    {
        return new ViewModel(['title' => 'people']);
    }

    /**
     * adds a Person entity to the database
     */
    public function addAction()
    {
        $viewModel = (new ViewModel())
                ->setTemplate('interpreters-office/admin/people/form.phtml');
        $form = new PersonForm($this->entityManager, ['action' => 'create']);
        $viewModel->setVariables(['form' => $form,'title' => 'add a person']);
        $request = $this->getRequest();
        $entity = new Entity\Person();
        $form->bind($entity);
        if ($request->isPost()) {
            $form->setData($request->getPost());
            if (! $form->isValid()) {
                return $viewModel;
            }
            $this->entityManager->persist($entity);
            $this->entityManager->flush();
            $this->flashMessenger()->addSuccessMessage(
                sprintf(
                    'The person <strong>%s %s</strong> has been added to the database',
                    $entity->getFirstname(),
                    $entity->getLastname()
                )
            );
            $this->redirect()->toRoute('people');
        }
        return $viewModel;
    }

    /**
     * updates a Person entity
     */
    public function editAction()
    {
        $viewModel = (new ViewModel())
                ->setTemplate('interpreters-office/admin/people/form.phtml')
                ->setVariable('title', 'edit a person');
        $id = $this->params()->fromRoute('id');
        if (! $id) { // get rid of this, since it will otherwise be 404?
            return $viewModel->setVariables(['errorMessage' => 'invalid or missing id parameter']);
        }
        $entity = $this->entityManager->find('InterpretersOffice\Entity\Person', $id);
        if (! $entity) {
            return $viewModel->setVariables(['errorMessage' => "person with id $id not found"]);
        } else {
            // judges and interpreters are special cases
            if (is_subclass_of($entity, Entity\Person::class)) {
                return $this->redirectToFormFor($entity);
            }
            $viewModel->id = $id;
        }
        $form = new PersonForm($this->entityManager, ['action' => 'update']);
        $form->bind($entity);
        $viewModel->form = $form;

        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setData($request->getPost());
            if (! $form->isValid()) {
                return $viewModel;
            }
            $this->entityManager->flush();
            $this->flashMessenger()
                  ->addSuccessMessage(sprintf(
                      "The person <strong>%s %s</strong> has been updated.",
                      $entity->getFirstname(),
                      $entity->getLastname()
                  ));
            $this->redirect()->toRoute('people');
        }

        return $viewModel;
    }

    /**
     * redirects to the page with specialized form for the Person subclass.
     *
     * When they load /admin/people/edit/xxx, Doctrine will try to find a Person
     * whose id is xxx even where if entity is subclassed -- e.g.,
     * is a Judge or Interpreter entity -- which would result in loading the wrong form.
     * Our front end should not expose this url, but if anyone should somehow stumble
     * into it or explicitly load the url, this is how we handle it. our routing
     * and class-naming conventions make it possible to compute the url to which
     * we should redirect.
     *
     * @param Entity\Person $entity
     *
     */
    public function redirectToFormFor(Entity\Person $entity)
    {

        $class = get_class($entity);
        $base  = substr($class, strrpos($class, '\\') + 1);
        $route = strtolower($base).'s/edit';
        $this->redirect()->toRoute($route, ['id' => $entity->getId()]);
    }
}
