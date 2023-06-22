<?php

declare(strict_types=1);

namespace TaskManager\Projections\Infrastructure\Repository;

use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use TaskManager\Projections\Domain\Entity\Event;
use TaskManager\Projections\Domain\Repository\EventRepositoryInterface;

final readonly class DoctrineEventRepository implements EventRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    public function save(Event $event): void
    {
        $this->entityManager->persist($event);
        $this->entityManager->flush();
    }

    /**
     * @return Event[]
     */
    public function findOrderedFromLastTime(\DateTimeImmutable $lastDatetime): array
    {
        $criteria = new Criteria();
        $criteria->where(Criteria::expr()->gt('occurredOn', $lastDatetime))
            ->orderBy([
                'occurredOn' => 'ASC',
            ]);

        return $this->getRepository()->matching($criteria)->toArray();
    }

    private function getRepository(): EntityRepository
    {
        return $this->entityManager->getRepository(Event::class);
    }
}
