<?php
namespace Sea\HttpKernel;

use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Controller base class
 * 
 * All custom controllers should extend this class
 *
 * @author Sebastiaan Marynissen <Sebastiaan.Marynissen@UGent.be>
 */
abstract class Controller extends ContainerAware {
    
    /**
     * Creates a new response object and returns it
     * 
     * This function can be used in order to not always have to use
     * new \Symfony\...\Response etc.
     * Note that it is still necessary to return the response that this method
     * returns!
     * 
     * @param string|Response $content Response content or a response itself
     * @param int $status HTTP status
     * @param array $headers Headers
     * @return Response
     */
    protected function response($content = '', $status = 200, $headers = array()) {
        return new Response($content, $status, $headers);
    }
    
    /**
     * Sets the response that will be sent to a redirectresponse
     * 
     * @param string $url Url to redirect to
     * @param int $status HTTP status code
     * @param array $headers Array of additional headers
     * @return RedirectResponse A redirectresponse, redirecting the user to the
     * given url
     */
    protected function redirect($url, $status = 302, $headers = array()) {
        return new RedirectResponse($url, $status, $headers);
    }
    
}
