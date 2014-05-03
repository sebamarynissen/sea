<?php
namespace Sea\Routing\Annotations;

/**
 * Defines a prefix annotation
 * 
 * A prefix annotation can be used 
 *
 * @author Sebastiaan Marynissen <Sebastiaan.Marynissen@UGent.be>
 * @Annotation
 */
class Prefix {
    
    /**
     * The prefix
     * 
     * @var string
     */
    protected $prefix;
    
    /**
     * Constructs the prefix annotation
     * 
     * Only one key can be specified, being "prefix". Therefore it is not
     * necessary to specify the "prefix" key explicitly. For example, 
     * [at]Prefix(prefix="myprefix") is equivalent to [at]Prefix("myprefix")
     * 
     * @param array $values
     */
    public function __construct(array $values) {
        $this->prefix = isset($values['prefix']) ? $values['prefix'] : $values['value'];
    }
    
    /**
     * Returns the prefix that was specified
     * 
     * @return string
     */
    public function getPrefix() {
        return $this->prefix;
    }
    
}
