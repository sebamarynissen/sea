<?php
namespace Sea\Routing\Annotations;

/**
 * Represents a method annotation
 *
 * @author Sebastiaan Marynissen <Sebastiaan.Marynissen@UGent.be>
 * @Annotation
 */
class Method {
    
    /**
     * Array of resitricted HTTP methods
     * 
     * @var array
     */
    protected $methods;
    
    /**
     * 
     * @param array $values Values to set the annotations
     * @throws \RuntimeException
     */
    public function __construct(array $values) {
        foreach ($values as $k => $v) {
            if (!method_exists($this, $name = 'set'.$k)) {
                throw new \RuntimeException(sprintf('Unknown key "%s" for annotation "@%s".', $k, get_class($this)));
            }
            $this->$name($v);
        }
    }
    
    /**
     * Returns all restricted HTTP methods that were set
     * 
     * @return array
     */
    public function getMethods() {
        return $this->methods;
    }
    
    /**
     * Sets the HTTP methods.
     *
     * @param array|string $methods An HTTP method or an array of HTTP methods
     */
    public function setMethods($methods) {
        $this->methods = is_array($methods) ? $methods : array($methods);
    }
    
    /**
     * Acts as default entry
     * 
     * Should redirect to "setMethods" to set the methods, because this is the
     * default
     * 
     * @param type $value
     */
    public function setValue($value) {
        $this->setMethods($value);
    }
    
}
