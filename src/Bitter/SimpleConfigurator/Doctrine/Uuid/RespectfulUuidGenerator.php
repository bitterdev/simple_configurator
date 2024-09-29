<?php /** @noinspection PhpUnused */

namespace Bitter\SimpleConfigurator\Doctrine\Uuid;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Id\AbstractIdGenerator;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use ReflectionClass;
use ReflectionProperty;

/**
 * Fixes https://github.com/ramsey/uuid-doctrine/issues/81
 * @see \Ramsey\Uuid\Doctrine\UuidGenerator
 */
final class RespectfulUuidGenerator extends AbstractIdGenerator
{
    /**
     * @param EntityManager $em
     * @param object $entity
     * @return UuidInterface
     */
    public function generate(EntityManager $em, $entity): UuidInterface
    {
        $idReflectionProperty = $this->findClassIdProperty($entity);
        if ($idReflectionProperty === null) {
            return Uuid::uuid4();
        }

        return $idReflectionProperty->getValue($entity) ?? Uuid::uuid4();
    }

    /**
     * @param object $object
     * @return ReflectionProperty|null
     */
    private function findClassIdProperty(object $object): ?ReflectionProperty
    {
        $reflectionClass = new ReflectionClass($object);

        do {
            if ($reflectionClass->hasProperty('id')) {
                $idReflectionProperty = $reflectionClass->getProperty('id');
                /** @noinspection PhpExpressionResultUnusedInspection */
                $idReflectionProperty->setAccessible(true);
                return $idReflectionProperty;
            }
        } while ($reflectionClass = $reflectionClass->getParentClass());

        return null;
    }
}