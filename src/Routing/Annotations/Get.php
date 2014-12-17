<?php
namespace Sea\Routing\Annotations;

/**
 * Represents a @Get annotation
 *
 * @author Sebastiaan Marynissen <Sebastiaan.Marynissen@UGent.be>
 * @Annotation
 */
class Get extends Method {
    
    /**
     * Sets that get is the only allowed method
     */
    public function __construct() {
        $this->setMethods("GET");
    }
    
}
