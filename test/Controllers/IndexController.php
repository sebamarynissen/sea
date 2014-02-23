<?php
namespace Controllers;

use Sea\Routing\Annotations\Route;
use Sea\Routing\Annotations\Prefix;

/**
 * Description of IndexController
 *
 * @author Sebastiaan Marynissen <Sebastiaan.Marynissen@UGent.be>
 * @Prefix("indexes")
 */
class IndexController {
    
    /**
     * @Route("/")
     */
    public function index() {
        die("Hello");
    }
    
    /**
     * @Route("/hello")
     */
    public function hello() {
        die("This is the hello route");
    }
    
}
