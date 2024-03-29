<?php

declare(strict_types=1);

namespace TaskManager\Tests\Shared\Infrastructure\Service;

use PHPUnit\Framework\TestCase;
use TaskManager\Shared\Domain\Event\DomainEvent;
use TaskManager\Shared\Infrastructure\Service\DomainEventMapper;

abstract class TestEvent extends DomainEvent
{
    public static function getEventName(): string
    {
        return 'test';
    }
}

abstract class TestEventWithSameName extends DomainEvent
{
    public static function getEventName(): string
    {
        return 'test';
    }
}

abstract class AnotherTestEvent extends DomainEvent
{
    public static function getEventName(): string
    {
        return 'another test';
    }
}

class DomainEventMapperTest extends TestCase
{
    public function testCreateWithValidValue(): void
    {
        $events = [
            TestEvent::class,
            AnotherTestEvent::class,
            TestEventWithSameName::class,
        ];
        $expected = [
            TestEvent::getEventName() => [
                TestEvent::class,
                TestEventWithSameName::class,
            ],
            AnotherTestEvent::getEventName() => [
                AnotherTestEvent::class,
            ],
        ];

        $service = new DomainEventMapper($events);

        $this->assertEquals($expected[TestEvent::getEventName()], $service->getEventClasses(TestEvent::getEventName()));
        $this->assertEquals(
            $expected[AnotherTestEvent::getEventName()],
            $service->getEventClasses(AnotherTestEvent::getEventName())
        );
    }

    public function testCreateWithInvalidValue(): void
    {
        $className = 'RandomClassName';

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage(sprintf('"%s" must be instance of DomainEvent', $className));

        new DomainEventMapper([$className]);
    }
}
