<?php

declare(strict_types=1);

namespace TaskManager\Shared\Application\Bus\Event;

interface IntegrationEventInterface
{
    public function getAggregateId(): string;

    public function getBody(): array;

    public function getPerformerId(): string;

    public function getOccurredOn(): string;

    public function getDomainEventName(): string;

    public function getVersion(): ?int;
}
