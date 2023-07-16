<?php

declare(strict_types=1);

namespace TaskManager\Projects\Infrastructure\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;
use TaskManager\Projects\Domain\Entity\Task;
use TaskManager\Projects\Domain\Repository\TaskRepositoryInterface;
use TaskManager\Projects\Domain\ValueObject\TaskId;
use TaskManager\Projects\Domain\ValueObject\TaskLink;
use TaskManager\Shared\Infrastructure\Service\ManagedCollectionManager;

final readonly class DoctrineTaskRepository implements TaskRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private readonly ManagedCollectionManager $collectionManager
    ) {
    }

    /**
     * @throws \ReflectionException
     */
    public function findById(TaskId $id): ?Task
    {
        /** @var Task $object */
        $object = $this->getRepository()->findOneBy([
            'id' => $id,
        ]);

        $items = $this->entityManager->getRepository(TaskLink::class)
            ->findBy([
                'taskId' => $object->getId(),
            ]);
        $this->collectionManager->load($object, 'links', $items);

        return $object;
    }

    /**
     * @throws \ReflectionException
     */
    public function save(Task $task): void
    {
        $this->entityManager->persist($task);
        $this->collectionManager->flush($task, 'links');
        $this->entityManager->flush();
    }

    private function getRepository(): ObjectRepository
    {
        return $this->entityManager->getRepository(Task::class);
    }
}
