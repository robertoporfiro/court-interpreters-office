<?php
/**
 * module/Requests/test/Controller/ScheduleUpdateManagerTest.php
 *
 */

namespace ApplicationTest\Controller;

use ApplicationTest\AbstractControllerTest;
use ApplicationTest\FixtureManager;
use ApplicationTest\FakeAuth;
use ApplicationTest\DataFixture;

/**
 * tests for ScheduleUpdateManager
 */
class ScheduleUpdateManagerTest extends AbstractControllerTest
{
    public function setUp()
    {
        parent::setUp();
        $this->cleanup();
        $container = $this->getApplicationServiceLocator();
        $eventManager = $container->get('SharedEventManager');

        $eventManager->attach(Listener\UpdateListener::class,'*',function($e) use ($container) {
            $container->get('log')->debug(
                "SHIT HAS BEEN TRIGGERED! {$e->getName()} is the event, calling ScheduleUpdateManager"
            );
            /** @var ScheduleUpdateManager $updateManager */
            $updateManager = $container->get(ScheduleUpdateManager::class);
            $updateManager->onUpdateRequest($e);

        });
        // $container = $this->getApplicationServiceLocator();
        $em = FixtureManager::getEntityManager();//$container->get("entity-manager");
        // $listener = $container->get('InterpretersOffice\Entity\Listener\UpdateListener');
        $resolver = $em->getConfiguration()->getEntityListenerResolver();
        $entityListener = $container->get('InterpretersOffice\Requests\Entity\Listener\RequestEntityListener');
        $entityListener->setLogger($container->get('log'));
        $auth = new \ApplicationTest\FakeAuth();
        $entityListener->setAuth($auth);
        $resolver->register($entityListener);
        $update_listener = $container->get('InterpretersOffice\Entity\Listener\UpdateListener');
        $update_listener->setAuth($auth);
        $resolver->register($update_listener);
        $fixtureExecutor = FixtureManager::getFixtureExecutor();
        $fixtureExecutor->execute(
            [
                new DataFixture\HatLoader(),
                new DataFixture\DefendantLoader(),
                new DataFixture\LocationLoader(),
                new DataFixture\JudgeLoader(),
                new DataFixture\LanguageLoader(),
                new DataFixture\EventTypeLoader(),
                new DataFixture\InterpreterLoader(),
                new DataFixture\UserLoader(),
                new DataFixture\RequestLoader(),
            ]
        );
        $request_repo = $em->getRepository('InterpretersOffice\Requests\Entity\Request');
        $result = $em->createQuery(
            'SELECT r FROM InterpretersOffice\Requests\Entity\Request r
            WHERE r.event IS NOT NULL'
        )->getResult();
        // debug
        //printf("\nfound %d requests on schedule in %s\n",count($result),__FUNCTION__);
        foreach($result as $r) {
            $event = $r->getEvent();
            $interpreters = $event->getInterpreters();
            //printf("\nfound %d interpreters on scheduled request in %s\n",count($interpreters),__FUNCTION__);
            //printf("\nlanguage is %s in %s\n",$event->getLanguage(),__FUNCTION__);
            if (! count($interpreters)) {
                $i = $em->createQuery('SELECT i FROM InterpretersOffice\Entity\Interpreter i WHERE i.lastname = :lastname')
                    ->setParameters(['lastname'=>'Somebody'])
                    ->getOneOrNullResult();
                $u = $em->createQuery('SELECT u FROM InterpretersOffice\Entity\User u WHERE u.username = :username')
                    ->setParameters(['username'=>'david'])
                    ->getOneOrNullResult();
                $ie = new \InterpretersOffice\Entity\InterpreterEvent($i,$event);
                $ie->setCreatedBy($u);
                $event->addInterpreterEvents(new \Doctrine\Common\Collections\ArrayCollection([$ie]));
            }
            $em->flush();

        }
        $this->em = $em;


    }
    public function cleanup()
    {
        $em = FixtureManager::getEntityManager();
        $result = $em->createQuery(
            'SELECT r FROM InterpretersOffice\Requests\Entity\Request r
            WHERE r.event IS NOT NULL'
        )->getResult();
        if (count($result)) {

            foreach ($result as $object) {
                $event = $object->getEvent();
                $em->remove($event);
                $em->remove($object);
            }
            $em->flush();
        }

    }
    public function testDataSetupSanity()
    {

        $em = $this->em;
        $request_repo = $em->getRepository('InterpretersOffice\Requests\Entity\Request');
        $result = $em->createQuery(
            'SELECT r FROM InterpretersOffice\Requests\Entity\Request r
            WHERE r.event IS NOT NULL'
        )->getResult();
        $this->assertGreaterThan(0,count($result));
        $request = $result[0];
        $event = $request->getEvent();
        $this->assertInstanceOf('InterpretersOffice\Entity\Event', $event);
        $interpreter = $event->getInterpreters()[0];
        $this->assertInstanceOf('InterpretersOffice\Entity\Interpreter', $interpreter);

    }

    public function testAutomaticDeletionOfEventWhenRequestIsWithdrawn()
    {
        $this->login('john','gack!');
        $this->reset(true);
        // get our request id
        $result = $this->em->createQuery("SELECT r FROM InterpretersOffice\Requests\Entity\Request r
        JOIN r.submitter p JOIN InterpretersOffice\Entity\User u WITH u.person = p WHERE u.username = :username")
        ->setParameters(['username'=>'john'])->getResult();
        $this->assertTrue(count($result) == 1);
        $request = $result[0];
        $event = $request->getEvent();
        $this->getRequest()->setMethod('POST');
        $this->dispatch('/requests/cancel/'.$request->getId());
        $this->dumpResponse();
    }
}