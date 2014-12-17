<?php
namespace Sea\Routing;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\Matcher\RequestMatcherInterface;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\Matcher\UrlMatcherInterface;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RouterInterface;

/**
 * Sea's router
 * 
 * The reason why the default Syfmony router isn't used is the fact that
 * prefixed routes will cause issues with the ending trailing slash etc. This is
 * not practical, and is therefore changed
 * 
 * @author Sebastiaan Marynissen <sebastiaan.marynissen@gmail.com>
 */
class Router implements RouterInterface, RequestMatcherInterface {
    
    /**
     * A collection of routes in the router
     * 
     * @var RouteCollection
     */
    protected $collection;
    
    /**
     * Generates urls
     * 
     * @var UrlGenerator
     */
    protected $generator;
    
    /**
     * The RequestContext
     * 
     * @var RequestContext
     */
    protected $context;
    
    /**
     * The UrlMatcher
     * 
     * @var UrlMatcher
     */
    protected $matcher;
    
    /**
     * Constructor
     * 
     * @param RouteCollection $collection
     */
    public function __construct(RouteCollection $collection) {
        // Symfony's default behavior when a prefix is specified is, that 
        // root/prefix/ and root/prefix are not the same. In Sea's view,
        // they ARE the same, therefore, rewrite the routes as a fix.
        $this->collection = $collection;
        foreach ($this->collection as $route) {
            $path = $route->getPath();
            if (preg_match('/\/$/', $path)) {
                $route->setPath(preg_replace('/\/$/', '', $path));
            }
        }
    }
    
    /**
     * Returns the collection of routes that was set for the router
     * 
     * @return RouteCollection
     */
    public function getRouteCollection() {
        return $this->collection;
    }

    /**
     * Returns the generator, which is constructed on the fly for performance
     * reasons
     * 
     * @return UrlGenerator
     */
    public function getGenerator() {
        if ($this->generator === null) {
            // Setup the generator, which will generate urls from route names
            $this->generator = new UrlGenerator($this->collection, null);
        }
        return $this->generator;
    }
    
    /**
     * Generates a url for a named route
     * 
     * @param string $name The route name
     * @param array $parameters The parameters to pass, as an associative array
     * @param boolean $referenceType A reference type
     * @return string A route as a string
     */
    public function generate($name, $parameters = array(), $referenceType = self::ABSOLUTE_PATH) {
        return $this->getGenerator()->generate($name, $parameters, $referenceType);
    }

    /**
     * Returns the requestcontext
     * 
     * @return RequestContext
     */
    public function getContext() {
        return $this->context;
    }

    /**
     * Matches a request based on the pathinfo
     */
    public function match($pathinfo) {
        return $this->getMatcher()->match($pathinfo);
    }

    /**
     * Matches an actual request
     */
    public function matchRequest(Request $request) {
        $matcher = $this->getMatcher();
        if (!$matcher instanceof RequestMatcherInterface) {
            // fallback to the default UrlMatcherInterface
            return $matcher->match($request->getPathInfo());
        }
        return $matcher->matchRequest($request);
    }

    /**
     * Gets the UrlMatcher instance associated with this Router.
     *
     * @return UrlMatcherInterface A UrlMatcherInterface instance
     */
    public function getMatcher() {
        $this->matcher = new UrlMatcher($this->collection, $this->context);
        return $this->matcher;
    }

    /**
     * Sets the RequestContext
     * 
     * @param RequestContext $context
     */
    public function setContext(RequestContext $context) {
        $this->context = $context;
        return $this;
    }

}
