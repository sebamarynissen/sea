<?php
namespace Sea\Routing\Annotations;

/**
 * Represents a @Delete annotation
 *
 * @author Sebastiaan Marynissen <Sebastiaan.Marynissen@UGent.be>
 * @Annotation
 */
class Delete extends Method {
    
    /**
     * Sets that get is the only allowed method
     */
    public function __construct() {
        $this->setMethods("DELETE");
    }
    
}
