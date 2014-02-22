<?php
namespace Sea\Routing\Annotations;

use Symfony\Component\Routing\Loader\AnnotationClassLoader;
use Sea\Routing\Annotations\Method;

/**
 * Description of AnnotationLoader
 *
 * @author Sebastiaan Marynissen <Sebastiaan.Marynissen@UGent.be>
 */
class AnnotationLoader extends AnnotationClassLoader {
    
    /**
     * Configures the controller
     * 
     * @param \Symfony\Component\Routing\Route $route
     * @param \ReflectionClass $class
     * @param \ReflectionMethod $method
     * @param type $annot
     */
    protected function configureRoute(\Symfony\Component\Routing\Route $route, \ReflectionClass $class, \ReflectionMethod $method, $annot) {
        
        // Look for the "Method" annotation, as well as the different @Put etc.
        foreach ($this->reader->getMethodAnnotations($method) as $config) {
            if ($config instanceof Method) {
                $route->setMethods($config->getMethods());
            }
        }
        
        $route->setDefault('_controller', sprintf('%s::%s', $class->getName(), $method->getName()));
    }
    
}
