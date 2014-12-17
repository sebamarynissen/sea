<?php
namespace Sea\Config;

use Sea\Sea;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Routing\RouteCollection;

/**
 * A configuration object, representing an entire configuration of Sea
 * 
 * @author Sebastiaan Marynissen <sebastiaan.marynissen@gmail.com>
 */
class Configuration implements ConfigurationInterface {
    
    /**
     * The collection of routes
     * 
     * @var RouteCollection
     */
    protected $routes;
    
    /**
     * The ContainerBuilder containing all defined services etc.
     * 
     * @var ContainerBuilder
     */
    protected $container;
    
    /**
     * The calable which performs additional configuration
     * 
     * @var callable
     */
    protected $configure;
    
    /**
     * Constructor
     * 
     * @param RouteCollection $routes
     */
    public function __construct(RouteCollection $routes, ContainerBuilder $container, callable $configure) {
        $this->routes = $routes;
        $this->container = $container;
        $this->configure = $configure;
    }
    
    /**
     * Returns all routes that where set
     * 
     * Forced by the ConfigurationInterface
     * 
     * @return RouteCollection
     */
    public function getRouteCollection() {
        return $this->routes;
    }
    
    /**
     * Returns the container containing all defined services
     * 
     * @return ContainerBuilder
     */
    public function getServiceContainer() {
        return $this->container;
    }
    
    /**
     * Performs additional configuration, out of the scope of the config file
     * 
     * @param Sea $sea The framework instance
     */
    public function configure(Sea $sea) {
        if ($this->configure) {
            call_user_func($this->configure, $sea);
        }
    }
    
}
