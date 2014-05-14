<?php
namespace Sea\Routing;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Sea\DependencyInjection\ServiceContainer;
use Sea\HttpKernel\PreLoadInterface;

/**
 * Subscribes for the FilterControllerEvent to do some stuff
 *
 * @author Sebastiaan Marynissen <Sebastiaan.Marynissen@UGent.be>
 */
class ControllerListener implements EventSubscriberInterface {
    
    /**
     * A Sea ServiceContainer instance
     * 
     * @var \Sea\DependencyInjection\ServiceContainer
     */
    protected $container;
    
    /**
     * The request which is currently being handled
     * 
     * @var Request 
     */
    protected $request;
    
    /**
     * Returns for what Events the controller subscribes
     * 
     * @return type
     */
    public static function getSubscribedEvents() {
        return array(
            KernelEvents::CONTROLLER => array(
                array('injectContainer', 1),
                array('checkBeforeLoad', 0)
            )
        );
    }
    
    /**
     * Constructs the subscriber by providing it a ServiceCOntainer instance
     * which should be injected into the controller
     * 
     * @param \Sea\DependencyInjection\ServiceContainer $container
     */
    public function __construct(Request $request, ServiceContainer $container) {
        $this->request = $request;
        $this->container = $container;
    }
    
    /**
     * Injects the servicecontainer into the controller
     * 
     * @param \Symfony\Component\HttpKernel\Event\FilterControllerEvent $e
     */
    public function injectContainer(FilterControllerEvent $e) {
        $controller = $e->getController();
        $obj = $controller[0];
        $obj->setContainer($this->container);
        $obj->setRequest($this->request);
        $obj->setSession($this->request->getSession());
    }
    
    /**
     * Checks whether the constructed controller implements the PreLoadInterface
     * 
     * If this is the case, the preLoad() method is called in order to let the
     * controller specify its preload behavior.
     * 
     * @param \Symfony\Component\HttpKernel\Event\FilterControllerEvent $e
     */
    public function checkBeforeLoad(FilterControllerEvent $e) {
        // Check whether the controller class implements the PreLoad interface
        // If this is the case, call the implemented preLoad method which will
        // execute some fancy stuff!
        $controller = $e->getController();
        $obj = $controller[0];
        if ($obj instanceof PreLoadInterface) {
            $obj->preLoad();
        }
    }
    
}
