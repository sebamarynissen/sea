<?php
namespace Sea\Routing\Annotations;

/**
 * Represents a @Options annotation
 *
 * @author Sebastiaan Marynissen <Sebastiaan.Marynissen@UGent.be>
 * @Annotation
 */
class Options extends Method {
    
    /**
     * Sets that get is the only allowed method
     */
    public function __construct() {
        $this->setMethods("OPTIONS");
    }
    
}
