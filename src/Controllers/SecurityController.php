<?php
namespace Sea\Controllers;

use Sea\HttpKernel\Controller;
use Sea\Routing\Annotations\Route;
use Sea\Routing\Annotations\Method;
use Sea\HttpKernel\PreLoadInterface;

/**
 * Description of SecurityController
 *
 * @author Sebastiaan Marynissen <Sebastiaan.Marynissen@UGent.be>
 */
class SecurityController extends Controller {
    
    /**
     * @Route("/")
     * @Method("GET")
     */
    public function getRoute() {
        return new \Symfony\Component\HttpFoundation\Response('GET');
    }
    
    /**
     * @Route("/")
     * @Method("POST")
     */
    public function post() {
        return new \Symfony\Component\HttpFoundation\Response('POST');
    }
    
}
