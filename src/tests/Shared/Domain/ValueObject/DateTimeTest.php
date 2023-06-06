<?php

declare(strict_types=1);

namespace TaskManager\Tests\Shared\Domain\ValueObject;

use PHPUnit\Framework\TestCase;
use TaskManager\Shared\Domain\Equatable;
use TaskManager\Shared\Domain\Exception\InvalidArgumentException;
use TaskManager\Shared\Domain\ValueObject\DateTime;

class DateTimeTest extends TestCase
{

    public function testIsGreaterThan()
    {
        $date = new DateTime('01-01-1990');
        $sameDate = new DateTime('01-01-1990');
        $biggerDate = new DateTime('22-11-1999');

        $this->assertTrue($biggerDate->isGreaterThan($date));
        $this->assertFalse($date->isGreaterThan($biggerDate));
        $this->assertFalse($date->isGreaterThan($sameDate));
        $this->assertFalse($sameDate->isGreaterThan($date));
    }

    public function testCreateWithValidValue()
    {
        $now = time();
        $date = date(DateTime::DEFAULT_FORMAT, $now);

        $dateObject = new DateTime(date('d-m-Y H:i:s'), $now);

        $this->assertEquals($date, $dateObject->getValue());
    }

    public function testCreateWithInvalidValue()
    {
        $invalidValue = 'abcdefg';

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf('Invalid datetime value "%s"', $invalidValue));

        new DateTime($invalidValue);
    }

    public function testToString()
    {
        $now = time();
        $date = date(DateTime::DEFAULT_FORMAT, $now);

        $dateObject = new DateTime(date('d-m-Y H:i:s'), $now);

        $this->assertEquals($date, (string) $dateObject);
    }

    public function testEquals(): void
    {
        $dateObject = new DateTime('01-01-1990');
        $equalDate = new DateTime('01-01-1990');
        $nonEqualDate = new DateTime('01-01-1999');
        $otherEquatable = $this->getMockBuilder(Equatable::class)
            ->getMock();

        $this->assertTrue($dateObject->equals($dateObject));
        $this->assertTrue($dateObject->equals($equalDate));
        $this->assertFalse($dateObject->equals($nonEqualDate));
        $this->assertFalse($dateObject->equals($otherEquatable));
    }
}
