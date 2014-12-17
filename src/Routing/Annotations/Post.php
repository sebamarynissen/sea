<?php
namespace Sea\Routing\Annotations;

/**
 * Represents a @Post annotation
 *
 * @author Sebastiaan Marynissen <Sebastiaan.Marynissen@UGent.be>
 * @Annotation
 */
class Post extends Method {
    
    /**
     * Sets that get is the only allowed method
     */
    public function __construct() {
        $this->setMethods("POST");
    }
    
}
