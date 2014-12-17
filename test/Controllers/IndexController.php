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
        return $this->response('Hello');
    }
    
    /**
     * @Route("/hello")
     */
    public function hello() {
        die("This is the hello route");
    }
    
}
