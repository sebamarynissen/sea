<?php
namespace Sea;

use Composer\Autoload\ClassLoader as Composer;
use Symfony\Component\Config\Loader\DelegatingLoader;
use Symfony\Component\Config\Loader\LoaderResolver;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpKernel\Controller\ControllerResolver;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\HttpKernel\EventListener\RouterListener;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\AnnotationReader;
use Sea\Routing\JsonFileLoader;
use Sea\Routing\RestRouteLoader;
use Sea\Routing\Annotations\AnnotationLoader;
use Sea\Routing\ControllerListener;
use Sea\Exception\RoutesNotFoundException;
use Sea\DependencyInjection\ServiceContainer;

/**
 * Main entry point for the Sea framework
 *
 * @author Sebastiaan Marynissen <Sebastiaan.Marynissen@UGent.be>
 * @todo Add a Router class...
 */
class Sea extends HttpKernel {
    
    /**
     * Composers class loader instance
     * 
     * @var \Composer\Autoloader\ClassLoader
     */
    protected $composer;
    
    /**
     * All routes that are loaded for the framework
     * 
     * @var RouteCollection
     */
    protected $routes;
    
    /**
     * A container with all loaded services
     * 
     * @var ServiceContainer
     */
    protected $services;
    
    /**
     * Constructs the Sea framework and prepares it to handle requests.
     * 
     * @param \Composer\Autoload\ClassLoader $composer Composers autoloader
     * @todo Should this be done in the constructor or not???
     */
    public function __construct(Composer $composer) {
        $this->registerComposer($composer);
        parent::__construct(new EventDispatcher(), new ControllerResolver());
    }
    
    /**
     * Registers composers class loader as an autoloader for Doctrine
     * 
     * @param \Composer\Autoload\ClassLoader $composer
     * @return \Sea\Sea
     */
    private function registerComposer(Composer $composer) {
        $this->composer = $composer;
        AnnotationRegistry::registerLoader(function($class) use ($composer) {
            return $composer->loadClass($class);
        });
        return $this;
    }
    
    /**
     * Initializes all routes
     * 
     * @param RouteCollection|string $routes A RouteCollection, or a path to a
     * json file specifying the different routes. Note that if this path
     * uses other file resources, those paths should be relative to the given
     * path!
     * @return \Sea\Sea Fluent interface
     */
    public function routing($routes) {
        if ($routes instanceof RouteCollection) {
            $this->routes = $routes;
        }
        elseif (is_string($routes)) {
            $info = pathinfo($routes);
            $paths = array($info['dirname']);
            $loader = $this->getRoutesLoader($paths);
            $this->routes = $loader->load($info['basename']);
        }
        
        // Symfony's default behavior when a prefix is specified is, that 
        // root/prefix/ and root/prefix are not the same. In Sea's view, they
        // ARE the same, therefore, rewrite the routes as a fix.
        foreach ($this->routes as $route) {
            $path = $route->getPath();
            if (preg_match('/\/$/', $path)) {
                $route->setPath(preg_replace('/\/$/', '', $path));
            }
        }
        return $this;
    }
    
    /**
     * Sets a Symfony DelegatingLoader to load the routes
     * 
     * @param string[] $paths The paths to look for the resources
     * @return \Sea\Sea Fluent interface
     */
    protected function getRoutesLoader($paths) {
        // IMPORTANT: Order of the loaders is important!
        $resolver = new LoaderResolver(array(
            new RestRouteLoader($paths),
            new JsonFileLoader($paths),
            new AnnotationLoader(new AnnotationReader())
        ));
        return new DelegatingLoader($resolver);
    }
    
    /**
     * Registers all services...
     * 
     * @param type $services
     * @return \Sea\Sea
     */
    public function services($services) {
        // TODO: Implement
        $this->services = new ServiceContainer();
        $this->services->setSea($this);
        return $this;
    }
    
    public function run(Request $request = null) {
        
        // First of all, make sure that the routes were loaded
        $this->checkRoutes();
        
        // If no request was specified, a request should be created from the
        // global variables
        if (is_null($request)) {
            $request = Request::createFromGlobals();
        }
        
        // Register a RouterListener, which listens to a KernelRequest Event.
        // This RouterListener will then be responsible for calling the
        // appropriate controller etc. and thus act as a router.
        $context = new RequestContext();
        $matcher = new UrlMatcher($this->routes, $context);
        $listener = new RouterListener($matcher);
        $this->dispatcher->addSubscriber($listener);
        
        // Add the subscriber which injects the service container into the
        // controller and also checks whether some preLoad method should be
        // implemented (which depends on whether the PreLoadInterface is 
        // implemented or not).
        $controllerListener = new ControllerListener($request, $this->services);
        $this->dispatcher->addSubscriber($controllerListener);
        
        // Handle the request
        return $this->handle($request);
        
    }
    
    /**
     * Checks whether a RouteCollection was specified. If not, an exception is
     * thrown
     * 
     * @return \Sea\Sea Fluent interface
     * @throws RoutesNotFoundException If no RouteCollection was found
     */
    protected function checkRoutes() {
        
        // If the routes were not initialized yet, notify the user that he
        // should provide a file which will be used to fetch the routes. Any
        // file resources that the previous file specifies, should be located in
        // the same folder!
        if ( !($this->routes instanceof RouteCollection) ) {
            throw new RoutesNotFoundException('No RouteCollection was found! Make sure to call Sea::routing()!');
        }
        
        return $this;
        
    }
    
    /**
     * Returns Sea's main EventDispatcher, to be able to hook into some events
     * 
     * To use this in your controller, call:
     * 
     * $dispatcher = $this->getSea()->getDispatcher();
     * 
     * And you're ready to go
     * 
     * @return EventDispatcher
     */
    public function getDispatcher() {
        return $this->dispatcher;
    }
    
}
