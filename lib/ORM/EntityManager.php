<?php
namespace Sea\ORM;

// Dependencies
use Doctrine\ORM\EntityManager as BaseManager;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\Configuration;
use Doctrine\DBAL\Driver\PDOMySql\Driver;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\Common\EventManager;

/**
 * An extension of Doctrine's EntityManager
 *
 * @author Sebastiaan Marynissen <Sebastiaan.Marynissen@UGent.be>
 */
class EntityManager extends BaseManager {
    
    /**
     * Doctrine's configuration object
     * 
     * @var Configuration
     */
    protected $config;
    
    /**
     * The Doctrine database connection
     * 
     * @var Connection
     */
    protected $connection;
    
    /**
     * Doctrine's Event Manager
     * 
     * @var EventManager
     */
    protected $eventManager;
    
    /**
     * Doctrine's PDO driver
     * 
     * @var Driver
     */
    protected $driver;
    
    /**
     * Doctrine's annotation mapper
     * 
     * @var AnnotationDriver
     */
    protected $mapper;
    
    /**
     * Creates a specific EntityManager based on a PDO object
     * 
     * @param \PDO $pdo A PDO instance to be used
     * @param string $proxyDir The proxy directory
     * @param string $proxyNamespace The proxy namespace
     */
    public function __construct(\PDO $pdo, $proxyDir, $proxyNamespace) {
        $this->createConfig($proxyDir, $proxyNamespace);
        $this->driver = new Driver();
        $this->connection = new Connection(array(
            'pdo' => $pdo
        ), $this->driver);
        $this->eventManager = new EventManager();
        parent::__construct($this->connection, $this->config, $this->eventManager);
    }
    
    /**
     * Creates a doctrine configuration object
     * 
     * @param string $dir
     * @param string $ns
     * @return EntityManager
     */
    protected function createConfig($dir, $ns) {
        $this->config = new Configuration();
        $this->mapper = new AnnotationDriver(new AnnotationReader());
        $this->config->setMetadataDriverImpl($this->mapper);
        $this->config->setProxyDir($dir);
        $this->config->setProxyNamespace($ns);
        return $this;
    }
    
}
