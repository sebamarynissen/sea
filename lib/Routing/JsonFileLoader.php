<?php
namespace Sea\Routing;
use Symfony\Component\Routing\Route;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\FileLoader;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Config\Resource\FileResource;

/**
 * Loads the routes
 *
 * @author Sebastiaan Marynissen <Sebastiaan.Marynissen@UGent.be>
 */
class JsonFileLoader extends FileLoader {
    
    /**
     * Array of keys that can be used to characterize a route
     * 
     * @var array
     */
    private static $availableKeys = array(
        'resource', 'type', 'prefix', 'pattern', 'path', 'host', 'schemes', 'methods', 'defaults', 'requirements', 'options', 'condition'
    );
    
    /**
     * Constructs the RoutesLoader by giving it an array of paths where to look
     * 
     * @param array $paths Array of paths to look for files
     */
    public function __construct($paths) {
        parent::__construct(new FileLocator($paths));
    }
    
    /**
     * Loads the routes and returns a route collection
     * 
     * @param The filename to load $resource
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

        // Read the routes configuration
        $config = json_decode(file_get_contents($path), true);

        // Create the collection
        $collection = new RouteCollection();
        $collection->addResource(new FileResource($path));

        // Handle empty files
        if (null === $config) {
            return $collection;
        }

        // Not an array
        if (!is_array($config)) {
            throw new \InvalidArgumentException(sprintf('The file "%s" must contain a valid JSON object.', $path));
        }

        // Loop all config settings and start creating routes
        foreach ($config as $name => $config) {
            
            // Backwards compatibility for pattern
            if (isset($config['pattern'])) {
                if (isset($config['path'])) {
                    throw new \InvalidArgumentException(sprintf('The file "%s" cannot define both a "path" and a "pattern" attribute. Use only "path".', $path));
                }

                $config['path'] = $config['pattern'];
                unset($config['pattern']);
            }

            $this->validate($config, $name, $path);

            // If a resource was specified, import, otherwise parse route
            if (isset($config['resource'])) {
                
                $this->parseImport($collection, $config, $path, $file);
            } else {
                $this->parseRoute($collection, $name, $config, $path);
            }
        }
        
        return $collection;
        
    }

    /**
     * Returns whether the loader supports to load the given resource
     * 
     * @param mixed $resource Resource
     * @param string $type [Optional] Type
     * @return boolean
     */
    public function supports($resource, $type = null) {
        return (is_string($resource) && (pathinfo($resource, PATHINFO_EXTENSION) === 'json') || $type === 'json') && ($type !== 'Sea/rest');
    }

    /**
     * Parses a route and adds it to the RouteCollection.
     *
     * @param RouteCollection $collection A RouteCollection instance
     * @param string          $name       Route name
     * @param array           $config     Route definition
     * @param string          $path       Full path of the YAML file being processed
     */
    protected function parseRoute(RouteCollection $collection, $name, array $config, $path) {
        
        $defaults = isset($config['defaults']) ? $config['defaults'] : array();
        $requirements = isset($config['requirements']) ? $config['requirements'] : array();
        $options = isset($config['options']) ? $config['options'] : array();
        $host = isset($config['host']) ? $config['host'] : '';
        $schemes = isset($config['schemes']) ? $config['schemes'] : array();
        $methods = isset($config['methods']) ? $config['methods'] : array();
        $condition = isset($config['condition']) ? $config['condition'] : null;

        $route = new Route($config['path'], $defaults, $requirements, $options, $host, $schemes, $methods, $condition);

        $collection->add($name, $route);
        
    }

    /**
     * Parses an import and adds the routes in the resource to the RouteCollection.
     *
     * @param RouteCollection $collection A RouteCollection instance
     * @param array           $config     Route definition
     * @param string          $path       Full path of the YAML file being processed
     * @param string          $file       Loaded file name
     */
    protected function parseImport(RouteCollection $collection, array $config, $path, $file) {

        $type = isset($config['type']) ? $config['type'] : null;
        $prefix = isset($config['prefix']) ? $config['prefix'] : '';
        $defaults = isset($config['defaults']) ? $config['defaults'] : array();
        $requirements = isset($config['requirements']) ? $config['requirements'] : array();
        $options = isset($config['options']) ? $config['options'] : array();
        $host = isset($config['host']) ? $config['host'] : null;
        $schemes = isset($config['schemes']) ? $config['schemes'] : null;
        $methods = isset($config['methods']) ? $config['methods'] : null;

        // Don't know why Symfony does this, causes to look in wrong directory..
        // $this->setCurrentDir(dirname($path));
        
        $subCollection = $this->import($config['resource'], $type, false, $file);
        
        /* @var $subCollection RouteCollection */
        $subCollection->addPrefix($prefix);
        if (null !== $host) {
            $subCollection->setHost($host);
        }
        if (null !== $schemes) {
            $subCollection->setSchemes($schemes);
        }
        if (null !== $methods) {
            $subCollection->setMethods($methods);
        }
        $subCollection->addDefaults($defaults);
        $subCollection->addRequirements($requirements);
        $subCollection->addOptions($options);

        $collection->addCollection($subCollection);
    }

    /**
     * Validates the route configuration.
     *
     * @param array  $config A resource config
     * @param string $name   The config key
     * @param string $path   The loaded file path
     *
     * @throws \InvalidArgumentException If one of the provided config keys is not supported,
     *                                   something is missing or the combination is nonsense
     */
    protected function validate($config, $name, $path) {
        
        if (!is_array($config)) {
            throw new \InvalidArgumentException(sprintf('The definition of "%s" in "%s" must be a YAML array.', $name, $path));
        }
        if ($extraKeys = array_diff(array_keys($config), self::$availableKeys)) {
            throw new \InvalidArgumentException(sprintf(
                'The routing file "%s" contains unsupported keys for "%s": "%s". Expected one of: "%s".',
                $path, $name, implode('", "', $extraKeys), implode('", "', self::$availableKeys)
            ));
        }
        if (isset($config['resource']) && isset($config['path'])) {
            throw new \InvalidArgumentException(sprintf(
                'The routing file "%s" must not specify both the "resource" key and the "path" key for "%s". Choose between an import and a route definition.',
                $path, $name
            ));
        }
        if (!isset($config['resource']) && isset($config['type'])) {
            throw new \InvalidArgumentException(sprintf(
                'The "type" key for the route definition "%s" in "%s" is unsupported. It is only available for imports in combination with the "resource" key.',
                $name, $path
            ));
        }
        if (!isset($config['resource']) && !isset($config['path'])) {
            throw new \InvalidArgumentException(sprintf(
                'You must define a "path" for the route "%s" in file "%s".',
                $name, $path
            ));
        }
    }
    
}
