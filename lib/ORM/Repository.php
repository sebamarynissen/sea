<?php
namespace Sea\ORM;

use ReflectionClass;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\Common\Annotations\AnnotationReader;

/**
 * A base class for the different Poutrix repositories
 *
 * @author Sebastiaan Marynissen <Sebastiaan.Marynissen@UGent.be>
 */
abstract class Repository extends EntityRepository {
    
    /**
     * The entity name the repository is responsible for
     * 
     * @var 
     */
    protected $entityName;
    
    /**
     * The namespace for the entities
     * 
     * @var string
     */
    protected $entityNamespace;
    
    /**
     * Constructs a new PoutrixRepository
     * 
     * @param \PDO $pdo A PDO instance to use
     * @param string $entityNamespace The namespace the entities are stored in
     * @param string $proxyDir Proxy directory
     * @param string $proxyNamespace Proxy namespace
     * @throws \Exception
     */
    public function __construct(\PDO $pdo, $entityNamespace, $proxyDir, $proxyNamespace) {
        $this->entityNamespace = $entityNamespace;
        $this->findEntityName();
        if (!isset($this->entityName)) {
            throw new \Exception('No entity name set!');
        }
        parent::__construct(new EntityManager($pdo, $proxyDir, $proxyNamespace), new ClassMetadata($this->entityNamespace . '\\' . $this->entityName));
    }
    
    /**
     * Saves an entity to the database
     * 
     * @param object $entity The entity to save
     */
    public function save($entity) {
        $this->_em->persist($entity);
        $this->_em->flush();
        return $this;
    }
    
    /**
     * Searches for the name the entity is responsible for in the class metadata
     * 
     * @return Repository
     */
    private function findEntityName() {
        $class = new ReflectionClass($this);
        $reader = new AnnotationReader();
        $name = $reader->getClassAnnotation($class, 'Sea\\ORM\\Annotations\\EntityName');
        if ($name) {
            $this->entityName = $name;
        }
        return $this;
    }
    
    /**
     * Creates a custom query builder, where from is already set to the Entity
     * 
     * @param string $alias Alias for the entity
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function createQueryBuilder($alias) {
        $qb = parent::createQueryBuilder($alias);
        return $qb->from($this->entityName, $alias);
    }
    
}
