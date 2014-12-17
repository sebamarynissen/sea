<?php
namespace Sea\Routing;

use Symfony\Component\Config\Loader\FileLoader;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;

/**
 * Description of RestRouteLoader
 *
 * @author Sebastiaan Marynissen <Sebastiaan.Marynissen@UGent.be>
 */
class RestRouteLoader extends FileLoader {
    
    /**
     * The constant of what type this loader supports
     */
    const SUPPORT_TYPE = 'Sea/rest';
    
    /**
     * Constructs the restrouteloader by providing it an array of paths to look
     * for file
     * 
     * @param type $paths
     */
    public function __construct($paths) {
        parent::__construct(new FileLocator($paths));
    }
    
    /**
     * Loads the RESTful routes
     * 
     * @param json file with restful routes $resource
     * @param type $type
     * @return \Symfony\Component\Routing\RouteCollection
     */
    public function load($file, $type = null) {
        
        // Locate the file and make sure it is not an external file or something
        $path = $this->locator->locate($file);
        if (!stream_is_local($path)) {
            throw new \InvalidArgumentException(sprintf('This is not a local file "%s".', $path));
        }
        if (!file_exists($path)) {
            throw new \InvalidArgumentException(sprintf('File "%s" not found.', $path));
        }
        
        // Read the configuration
        $config = json_decode(file_get_contents($path), true);
        
        // Create the collection
        $collection = new RouteCollection();
        $collection->addResource(new FileResource($file));
        
        // Handle empty files
        if ($config === null) {
            return $collection;
        }
        
        // Not an array
        if (!is_array($config)) {
            throw new \InvalidArgumentException(sprintf('The file "%s" must contain a valid JSON object.', $path));
        }
        
        // Loop config settings and start parsing routes
        foreach ($config as $config) {
            
            // If a resource was specified, import, otherwise parse route
            if (isset($config['resource'])) {
                $this->parseImport($collection, $config, $path, $file);
            } else {
                $this->parseRoutes($collection, $config);
            }
        }
        
        return $collection;
        
    }

    /**
     * {@inheritdoc}
     */
    public function supports($resource, $type = null) {
        return $type === 'Sea/rest' && pathinfo($resource, PATHINFO_EXTENSION) === 'json';
    }
    
    /**
     * Parses routes from a rest-route array
     * 
     * @param RouteCollection $collection Collection to add routes to
     * @param array $config COnfig values
     */
    protected function parseRoutes(RouteCollection $collection, array $config) {
        
        // Get controller and all paths to route to this controller
        $controller = isset($config['controller']) ? $config['controller'] : null;
        $paths = isset($config['paths']) ? $config['paths'] : array();
        
        // Parse additional route specifications, may be overriden later
        $controllerSpecs = array(
            'defaults' => isset($config['defaults']) ? $config['defaults'] : array(),
            'requirements' => isset($config['requirements']) ? $config['requirements'] : array(),
            'options' => isset($config['options']) ? $config['options'] : array(),
            'host' => isset($config['host']) ? $config['host'] : null,
            'schemes' => isset($config['schemes']) ? $config['schemes'] : array(),
            'condition' => isset($config['condition']) ? $config['condition'] : null
        );
        
        // Loop all paths and the path configuration
        foreach ($paths as $path => $cnf) {
            
            // Override controller specs if necessary
            $pathSpecs = array(
                'defaults' => array_merge($controllerSpecs['defaults'], isset($cnf['defaults']) ? $cnf['defaults'] : array()),
                'requirements' => array_merge($controllerSpecs['requirements'], isset($cnf['requirements']) ? $cnf['requirements'] : array()),
                'options' => array_merge($controllerSpecs['options'], isset($cnf['options']) ? $cnf['options'] : array()),
                'host' => isset($cnf['host']) ? $cnf['host'] : $controllerSpecs['host'],
                'schemes' => array_merge($controllerSpecs['schemes'], isset($cnf['schemes']) ? $cnf['schemes'] : array()),
                'condition' => isset($cnf['condition']) ? $cnf['condition'] : $controllerSpecs['condition']
            );
            
            // Get all methods to support for this path and loop
            $methods = isset($cnf['methods']) ? $cnf['methods'] : array();
            foreach ($methods as $method => $action) {
                
                // Parse route and add
                $routeName = strtoupper($method) . ' ' . $path;
                $defaults = array('_controller' => sprintf('%s::%s', $controller, $action));
                $route = new Route(
                        $path,
                        array_merge($pathSpecs['defaults'], $defaults),
                        $pathSpecs['requirements'],
                        $pathSpecs['options'],
                        $pathSpecs['host'],
                        $pathSpecs['schemes'],
                        array($method),
                        $pathSpecs['condition']
                );
                $collection->add($routeName, $route);
                
            }
            
        }
        
    }
    
    /**
     * Imports restful routes
     * 
     * @param \Symfony\Component\Routing\RouteCollection $collection
     * @param array $config
     * @param filename of the file containing the restful routes $file
     */
    protected function parseImport(RouteCollection $collection, array $config, $file) {
        
        $prefix = isset($config['prefix']) ? $config['prefix'] : '';
        $defaults = isset($config['defaults']) ? $config['defaults'] : array();
        $requirements = isset($config['requirements']) ? $config['requirements'] : array();
        $options = isset($config['options']) ? $config['options'] : array();
        $host = isset($config['host']) ? $config['host'] : null;
        $schemes = isset($config['schemes']) ? $config['schemes'] : null;

        // Don't know why Symfony does this, causes to look in wrong directory..
        // $this->setCurrentDir(dirname($path));
        
        $subCollection = $this->import($config['resource'], self::SUPPORT_TYPE, false, $file);
        
        /* @var $subCollection RouteCollection */
        $subCollection->addPrefix($prefix);
        if (null !== $host) {
            $subCollection->setHost($host);
        }
        if (null !== $schemes) {
            $subCollection->setSchemes($schemes);
        }
        $subCollection->addDefaults($defaults);
        $subCollection->addRequirements($requirements);
        $subCollection->addOptions($options);

        $collection->addCollection($subCollection);
        
    }
    
}
