<?php
// Require the configuration function. This returns a function which should be
// called by specifying it a resource where it can find the entire configuration
// It should be possible to reference other configuration resources etc.
$config = require './config.php';

// Instantiate the Sea class. The Sea class is the actual framework class.
// When using Annotations - in which Sea heavily believes, an autoloader should
// be passed to the AnnotationRegistry, therefore composers autoloader is
// injected into the sea object.
$sea = new Framework($config('config.json'));

// You can define your own ServiceContainers by extending the
// Sea\ServiceContainer class and specifying your services, as you would do in
// a Symfony project. If you don't need any specific services, just pass null
// and the default Sea\ServiceContainer will be used, only containing some
// essential Sea Services.
// $sea->services('services.php');

// After all dependencies are injected, we'll start the framework.
$sea->start();

// Process the request, and send the response that was returned. Note that the
// response is not sent by default. This allows you to simulate requests and
// responses internally, without the responses being sent automatically.
$resp = $sea->handle()->send();