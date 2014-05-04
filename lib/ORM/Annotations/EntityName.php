<?php
namespace Sea\ORM\Annotations;

use Doctrine\ORM\Mapping\Annotation;

/**
 * An EntityName annotation is used to specify which Entity a specific
 * repository is responsible for. Put this annotation at your class description
 * to specify which annotation the repository should listen for
 *
 * @author Sebastiaan Marynissen <Sebastiaan.Marynissen@UGent.be>
 * @Annotation
 */
class EntityName {
    
    /**
     * The class name of the specified entity
     * 
     * @var string
     */
    protected $entityName;
    
    /**
     * Constructs the EntityName annotation
     * 
     * No keys can be specified, only the class name of the Entity. Therefore, 
     * only extract the value
     */
    public function __construct(array $values) {
        $this->entityName = $values['value'];
    }
    
    /**
     * Returns the entity name
     * 
     * @return string
     */
    public function __toString() {
        return $this->getEntityName();
    }
    
    /**
     * Returns the entity name
     * 
     * @return string
     */
    public function getEntityName() {
        return $this->entityName;
    }
    
}
