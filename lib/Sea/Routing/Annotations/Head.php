<?php
namespace Sea\Routing\Annotations;

/**
 * Represents a @Head annotation
 *
 * @author Sebastiaan Marynissen <Sebastiaan.Marynissen@UGent.be>
 * @Annotation
 */
class Head extends Method {
    
    /**
     * Sets that get is the only allowed method
     */
    public function __construct() {
        $this->setMethods("HEAD");
    }
    
}
