<?php
namespace Sea;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * Controller base class
 * 
 * All custom controllers should extend this class
 *
 * @author Sebastiaan Marynissen <Sebastiaan.Marynissen@UGent.be>
 */
abstract class Controller {
    
    /**
     * The request being handled
     * 
     * @var Request
     */
    protected $request;
    
    /**
     * The response being created
     * 
     * @var Response
     */
    protected $response;

    /**
     * Symfony's session object
     * 
     * @var Session
     */
    protected $session;
    
    /**
     * Constructor
     * 
     * Called by the Sea class. Do not override!
     * Override Controller::initialize() if you want to perform some
     * initialisation for each request handled by this controller
     * 
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    public function __construct(Request $request) {
        $this->request = $request;
        $this->session = $request->getSession();
        $this->initialize();
    }
    
    /**
     * Does nothing
     * 
     * Override this function to specify the initialization of the controller,
     * for example for user authentication etc.
     */
    protected function initialize() {
        // Do nothing, but be there to be overridden
    }
    
    /**
     * Creates a new response object and returns it
     * 
     * This function can be used in order to not always have to use
     * new \Symfony\...\Response etc.
     * 
     * @param string|Response $content Response content or a response itself
     * @param int $status HTTP status
     * @param array $headers Headers
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function response($content = '', $status = 200, $headers = array()) {
        if ($content instanceof Response) {
            $this->response = $content;
        }
        else {
            $this->response = new Response($content, $status, $headers);
        }
        return $this->response;
    }
    
    /**
     * Sets the response that will be sent to a redirectresponse
     * 
     * @param string $url Url to redirect to
     * @param int $status HTTP status code
     * @param array $headers Array of additional headers
     * @return RedirectResponse The response that was set
     */
    protected function redirect($url, $status = 302, $headers = array()) {
        $this->response = new RedirectResponse($url, $status, $headers);
        return $this->response;
    }
    
    /**
     * Returns the response that was specified by the controller
     * 
     * If no response was set yet, an empty response is returned
     * 
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getResponse() {
        if (!isset($this->response)) {
            return new Response('EMPTY RESPONSE, CODE = 200 FOR DEBUGGING PURPOSES', 200);
        }
        else {
            return $this->response;
        }
    }
    
}
