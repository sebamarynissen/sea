<?php
namespace Sea;

use Composer\Autoload\ClassLoader;
use Sea\Routing\ControllerResolver;
use Sea\Routing\JsonFileLoader;
use Sea\Routing\RestRouteLoader;
use Sea\Routing\Annotations\AnnotationLoader;
use Sea\Exception\RoutesNotFoundException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Config\Loader\DelegatingLoader;
use Symfony\Component\Config\Loader\LoaderResolver;
use Symfony\Component\Security\Core\SecurityContext;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\AnnotationReader;

/**
 * Sea PHP framework
 * 
 * Call new Sea() in your index file to initialize all stuff
 *
 * @author Sebastiaan Marynissen <Sebastiaan.Marynissen@UGent.be>
 */
class Sea {
    
    /**
     * The arguments that were passed to the controller currently loaded
     * 
     * @var array
     */
    protected $arguments;
    
    /**
     * Composer's autoloader
     * 
     * @var ClassLoader
     */
    protected $composer;
    
    /**
     * The context of the request currently being processed
     * 
     * @var RequestContext
     */
    protected $context;
    
    /**
     * The controller currently loaded
     * 
     * @var Sea\Controller
     */
    protected $controller;
    
    /**
     *
     * @var DelegatingLoader
     */
    protected $loader;
    
    /**
     * Symfony's url matcher
     * 
     * @var UrlMatcher
     */
    protected $matcher;
    
    /**
     * Sea's controller resolver, which extends Symfony's resolver
     * 
     * @var ControllerResolver
     */
    protected $resolver;
    
    /**
     * The request currently being handled
     * 
     * @var Request
     */
    protected $request;
    
    /**
     * All routes that were registered
     * 
     * @var RouteCollection
     */
    protected $routes;
    
    /**
     * Symfony's session object
     * 
     * @var Session
     */
    protected $session;
    
    /**
     * Initializes the framework.
     * 
     * After calling the constructor the requested route will be served
     * 
     * @param \Composer\Autoload\ClassLoader $composer Composer's ClassLoader.
     */
    public function __construct(ClassLoader $composer) {
        // Store composers autoloader and register it as the autoloader for
        // doctrine's annotations since Doctrine doesn't supported classes
        // autoloaded by composer
        $this->composer = $composer;
        AnnotationRegistry::registerLoader(function($class) use ($composer) {
            return $composer->loadClass($class);
        });
        $this->resolver = new ControllerResolver();
        $this->session = new Session();
    }

    /**
     * Creates a context for the request being processed
     * 
     * @return \Sea\Sea Fluent interface
     */
    protected function createContext() {
        $this->context = new RequestContext();
        $this->context->fromRequest($this->request);
        return $this;
    }
    
    /**
     * Creates a URL Matcher for the current request
     * 
     * @return \Sea\Sea Fluent interface
     */
    protected function createMatcher() {
        $this->matcher = new UrlMatcher($this->routes, $this->context);
        return $this;
    }
    
    /**
     * Creates a ControllerResolver
     * 
     * @return \Sea\Sea Fluent interface
     */
    protected function createResolver() {
        $this->resolver = new ControllerResolver();
        return $this;
    }
    
    /**
     * Fetches the arguments to pass to the controller
     * 
     * @return \Sea\Sea Fluent interface
     */
    protected function fetchArguments() {
        $this->arguments = $this->resolver->getArguments($this->request, $this->controller);
        return $this;
    }
    
    /**
     * Fetches the controller to load
     * 
     * @return \Sea\Sea Fluent interface
     */
    protected function fetchController() {
        $this->request->attributes->add($this->matcher->matchRequest($this->request));
        $this->controller = $this->resolver->getController($this->request);
        return $this;
    }
    
    /**
     * Sets a Symfony DelegatingLoader if needed
     * 
     * @param string[] $paths The paths to look for the resources
     * @return \Sea\Sea Fluent interface
     */
    protected function setDelegatingLoader($paths) {
        // IMPORTANT: Order of the loaders is important!
        $resolver = new LoaderResolver(array(
            new RestRouteLoader($paths),
            new JsonFileLoader($paths),
            new AnnotationLoader(new AnnotationReader())
        ));
        $this->loader = new DelegatingLoader($resolver);
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
            $this->setDelegatingLoader($paths);
            $this->routes = $this->loader->load($info['basename']);
        }
        
        // TODO: Better way to implement /events/ as /events etc.
        foreach ($this->routes as $route) {
            $path = $route->getPath();
            if (preg_match('/\/$/', $path)) {
                $route->setPath(preg_replace('/\/$/', '', $path));
            }
        }
        return $this;
    }
    
    /**
     * Runs the application
     * 
     * @param \Symfony\Component\HttpFoundation\Request $request [Optional] If
     * not specified, the request is created from the PHP globals
     * @return Response Returns a Symfony response that is ready to be sent to
     * the client
     */
    public function run(Request $request = null) {
        
        // If the routes were not initialized yet, notify the user that he
        // should provide a file which will be used to fetch the routes. Any
        // file resources that the previous file specifies, should be located in
        // the same folder!
        if (! ($this->routes instanceof RouteCollection)) {
            throw new RoutesNotFoundException('No RouteCollection was found! Make sure to call Sea::routing()!');
        }
        
        // If no - perhaps simulated - request wa specified, create from globals
        if ($request === null) {
            $this->request = Request::createFromGlobals();
        }
        else {
            $this->request = $request;
        }
        
        // Handle all errors within a try-catch block
        try {
            
            // Prepare all needed Symfony components to resolve the controller
            $this
                    ->createContext()
                    ->createMatcher()
                    ->createResolver()
                    ->setSession()
                    ->fetchController()
                    ->fetchArguments();
            
            // Now, when everything is loaded, call the controller
            $response = call_user_func_array($this->controller, $this->arguments);
            
            // Send a response. If no response was set, an empty response is generated
            if (!($response instanceof Response)) {
                // Get the actual controller object, and NOT the PHP callable
                // which is an array!
                $response = $this->controller[0]->getResponse();
            }
            
        }
        catch (\ReflectionException $e) {
            $response = new Response('A reflection exception occured! Probably the specified controller does not exist!' . $e, 500);
            $response->headers->set('Content-type', 'text/plain');
        }
        catch (ResourceNotFoundException $e) {
            $response = new Response('Not found', 404);
        }
        catch (\Exception $e) {
            $response = new Response('Internal server error. Exception was: ' . $e, 500);
            $response->headers->set('Content-type', 'text/plain');
        }
        return $response;
        
    }
    
    /**
     * Injects the Session object in the request that is being handled currently
     * 
     * @return \Sea\Sea Fluent interface
     */
    public function setSession() {
        $this->request->setSession($this->session);
        return $this;
    }
    
}
