<?php
/** module/Admin/config/acl.php
 *
 *  ACL configuration based on LearnZF2.
 */
use InterpretersOffice\Admin\Controller as Admin;
use InterpretersOffice\Controller as Main;
use InterpretersOffice\Requests\Controller as Requests;
use InterpretersOffice\Admin\Notes;

return [
    'roles' => [
        // 'role name' => 'parent role'
        'anonymous' => null,
        'submitter' => null,
        'manager' => null,
        'administrator' => 'manager',
        'staff' => null,
    ],

    'resources' => [
        // 'resource name (controller)' => 'parent resource'
       // Main\IndexController::class => null,
        Admin\LanguagesController::class => null,
        Admin\ConfigController::class => null,
        Admin\EventTypesController::class => Admin\LanguagesController::class,
        Admin\LocationsController::class => Admin\LanguagesController::class,
        Admin\EventsController::class => null,
        Admin\UsersController::class => Admin\LanguagesController::class, //null,//Admin\EventsController::class,
        Admin\PeopleController::class => Admin\LanguagesController::class,
        Admin\JudgesController::class => Admin\UsersController::class,//Admin\EventsController::class,
        Admin\InterpretersController::class => Admin\EventsController::class,
        Admin\InterpretersWriteController::class => Admin\LanguagesController::class,
        Admin\CourtClosingsController::class => Admin\LanguagesController::class,
        Admin\DefendantsController::class => Admin\EventsController::class,
        Admin\ScheduleController::class => Admin\EventsController::class,
        Admin\EmailController::class => Admin\EventsController::class,
        Admin\NormalizationController::class => Admin\EventsController::class,
        Admin\SearchController::class => Admin\EventsController::class,
        Admin\ReportsController::class => Admin\EventsController::class,
        Admin\DocketAnnotationsController::class => Admin\EventsController::class,
        Admin\RestfulDocketAnnotationsController::class => Admin\DocketAnnotationsController::class,
        Notes\Controller\NotesController::class => Admin\EventsController::class,
        // the topmost controller
        Main\IndexController::class => null,
        Requests\IndexController::class => null,
        Requests\WriteController::class => null,
        Admin\IndexController::class => null,
        'SDNY\Vault\Controller\VaultController' => null,
        Main\AuthController::class => null,
        // these refer to user resource ids. the User entity implements
        // Laminas\Permissions\Acl\Resource\ResourceInterface
        'administrator' => null,
        'manager' => null,
        'submitter' => null,
        'staff' => null,
        // probably don't need this in production :-)
       // 'DoctrineORMModule\Yuml\YumlController' => null,
    ],
    /* How do we configure this to use Assertions?
       I think we don't. What we probably could do is use PHP 
       to reason about the environment and set the rules dynamically
       in that way. 
    */
    'allow' => [
        //'role' => [ 'resource (controller)' => [ priv, other-priv, ...  ]
        'submitter' => [
            Requests\IndexController::class => ['index','list','view','create','update','search','cancel','help','docket-search'],
            Requests\WriteController::class => ['create','update','cancel'],            
            Main\AuthController::class => ['logout'],
            Main\IndexController::class => ['schedule','view-event'],
        ],
        'manager' => [
            Admin\IndexController::class => null,
            Admin\LanguagesController::class => null,
            Admin\EventsController::class => null,
            Admin\UsersController::class => null,
            Admin\ConfigController::class => ['index','forms'],
            // ??
            'SDNY\Vault\Controller\VaultController' => null,
            Main\AuthController::class => ['logout'],
            'submitter' => null,
        ],
        'staff' => [
            Admin\IndexController::class => ['index'],
            Main\AuthController::class => ['logout'],
            Admin\EventsController::class => null,
            Admin\LanguagesController::class =>['index','view'],
        ],
        'administrator' => null,
        'anonymous' => [
            Main\AuthController::class => 'login',            
        ]
    ],
    'deny' => [
        'administrator' => [
            Requests\IndexController::class => null,
            //['add','edit','update','delete','cancel','index'],
        ],

        'anonymous' => [
            Main\AuthController::class => 'logout',
            Main\IndexController::class => ['schedule','view-event'],
        ],
    ]
];
