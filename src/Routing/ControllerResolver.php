<?php
namespace Sea\Routing;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ControllerResolver as BaseResolver;

/**
 * Resolves the controller
 *
 * @author Sebastiaan Marynissen <Sebastiaan.Marynissen@UGent.be>
 */
class ControllerResolver extends BaseResolver {

    /**
     * The request being handled
     * 
     * @var Request
     */
    protected $request;
    
    /**
     * {@inheritdoc}
     * 
     * @param \Symfony\Component\HttpFoundation\Request $request The request
     * being handled
     */
    public function getController(Request $request) {
        $this->request = $request;
        return parent::getController($request);
    }
    
    /**
     * Creates the controller callable
     * 
     * Note that the controller should extend the Sea\Controller class!
     * 
     * @param string $controller Controller string
     * @return callable
     * @throws \InvalidArgumentException If controller could not be loaded
     */
    protected function createController($controller) {
        
        // Make sure controller can be found
        if (false === strpos($controller, '::')) {
            throw new \InvalidArgumentException(sprintf('Unable to find controller "%s".', $controller));
        }

        list($class, $method) = explode('::', $controller, 2);

        // Make sure the controller exists
        if (!class_exists($class)) {
            throw new \InvalidArgumentException(sprintf('Class "%s" does not exist.', $class));
        }
        
        // Create the callable
        return array(new $class($this->request), $method);
        
    }
    
}
