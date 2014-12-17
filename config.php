<?php
use Sea\Config\Loaders\ConfigLoader;
use Symfony\Component\Config\FileLocator;

// Include composer
$composer = require './vendor/autoload.php';

/**
 * The function setting up the configuration
 */
return function($resource) use ($composer) {
    
    // Load and return the configuration
    $locator = new FileLocator(array('config'));
    $loader = new ConfigLoader($locator, $composer);
    $info = pathinfo($resource);
    $config = $loader->load($resource, $info['extension']);
    return $config;
    
};