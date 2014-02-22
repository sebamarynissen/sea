Sea
===

Symfony2 is great, however, often the complete framework is overkill. Therefore I wrote my own "glue" between the components I most use and decided to call it "Sea".

I mainly did this for personal use, but feel free to use it. I won't provide any documentation though, but feel free to run PHPDocumentor on it if you need some explanation.

Usage
===

In your front controller, put
```php
use Sea\Sea;

// Get composers ClassLoader to pass to Sea. This is needed because Doctrine 
// Annotations cannot be autoload by simply including composer. The loader needs
// to be explicitly specified to the AnnotationRegistry.
$composer = require './vendor/autoload.php';

// Specify the path to the json file containing all routes. The json file is
// similar to a Symfony routing.yaml file. The same fields can be specified.
// If your json file depends on other file resources, the paths need to be
// defined relatively.
// Alternatively you can pass an instance of a Symfony RouteCollection directly.
$sea = new Sea($composer);
$sea->routing('path/to/routes.json');

// Process the request, and send the response that was returned. Note that the
// response is not sent by default. This allows you to simulate requests and
// responses internally, without the responses being sent automatically.
$sea->run()->send();
```
