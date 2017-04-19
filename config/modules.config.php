<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 *
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

/**
 * List of enabled modules for this application.
 *
 * This should be an array of module namespaces used in the application.
 */
return [
    'Zend\Log',
    'Zend\Mvc\Plugin\FlashMessenger',
    'Zend\Session',
    'DoctrineModule',
    'DoctrineORMModule',
    //'Zend\Cache',
    'Zend\Form',
    //'Zend\InputFilter',
    //'Zend\Filter',
    //'Zend\Paginator',
    //'Zend\Hydrator',
    'Zend\Router',
    //'Zend\Validator',
    'InterpretersOffice',
    'InterpretersOffice\Admin',
    'InterpretersOffice\Requests',
    'SDNY\Vault'
];
