<?php
use Sea\Sea;

// Get composers ClassLoader to pass to Sea. This is needed because Doctrine 
// Annotations cannot be autoload by simply including composer. The loader needs
// to be explicitly specified to the AnnotationRegistry.
$composer = require './vendor/autoload.php';

// Instantiate the Sea class. The Sea class is the actual framework class.
// When using Annotations - in which Sea heavily believes, an autoloader should
// be passed to the AnnotationRegistry, therefore composers autoloader is
// injected into the sea object.
$sea = new Sea($composer);

// Specify the path to the json file containing all routes. The json file is
// similar to a Symfony routing.yaml file. The same fields can be specified.
// If your json file depends on other file resources, the paths need to be
// defined relatively.
// Alternatively you can pass an instance of a Symfony RouteCollection directly.
$sea->routing('config/routes.json');

// You can define your own ServiceContainers by extending the
// Sea\ServiceContainer class and specifying your services, as you would do in
// a Symfony project. If you don't need any specific services, just pass null
// and the default Sea\ServiceContainer will be used, only containing some
// essential Sea Services.
$sea->services(null);

// Process the request, and send the response that was returned. Note that the
// response is not sent by default. This allows you to simulate requests and
// responses internally, without the responses being sent automatically.
//$sea->run()->send();
$resp = $sea->run()->send();