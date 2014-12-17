<?php
namespace Sea\Config\Loaders\Configure;

use Sea\Sea;
use Symfony\Component\Config\FileLocatorInterface;
use Symfony\Component\Config\Loader\FileLoader;

/**
 * The laoder which loads a php file which will define the configure function
 * 
 * @author Sebastiaan Marynissen <sebastiaan.marynissen@gmail.com>
 */
class PhpConfigurerLoader extends FileLoader {
    
    /**
     * The sea framework instance (which is the kernel)
     * 
     * @var Sea
     */
    protected $sea;
    
    /**
     * Constructor
     * 
     * @param Sea $sea The Sea kernel. Will be passed to the configure function
     * @param FileLocatorInterface $locator A filelocator
     */
    public function __construct(Sea $sea, FileLocatorInterface $locator) {
        parent::__construct($locator);
        $this->sea = $sea;
    }
    
    /**
     * Loads the configurer, which means calls the configure script
     * 
     * @param string $resource The configure script
     * @param string $type The type
     */
    public function load($resource, $type = null) {
        // The included file will know about sea
        $sea = $this->sea;
        $path = $this->locator->locate($resource);
        
        // Configure
        include $path;
        
    }

    /**
     * Whether the resource is supported
     * 
     * @param string $resource The resource
     * @param string $type The resource type
     * @return boolean Supported or not
     */
    public function supports($resource, $type = null) {
        return is_string($resource) && 'php' === pathinfo($resource, PATHINFO_EXTENSION);
    }

}
