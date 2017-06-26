<?php
/**
 * module/Vault/src/Controller/VaultController
 */
namespace SDNY\Vault\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;

use SDNY\Vault\Service\Vault;
use SDNY\Vault\Service\VaultException;
use Zend\Authentication\AuthenticationServiceInterface;



//use Zend\Session\SessionManager;
use Zend\Crypt\BlockCipher;
use Zend\Crypt\Symmetric\Openssl;

/**
 * 
 * VaultController. very much a work-in-progress
 * 
 */
class VaultController extends AbstractActionController {
    
    
    protected $vaultService;
    
    protected $auth;
    
    public function __construct(Vault $vaultService, AuthenticationServiceInterface $auth) {
        
        $this->vaultService = $vaultService;
        $this->auth = $auth;
    }
  
    public function testAction()
    {
      
        $this->verifyAuth();
        echo " ...gack! looking good in testAction ..." ;  
        return false;
        return new JsonModel(['result'=>'OK']);
    }
    /**
     * redundant authentication and authorization check
     * 
     * @return boolean
     * @throws \Exception
     */
    protected function verifyAuth() {
        if (! $this->auth->hasIdentity()) {
            throw new \Exception("authentication is required");
        }
        $role = (string)$this->auth->getIdentity()->role;
        if (!in_array($role,['administrator','manager'])) {
            throw new \Exception("authorization denied to user in role $role");
        }
        return true;
    }   
    
    

    public function decryptAction()
    {
        $params = $this->params()->fromPost();
       
        try {
            $this->verifyAuth();           
            $key = $this->vaultService->getEncryptionKey();
            $cipher = new BlockCipher(new Openssl());
            $cipher->setKey($key);
            $decrypted = [];
            foreach(['ssn','dob'] as $field) {
                if (empty($params[$field])) {
                     $decrypted[$field] = '';
                     continue;
                }
                $decrypted[$field] =  $cipher->decrypt($params[$field]);
            }            
            return new JsonModel($decrypted) ;
            
        } catch (VaultException $e) {

             return new JsonModel(['error'=>$e->getMessage()]);
        }
    }  
}
