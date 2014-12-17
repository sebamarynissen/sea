<?php
namespace Sea\Routing\Annotations;

use ReflectionClass;
use ReflectionMethod;
use Sea\Routing\Annotations\Method;
use Symfony\Component\Routing\Loader\AnnotationClassLoader;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * Description of AnnotationLoader
 *
 * @author Sebastiaan Marynissen <Sebastiaan.Marynissen@UGent.be>
 */
class AnnotationLoader extends AnnotationClassLoader {
    
    /**
     * Configures the controller
     * 
     * @param Route $route
     * @param ReflectionClass $class
     * @param ReflectionMethod $method
     * @param type $annot
     */
    protected function configureRoute(Route $route, ReflectionClass $class, ReflectionMethod $method, $annot) {
        
        // Look for the "Method" annotation, as well as the different @Put etc.
        foreach ($this->reader->getMethodAnnotations($method) as $config) {
            if ($config instanceof Method) {
                $route->setMethods($config->getMethods());
            }
        }
        
        $route->setDefault('_controller', sprintf('%s::%s', $class->getName(), $method->getName()));
    }
    
    /**
     * Loads all routes associated with the given class.
     * 
     * Extends Symfony's AnnotationClassLoader::load() to add a prefix if 
     * specified
     * 
     * @param ReflectionClass $class
     * @param type $type
     * @return RouteCollection
     */
    public function load($class, $type = null) {
        $collection = parent::load($class, $type);
        $class = new ReflectionClass($class);
        // Check for "Prefix" annotations and if so, add a prefix
        $prefix = $this->reader->getClassAnnotation($class, 'Sea\\Routing\\Annotations\\Prefix');
        if ($prefix) {
            $collection->addPrefix($prefix->getPrefix());
        }
        return $collection;
    }
    
}
