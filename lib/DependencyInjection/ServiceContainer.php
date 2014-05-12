<?php
namespace Sea\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Sea\Sea;

/**
 * Sea's ServiceContainer
 *
 * @author Sebastiaan Marynissen <Sebastiaan.Marynissen@UGent.be>
 */
class ServiceContainer extends ContainerBuilder {
    
    /**
     * The Sea instance
     * 
     * @var Sea
     */
    private $sea;
    
    /**
     * Constructs the ContainerBuilder
     * 
     * Override this to specify your custom ServiceContainer, but don't forget
     * to call the parent constructor!!
     */
    public function __construct() {
        parent::__construct();
    }
    
    /**
     * Returns service, identified by its id
     * 
     * @param string $id Service id
     * @param int $invalidBehavior What to do when the service was not found
     * @return object The requested service
     */
    public function get($id, $invalidBehavior = self::EXCEPTION_ON_INVALID_REFERENCE) {
        if ($id === 'sea') {
            return $this->getSea();
        }
        else {
            return parent::get($id, $invalidBehavior);
        }
    }
    
    /**
     * Returns Sea
     * 
     * @return Sea
     */
    public function getSea() {
        return $this->sea;
    }
    
    /**
     * Sets the sea instance
     * 
     * @param \Sea\Sea $sea
     * @return \Sea\Services\ContainerBuilder
     * @internal Internal use only
     */
    public function setSea(Sea $sea) {
        $this->sea = $sea;
        return $this;
    }
    
}
