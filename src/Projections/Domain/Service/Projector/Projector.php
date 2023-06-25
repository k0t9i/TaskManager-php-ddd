<?php

declare(strict_types=1);

namespace TaskManager\Projections\Domain\Service\Projector;

use TaskManager\Shared\Domain\Event\DomainEventInterface;

abstract class Projector implements ProjectorInterface
{
    /**
     * @throws \ReflectionException
     */
    public function projectWhen(DomainEventInterface $event): void
    {
        $this->invokeSuitableMethods($event);
    }

    public function priority(): int
    {
        return 100;
    }

    /**
     * @throws \ReflectionException
     */
    private function invokeSuitableMethods(DomainEventInterface $event): void
    {
        $reflectionObject = new \ReflectionObject($this);

        foreach ($reflectionObject->getMethods() as $method) {
            if ('projectWhen' === $method->getName()) {
                continue;
            }
            if (1 !== $method->getNumberOfParameters()) {
                continue;
            }
            $typeName = $method->getParameters()[0]->getType()->getName();
            if (is_a($event, $typeName)) {
                $method->invoke($this, $event);
            }
        }
    }
}
