<?php
/**
 * module/Admin/src/Service/Acl.php
 */
namespace InterpretersOffice\Admin\Service;

use Zend\Permissions\Acl\Acl as ZendAcl;



/**
 * ACL
 * 
 * 
 */
class Acl extends ZendAcl {
    
    
    /**
     * configuration
     * 
     * @var Array
     */
    protected $config;
    
    /**
     * constructor
     * 
     * @param array $config
     */
    public function __construct(Array $config)
    {
       $this->config = $config; 
       $this->setup();
    }
    
    /**
     * initialize the ACL
     */
    protected function setup()
            
    {       
        foreach($this->config['resources'] as $resource => $parent) {
            $this->addResource($resource, $parent);
        }
        foreach ($this->config['roles'] as $role => $parents) {
            $this->addRole($role,$parents);
        }
        /*
        'allow' => [
            //'role' => [ 'resource' => [ priv, other-priv, ...  ]
            'submitter' => [
                'requests' => ['create','view','index'],
                'events'   => ['index','view','search'],
            ],
            'manager' => [                
                'languages' => null,
                'events' => null,
            ],
            'administrator' => null,
        ],
         */
        foreach($this->config['allow'] as $role => $rules ) {
           if (null === $rules) {
               $this->allow($role);
               continue;
           }
           foreach($rules as $resource => $privileges) {
               //printf ("we are setting allow on role %s, resource %s, privs %s<br>",$role,$resource, is_scalar($privileges)
               // ? $privileges : implode(",",$privileges));
               $this->allow($role,$resource,$privileges);               
           }            
        }
        foreach($this->config['deny'] as $role => $rules ) {
           if (null === $rules) {
               $this->deny($role);
               continue;
           }
           foreach($rules as $resource => $privileges) {
               //printf ("we are setting allow on role %s, resource %s, privs %s<br>",$role,$resource, is_scalar($privileges)
               // ? $privileges : implode(",",$privileges));
               $this->deny($role,$resource,$privileges);               
           }            
        } 
    }
    
}
