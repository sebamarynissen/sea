<?php
namespace Sea\Routing\Annotations;

/**
 * Represents a @Put annotation
 *
 * @author Sebastiaan Marynissen <Sebastiaan.Marynissen@UGent.be>
 * @Annotation
 */
class Put extends Method {
    
    /**
     * Sets that get is the only allowed method
     */
    public function __construct() {
        $this->setMethods("PUT");
    }
    
}
