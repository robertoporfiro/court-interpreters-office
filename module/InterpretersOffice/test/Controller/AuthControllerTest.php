<?php

/** module/Application/test/Controller/AuthControllerTest.php */

namespace ApplicationTest\Controller;

use ApplicationTest\AbstractControllerTest;
use InterpretersOffice\Admin\Controller\AuthController;
use ApplicationTest\FixtureManager;
use ApplicationTest\DataFixture;
use Zend\Stdlib\Parameters;
use Zend\Dom\Query;
use InterpretersOffice\Entity;

class AuthControllerTest extends AbstractControllerTest
{
    public function setUp()
    {
        parent::setUp();
        $fixtureExecutor = FixtureManager::getFixtureExecutor();
        $fixtureExecutor->execute([
            new DataFixture\MinimalUserLoader(),
        ]);
 
    }
    /**
     * asserts that susie_somebody@nysd.uscourts.gov can log in
     * with either username or email address
     */
    public function testLoginAdminUser()
    {
        
        $token = $this->getCsrfToken('/login');
        $auth = $this->getApplicationServiceLocator()->get('auth');
        $params =
        [
            'identity' => 'susie',
            'password' => 'boink',
            'csrf'     => $token,
        ];
        $this->dispatch('/login', 'POST', $params);        

        $this->assertTrue($auth->hasIdentity());
        $auth->clearIdentity();
        // just checking :-)
        $this->assertFalse($auth->hasIdentity());
        
        // now try it using email instead of username
        $params['identity'] = 'susie_somebody@nysd.uscourts.gov';
        $this->dispatch('/login', 'POST', $params);
        $this->assertTrue($auth->hasIdentity());
        
        $this->assertRedirect();
        $this->assertRedirectTo('/admin');
        
    }
    
    /**
     * asserts that a non-administrative user cannot go to /admin
     */
    public function testNonAdministrativeUserCannotAccessAdmin()
    {
        // demote user Susie
        $entityManager = FixtureManager::getEntityManager();
        $susie = $entityManager->getRepository('InterpretersOffice\Entity\User')
                ->findByUsername('susie')[0];
        $susie->setRole(
             $entityManager->getRepository('InterpretersOffice\Entity\Role')
                ->findByName('submitter')[0]
        );
        $entityManager->flush();
        // sanity-check it first
        $this->assertEquals('submitter',(string)$susie->getRole());
        
        $token = $this->getCsrfToken('/login');
        $params =
        [
            'identity' => 'susie',
            'password' => 'boink',
            'csrf'    => $token,
        ];
        $this->dispatch('/login', 'POST', $params);
        $this->assertRedirect();
        $this->assertNotRedirectTo('/admin');
        
        $this->dispatch('/admin');
        $this->assertRedirect();
        $this->assertResponseStatusCode(302);
        
    }
    /**
     * asserts that the wrong identity or password fails to authenticate
     */
    public function testBadLoginNameOrPasswordFailsToAuthenticate()
    {
        // previous assertions established that the password is 'boink' 
        // so anything else should fail
        $this->dispatch('/login', 'POST', 
               ['identity'=>'susie',
                 'password'=>'notCorrect',
                 'csrf' => $this->getCsrfToken('/login'),
               ]
        );
        $this->assertNotRedirect();
        $auth = $this->getApplicationServiceLocator()->get('auth');
        $this->assertFalse($auth->hasIdentity());
        $this->assertQuery('div.alert-warning');
        $this->assertQueryContentRegex('div.alert-warning', '/authentication failed/i');
        $this->dispatch('/login', 'POST', 
                 [   'identity'=>'nobody',
                     'password'=>'notCorrect',
                     'csrf' => $this->getCsrfToken('/login'),
                 ]
        );
        $this->assertNotRedirect();
        $this->assertFalse($auth->hasIdentity());
        $this->assertQuery('div.alert-warning');
        $this->assertQueryContentRegex('div.alert-warning', '/authentication failed/i');
    }
}