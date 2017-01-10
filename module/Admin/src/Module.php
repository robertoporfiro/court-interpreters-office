<?php
/**
 * module/Admin/src/Module.php.
 */

namespace InterpretersOffice\Admin;

use Zend\Mvc\MvcEvent;
use Zend\Session\SessionManager;

/**
 * Module class for our InterpretersOffice\Admin module.
 */
class Module
{
    /**
     * returns this module's configuration.
     *
     * @return array
     */
    public function getConfig()
    {
        return include __DIR__.'/../config/module.config.php';
    }

    /**
     * {@inheritdoc}
     *
     * @param \Zend\EventManager\EventInterface $event
     *                                                 interesting discussion, albeit for ZF2
     *                                                 http://stackoverflow.com/questions/14169699/zend-framework-2-how-to-place-a-redirect-into-a-module-before-the-application#14170913
     */
    public function onBootstrap(\Zend\EventManager\EventInterface $event)
    {
        $eventManager = $event->getApplication()->getEventManager();
        $eventManager->attach(MvcEvent::EVENT_ROUTE, [$this, 'enforceAuthentication']);
        $container = $event->getApplication()->getServiceManager();
        //// The following line instantiates the SessionManager and automatically
        // makes the SessionManager the 'default' one:
        // https://olegkrivtsov.github.io/using-zend-framework-3-book/html/en/Working_with_Sessions/Session_Manager.html
        // $sessionManager =
        $container->get(SessionManager::class);
    }

    /**
     * callback to check authentication on mvc route event.
     *
     * If the routeMatch's "module" parameter is InterpretersOffice\Admin,
     * we test for authentication and redirect to login if the user is not
     * authenticated. Otherwise, we test whether the user is in the role
     * "manager" or "administrator" and redirect to login if not. This last
     * is arguably something that should be handled by ACL but we are here now,
     * so why not.
     *
     * @todo maybe inject User entity, if found, into someplace for later access.
     * e.g., the controller?
     *
     * @param MvcEvent $event
     */
    public function enforceAuthentication(MvcEvent $event)
    {
        $match = $event->getRouteMatch();
        if (!$match) {
            return;
        }
        $module = $match->getParam('module');
        if (__NAMESPACE__ == $module) {
            $container = $event->getApplication()->getServiceManager();
            $auth = $container->get('auth');
            if (!$auth->hasIdentity()) {
                $flashMessenger = $container
                        ->get('ControllerPluginManager')
                        ->get('FlashMessenger');
                $flashMessenger->addWarningMessage('Authentication is required.');
                $session = $container->get('Authentication');
                $session->redirect_url = $event->getRequest()->getUriString();

                return $this->getRedirectionResponse($event);
            } else {
                $allowed = ['manager', 'administrator'];
                $user = $container->get('entity-manager')
                    ->find(
                        'InterpretersOffice\Entity\User',
                        $auth->getIdentity()->getId()
                    );
                $role = (string) $user->getRole();
                if (!in_array($role, $allowed)) {
                    $flashMessenger = $container
                        ->get('ControllerPluginManager')
                        ->get('FlashMessenger');
                    $flashMessenger->addWarningMessage('Access denied.');

                    return $this->getRedirectionResponse($event);
                }
            }
        }
    }

    /**
     * returns a Response redirecting to the login page.
     *
     * @param MvcEvent $event
     *
     * @return Zend\Http\PhpEnvironment\Response
     */
    public function getRedirectionResponse(MvcEvent $event)
    {
        $response = $event->getResponse();
        $baseUrl = $event->getRequest()->getBaseurl();
        $response->getHeaders()
            ->addHeaderLine('Location', $baseUrl.'/login');
        $response->setStatusCode(303);
        $response->sendHeaders();

        return $response;
    }
}
