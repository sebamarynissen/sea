Sea
===

Symfony2 is great, however, often the complete framework is overkill. Therefore I wrote my own "glue" between the components I most use and decided to call it "Sea".

I mainly did this for personal use, but feel free to use it. I won't provide any documentation though, but feel free to run PHPDocumentor on it if you need some explanation.

Usage
===

In your front controller, put

```php
use Sea\Sea;

$composer = require './vendor/autoload.php';
new Sea($composer);
```
