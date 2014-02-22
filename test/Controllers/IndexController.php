<?php
namespace Controllers;

use Sea\Routing\Annotations\Route;

/**
 * Description of IndexController
 *
 * @author Sebastiaan Marynissen <Sebastiaan.Marynissen@UGent.be>
 */
class IndexController {
    
    /**
     * @Route("/")
     */
    public function index() {
        die("Hello");
    }
    
}
