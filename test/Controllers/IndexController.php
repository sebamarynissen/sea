<?php
namespace Controllers;

use Sea\HttpKernel\Controller;
use Sea\Routing\Annotations\Route;
use Sea\Security\SecurityContext;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Description of IndexController
 *
 * @author Sebastiaan Marynissen <Sebastiaan.Marynissen@UGent.be>
 */
class IndexController extends Controller {
    
    /**
     * @Route("/")
     */
    public function index() {
        
        /* @var $context SecurityContext */
        $context = $this->get('security.context');
        
        
        /* @var $token UsernamePasswordToken */
        $token = $context->getToken();
        
        /* @var $user UserInterface */
        $user = $token->getUser();
        return $this->response($user->getUsername());
    }
    
    /**
     * @Route("/hello")
     */
    public function hello() {
        die("This is the hello route");
    }
    
}
