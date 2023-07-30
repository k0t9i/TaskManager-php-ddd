<?php

declare(strict_types=1);

namespace TaskManager\Projections\Domain\Service\Projector;

use TaskManager\Projections\Domain\DTO\DomainEventEnvelope;

abstract class Projector implements ProjectorInterface
{
    /**
     * @throws \ReflectionException
     */
    public function projectWhen(DomainEventEnvelope $envelope): void
    {
        $this->invokeSuitableMethods($envelope);
    }

    public function priority(): int
    {
        return 100;
    }

    /**
     * @throws \ReflectionException
     */
    private function invokeSuitableMethods(DomainEventEnvelope $envelope): void
    {
        $reflectionObject = new \ReflectionObject($this);

        foreach ($reflectionObject->getMethods() as $method) {
            if ('projectWhen' === $method->getName()) {
                continue;
            }
            $numberOfParameters = $method->getNumberOfParameters();
            if ($numberOfParameters < 1 || $numberOfParameters > 2) {
                continue;
            }

            $firstParameterType = $method->getParameters()[0]->getType();
            if (!($firstParameterType instanceof \ReflectionNamedType)) {
                continue;
            }

            if (is_a($envelope->event, $firstParameterType->getName())) {
                $method->invoke($this, $envelope->event, $envelope->version);
            }
        }
    }
}
