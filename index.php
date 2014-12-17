<?php
// Require the configuration function. This returns a function which should be
// called by specifying it a resource where it can find the entire configuration
// It should be possible to reference other configuration resources etc.
$config = require './config.php';

// Instantiate the Sea class. The Sea class is the actual framework class.
$sea = new Sea\Sea($config('config.json'));

// Process the request, and send the response that was returned. Note that the
// response is not sent by default. This allows you to simulate requests and
// responses internally, without the responses being sent automatically.
$resp = $sea->handle()->send();