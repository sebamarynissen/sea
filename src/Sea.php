<?php
namespace Sea;

use Sea\Config\Configuration;
use Sea\Config\ConfigurationInterface;
use Sea\Routing\ControllerListener;
use Sea\Routing\Router as SeaRouter;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ControllerResolver;
use Symfony\Component\HttpKernel\EventListener\RouterListener;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Router;

/**
 * Main entry point for the Sea framework
 *
 * @author Sebastiaan Marynissen <Sebastiaan.Marynissen@UGent.be>
 * @todo Add a Router class...
 */
class Sea extends HttpKernel {
    
    /**
     * The router which is set
     * 
     * @var Router
     */
    protected $router;
    
    /**
     * A PhpFileLoader to load the service definitions defined in php files
     * 
     * @var PhpFileLoader
     */
    protected $containerLoader;
    
    /**
     * Composers class loader instance
     * 
     * @var \Composer\Autoloader\ClassLoader
     */
    protected $composer;
    
    /**
     * A container with all loaded services
     * 
     * @var ContainerBuilder
     */
    protected $container;
    
    /**
     * The dispatcher that was set
     * 
     * @var EventDispatcher
     */
    protected $dispatcher;
    
    /**
     * Constructs the Sea framework and prepares it to handle requests.
     * 
     * @param Configuration $config The configuration object. This should be the
     * return value of the configuration function in config.php
     */
    public function __construct(ConfigurationInterface $config) {
        parent::__construct(new EventDispatcher(), new ControllerResolver());
        $this->container = $config->getServiceContainer();
        $this->router = new SeaRouter($config->getRouteCollection());
        $this->registerListeners();
        $config->configure($this);
    }
    
    /**
     * Registers all listeners on the dispatcher
     * 
     * @return Sea Fluent interface
     */
    private function registerListeners() {
        
        // Register a RouterListener, which listens to a KernelRequest Event.
        // This RouterListener will then be responsible for calling the
        // appropriate controller etc. and thus act as a router.
        $listener = new RouterListener($this->router);
        $this->dispatcher->addSubscriber($listener);
        
        // Add the subscriber which injects the service container into the
        // controller and also checks whether some preLoad method should be
        // implemented (which depends on whether the PreLoadInterface is 
        // implemented or not).
        $controllerListener = new ControllerListener($this->container);
        $this->dispatcher->addSubscriber($controllerListener);
        return $this;
    }
    
    /**
     * Handles the request
     * 
     * @param Request $request
     * @return type
     */
    public function handle(Request $request = null, $type = HttpKernel::MASTER_REQUEST, $catch = true) {
        
        // If no request was specified, a request should be created from the
        // global variables
        if (is_null($request)) {
            $request = Request::createFromGlobals();
            if (0 === strpos($request->headers->get('CONTENT_TYPE'), 'application/json')) {
                $data = json_decode($request->getContent(), true);
                $request->request = new ParameterBag($data);
            }
        }
        $context = new RequestContext();
        $context->fromRequest($request);
        $this->router->setContext($context);
        
        // Handle the request
        $response = parent::handle($request, $type, $catch);
        return $response;
        
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
    
    /**
     * Returns all routes that were loaded
     * 
     * @return RouteCollection
     */
    public function getRoutes() {
        return $this->router->getRouteCollection();
    }
    
    /**
     * All loaded dependencies in the DependencyContainer
     * 
     * @return \Symfony\Component\DependencyInjection\ContainerInterface
     */
    public function getServices() {
        return $this->container;
    }
    
}
