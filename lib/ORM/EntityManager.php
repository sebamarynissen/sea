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
abstract class EntityManager extends BaseManager {
    
    /**
     * Doctrine's configuration object
     * 
     * @var Configuration
     */
    private $config;
    
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
     * @param string $entityNamespace The namespace the entities are stored in
     * @param string $proxyDir The proxy directory
     * @param string $proxyNamespace The proxy namespace
     */
    public function __construct(\PDO $pdo, $entityNamespace, $proxyDir, $proxyNamespace) {
        $this->createConfig($entityNamespace, $proxyDir, $proxyNamespace);
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
     * @param string $ens
     * @param string $dir
     * @param string $ns
     * @return EntityManager
     */
    private function createConfig($ens, $dir, $ns) {
        $this->config = new Configuration();
        $this->mapper = new AnnotationDriver(new AnnotationReader());
        $this->config->setMetadataDriverImpl($this->mapper);
        $this->config->setEntityNamespaces(array($ens));
        $this->config->setProxyDir($dir);
        $this->config->setProxyNamespace($ns);
        $this->configure($this->config);
        return $this;
    }
    
    /**
     * Should be implemented to provide some additional configuration, like a
     * custom RepositoryFactory or something
     */
    abstract protected function configure(Configuration $config);
    
}
