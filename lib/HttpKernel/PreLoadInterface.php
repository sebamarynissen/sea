<?php
namespace Sea\HttpKernel;

/**
 * Implement this interface if you want to perform some actions before your
 * controller action is called
 *
 * @author Sebastiaan Marynissen <Sebastiaan.Marynissen@UGent.be>
 */
interface PreLoadInterface {
    
    /**
     * Implement this method to specify the behavior before an action is called.
     * For instance, if your controller should be a secured controller, using
     * the security stuff in Sea, use this method to grant or deny access etc.
     */
    public function preLoad();
    
}
