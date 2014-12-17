<?php
namespace Sea\Config;

use Sea\Sea;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Routing\RouteCollection;

/**
 * Interface that Configuration classes need to implement
 * 
 * If you want to specify a configuration by manually setting up the entire
 * configuration, the returned instance should implement this interface.
 * 
 * @author Sebastiaan
 */
interface ConfigurationInterface {
    
    /**
     * The configuration should return a collection of all routes
     * 
     * @return RouteCollection
     */
    public function getRouteCollection();
    
    /**
     * Should return a ContainerBuilder containing all services in the
     * configuration
     * 
     * @return ContainerBuilder
     */
    public function getServiceContainer();
    
    /**
     * Called upon initialization.
     * 
     * Can be used to setup additional configuration such as setting up security
     * firewalls etc.
     * 
     * @param Sea $sea The framework instance
     */
    public function configure(Sea $sea);
    
}
