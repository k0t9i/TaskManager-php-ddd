<?php

declare(strict_types=1);

namespace TaskManager\Projects\Infrastructure\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;
use TaskManager\Projects\Domain\Entity\Project;
use TaskManager\Projects\Domain\Repository\ProjectRepositoryInterface;
use TaskManager\Projects\Domain\ValueObject\Participant;
use TaskManager\Projects\Domain\ValueObject\ProjectId;
use TaskManager\Shared\Infrastructure\Service\ManagedCollectionManager;

final readonly class DoctrineProjectRepository implements ProjectRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ManagedCollectionManager $collectionManager
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

        $participants = $this->entityManager->getRepository(Participant::class)
            ->findBy([
                'projectId' => $object->getId(),
            ]);

        $this->collectionManager->load($object, 'participants', $participants);

        return $object;
    }

    /**
     * @throws \ReflectionException
     */
    public function save(Project $project): void
    {
        $this->entityManager->persist($project);
        $this->collectionManager->flush($project, 'participants');
        $this->entityManager->flush();
    }

    private function getRepository(): ObjectRepository
    {
        return $this->entityManager->getRepository(Project::class);
    }
}
