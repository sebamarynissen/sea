<?php
namespace Sea\Config\Loaders;

use Composer\Autoload\ClassLoader;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Sea\Config\Configuration;
use Sea\Config\ConfigurationInterface;
use Sea\Config\Loaders\Routing\AnnotationLoader;
use Sea\Routing\JsonFileLoader;
use Sea\Sea;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\FileLocatorInterface;
use Symfony\Component\Config\Loader\DelegatingLoader;
use Symfony\Component\Config\Loader\FileLoader;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Loader\LoaderResolver;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\Routing\Loader\PhpFileLoader as RoutesPhpFileLoader;

/**
 * Loads the configuration values.
 * 
 * It would be possible to use a loaderresolver and then pass multiple loaders,
 * however this would be overkill. We'll simply use something different
 * 
 * @author Sebastiaan Marynissen <sebastiaan.marynissen@gmail.com>
 */
class ConfigLoader extends FileLoader {
    
    /**
     * The loader which will load the routes
     * 
     * @var LoaderInterface
     */
    protected $routesLoader;
    
    /**
     * The locator which will look for files
     * 
     * @var FileLocator
     */
    protected $locator;
    
    /**
     * Cosntructor
     * 
     * @param FileLocatorInterface $locator Will look for files
     * @param ClassLoader $composer Needed to autoload the annotations
     */
    public function __construct(FileLocatorInterface $locator, ClassLoader $composer) {
        parent::__construct($locator);
        AnnotationRegistry::registerLoader(function($class) use ($composer) {
            return $composer->loadClass($class);
        });
    }
    
    /**
     * Creates the RoutesLoader on the fly
     * 
     * @return LoaderInterface The loader which will load all routes
     */
    protected function getRoutesLoader() {
        
        // Set up the routesloader if none setyet
        if ($this->routesLoader === null) {
            $this->routesLoader = new DelegatingLoader(new LoaderResolver(array(
                new JsonFileLoader($this->locator),
                new AnnotationLoader(new AnnotationReader()),
                new RoutesPhpFileLoader($this->locator)
            )));
        }
        
        return $this->routesLoader;
    }
    
    /**
     * Returns a new serviceloader for the given containerbuilder
     * 
     * @param ContainerBuilder $container The containerbuilder
     * @return DelegatingLoader A delegatingloader which will load the services
     * for us
     */
    protected function getServiceLoader(ContainerBuilder $container) {
        $resolver = new LoaderResolver(array(
            new PhpFileLoader($container, $this->locator)
        ));
        $loader = new DelegatingLoader($resolver);
        return $loader;
    }
    
    /**
     * Loads the actual resource
     * 
     * @param string $resource The resource to load
     * @param string $type The type of the resource to load
     * @return array An associative array containing all configuration values
     */
    public function load($resource, $type = null) {
        
        $path = $this->locator->locate($resource);
        $ext = $this->norm($type);
        
        // First of all, if we're dealing with a raw PHP configuration, that's
        // easy, we'll require a configuration.
        if ($ext === 'php') {
            $config = require_once $path;
            if (!($config instanceof ConfigurationInterface)) {
                throw new InvalidConfigurationException(sprintf('Invalid configuration! %s did not return a valid Configuration! The return value should implement the Sea\\Config\\ConfigurationInterface!', $path));
            }
            return $config;
        }
        else {
        
            // In case of all other types (which is mainly json, possibly Yaml), 
            // we'll simply request the loaders to do the work for us. However,
            // first, we need to know where the resources are which we should
            // load. Therefore, we need to parse the configuration first.
            if ($ext === 'json') {
                $setup = json_decode(file_get_contents($path), true);
            }

            // When parsed, we'll look fro the "routes" field. This will define
            // where the routes will be loaded from. Could be from a Php
            // resource, a json resource etc.
            $routesBag = new ParameterBag($setup['routes'] ?: array());
            $routes = $this->getRoutesLoader()->load($routesBag->get('resource'), $routesBag->get('type'));
            
            // Allow the configuration to define all services by passing it a
            // containerbuilder.
            $services = new ContainerBuilder();
            if (isset($setup['services'])) {
                $serviceBag = new ParameterBag($setup['services'] ?: array());
                $this->getServiceLoader($services)->load($serviceBag->get('resource'), $serviceBag->get('type'));
            }
            
            // Allow for further detailed configuration by given the possiblity
            // to specify a custom callable.
            $callable = function() {};
            if (isset($setup['configure'])) {
                $bag = new ParameterBag($setup['configure'] ?: array());
                $callable = $this->createConfigurer($bag->get('resource'), $bag->get('type'));
            }
            
            // Create the configuration and let's return it
            $config = new Configuration($routes, $services, $callable);
            
        }
        
        return $config;
    }
    
    /**
     * Returns a configure callable
     * 
     * @return callable
     */
    private function createConfigurer($resource, $type = null) {
        $locator = $this->locator;
        return function(Sea $sea) use ($locator, $resource, $type) {
            $resolver = new LoaderResolver(array(
                new Configure\PhpConfigurerLoader($sea, $locator)
            ));
            $loader = new DelegatingLoader($resolver);
            $loader->load($resource, $type);
        };
    }
    
    /**
     * Normalizes file extensions
     * 
     * @param string $extension The extension to normalize
     * @return string Normalized extension
     */
    protected function norm($extension) {
        if ($extension === null) {
            return null;
        }
        return preg_replace('/^ya?ml$/', 'yaml', strtolower($extension));
    }
    
    /**
     * Returns whether the loading of the configuration file is supported
     * 
     * @param string $resource The resource
     * @param string $type The resource type
     * @return boolean Whether the resource is supported or not
     */
    public function supports($resource, $type = null) {
        return true;
    }

}
