<?php
namespace Sea\Routing;

use Sea\DependencyInjection\ServiceContainer;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Subscribes for the FilterControllerEvent to do some stuff
 *
 * @author Sebastiaan Marynissen <Sebastiaan.Marynissen@UGent.be>
 */
class ControllerListener implements EventSubscriberInterface {
    
    /**
     * A Sea ServiceContainer instance
     * 
     * @var ServiceContainer
     */
    protected $container;
    
    /**
     * Returns for what Events the controller subscribes
     * 
     * @return type
     */
    public static function getSubscribedEvents() {
        return array(
            KernelEvents::CONTROLLER => array(
                array('injectContainer', 1)
            )
        );
    }
    
    /**
     * Constructs the subscriber by providing it a ServiceCOntainer instance
     * which should be injected into the controller
     * 
     * @param ServiceContainer $container
     */
    public function __construct(Container $container) {
        $this->container = $container;
    }
    
    /**
     * Injects the servicecontainer into the controller
     * 
     * @param FilterControllerEvent $e
     */
    public function injectContainer(FilterControllerEvent $e) {
        $controller = $e->getController();
        $obj = $controller[0];
        $obj->setContainer($this->container);
    }
    
}
