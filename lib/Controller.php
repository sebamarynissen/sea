<?php
namespace Sea;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\DependencyInjection\ContainerAware;

/**
 * Controller base class
 * 
 * All custom controllers should extend this class
 *
 * @author Sebastiaan Marynissen <Sebastiaan.Marynissen@UGent.be>
 */
abstract class Controller extends ContainerAware {
    
    /**
     * The request being handled
     * 
     * @var Request
     */
    protected $request;

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
     * Note that it is still necessary to return the response that this method
     * returns!
     * 
     * @param string|Response $content Response content or a response itself
     * @param int $status HTTP status
     * @param array $headers Headers
     * @return \Symfony\Component\HttpFoundation\Response
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
    
    /**
     * Gets a service by id
     * 
     * Note: It is suggested to extend Sea\Controller with your own getService()
     * methods. For instance, if you have a custom EntityManager, it is
     * suggested to implement getEntityManager() as
     * 
     * public function getEntityManager() {
     *     return $this->get('managerId');
     * }
     * 
     * and specify the return value in the docblock!
     * 
     * @param string $id The service id
     * @return object The service
     */
    public function get($id) {
        return $this->container->get($id);
    }
    
}
