<?php

declare(strict_types=1);

namespace TaskManager\Shared\Infrastructure\Service;

use Doctrine\ORM\EntityManagerInterface;
use TaskManager\Shared\Domain\Collection\ManagedCollectionInterface;

final readonly class ManagedCollectionManager implements ManagedCollectionManagerInterface
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    /**
     * @throws \ReflectionException
     */
    public function load(object $owner, string $propertyName, array $items): void
    {
        $collection = $this->getCollection($owner, $propertyName);

        foreach ($items as $item) {
            $collection->addOrUpdateElement($item);
        }

        $collection->flush();
    }

    /**
     * @throws \ReflectionException
     */
    public function flush(object $owner, string $propertyName): void
    {
        $collection = $this->getCollection($owner, $propertyName);

        foreach ($collection->getRemovedItems() as $item) {
            $this->entityManager->remove($item);
        }

        foreach ($collection->getItems() as $item) {
            $this->entityManager->persist($item);
        }

        $this->entityManager->flush();
        $collection->flush();
    }

    /**
     * @throws \ReflectionException
     */
    private function getCollection(object $owner, string $propertyName): ManagedCollectionInterface
    {
        $reflectionObject = new \ReflectionObject($owner);
        $reflectionProperty = $reflectionObject->getProperty($propertyName);
        if (!$reflectionProperty->isInitialized($owner)) {
            /** @var \ReflectionNamedType $type */
            $type = $reflectionProperty->getType();
            $className = $type->getName();
            if (!is_a($className, ManagedCollectionInterface::class, true)) {
                throw new \LogicException('Invalid type '.$className);
            }
            $reflectionClass = new \ReflectionClass($type->getName());
            $reflectionProperty->setValue($owner, $reflectionClass->newInstanceWithoutConstructor());
        }

        return $reflectionProperty->getValue($owner);
    }
}
