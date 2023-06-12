<?php

declare(strict_types=1);

namespace TaskManager\Projects\Infrastructure\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;
use TaskManager\Projects\Domain\Entity\Project;
use TaskManager\Projects\Domain\Entity\Request;
use TaskManager\Projects\Domain\Repository\ProjectRepositoryInterface;
use TaskManager\Projects\Domain\ValueObject\Participant;
use TaskManager\Projects\Domain\ValueObject\ProjectId;
use TaskManager\Projects\Domain\ValueObject\ProjectTask;
use TaskManager\Shared\Infrastructure\Service\ManagedCollectionManager;

final class DoctrineProjectRepository implements ProjectRepositoryInterface
{
    private array $collections = [
        Participant::class => ['participants', 'projectId'],
        Request::class => ['requests', 'projectId'],
        ProjectTask::class => ['tasks', 'projectId'],
    ];

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ManagedCollectionManager $collectionManager
    ) {
    }

    /**
     * @throws \ReflectionException
     */
    public function findById(ProjectId $id): ?Project
    {
        /** @var Project $object */
        $object = $this->getRepository()->findOneBy([
            'id' => $id,
        ]);

        if (null === $object) {
            return $object;
        }

        foreach ($this->collections as $className => $metadata) {
            [$propertyName, $fkName] = $metadata;

            $items = $this->entityManager->getRepository($className)
                ->findBy([
                    $fkName => $object->getId(),
                ]);
            $this->collectionManager->load($object, $propertyName, $items);
        }

        return $object;
    }

    /**
     * @throws \ReflectionException
     */
    public function save(Project $project): void
    {
        $this->entityManager->persist($project);

        foreach ($this->collections as $metadata) {
            [$propertyName] = $metadata;

            $this->collectionManager->flush($project, $propertyName);
        }

        $this->entityManager->flush();
    }

    private function getRepository(): ObjectRepository
    {
        return $this->entityManager->getRepository(Project::class);
    }
}
