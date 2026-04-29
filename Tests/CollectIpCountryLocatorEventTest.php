<?php

declare(strict_types=1);

namespace AUS\GeoRedirect\Tests;

use AUS\GeoRedirect\Dto\CollectIpCountryLocatorEvent;
use AUS\GeoRedirect\Service\IpCountryLocator\CloudflareHeader;
use AUS\GeoRedirect\Service\IpCountryLocator\IpCountryLocatorInterface;
use AUS\GeoRedirect\Service\IpCountryLocator\MmdbFile;
use AUS\GeoRedirect\Service\IpCountryLocator\SucuriHeader;
use Exception;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use stdClass;

final class CollectIpCountryLocatorEventTest extends TestCase
{
    #[Test]
    public function addFirstPrependsLocatorClass(): void
    {
        $event = new CollectIpCountryLocatorEvent([SucuriHeader::class]);

        $event->addFirst(CloudflareHeader::class);

        self::assertSame([CloudflareHeader::class, SucuriHeader::class], $event->getLocatorClasses());
    }

    #[Test]
    public function addLastAppendsLocatorClass(): void
    {
        $event = new CollectIpCountryLocatorEvent([SucuriHeader::class]);

        $event->addLast(CloudflareHeader::class);

        self::assertSame([SucuriHeader::class, CloudflareHeader::class], $event->getLocatorClasses());
    }

    #[Test]
    public function getLocatorClassesRemovesDuplicates(): void
    {
        $event = new CollectIpCountryLocatorEvent([
            SucuriHeader::class,
            CloudflareHeader::class,
            SucuriHeader::class,
        ]);

        self::assertSame([SucuriHeader::class, CloudflareHeader::class], $event->getLocatorClasses());
    }

    #[Test]
    public function setLocatorClassesReplacesLocatorClasses(): void
    {
        $event = new CollectIpCountryLocatorEvent([SucuriHeader::class]);

        $event->setLocatorClasses([CloudflareHeader::class]);

        self::assertSame([CloudflareHeader::class], $event->getLocatorClasses());
    }

    #[Test]
    public function setLocatorClassesThrowsExceptionForMissingClass(): void
    {
        $event = new CollectIpCountryLocatorEvent([]);

        $this->expectException(Exception::class);
        $this->expectExceptionCode(7668017858);

        // @phpstan-ignore argument.type
        $event->setLocatorClasses(['AUS\\GeoRedirect\\Tests\\MissingTestLocator']);
    }

    #[Test]
    public function setLocatorClassesThrowsExceptionForClassNotImplementingInterface(): void
    {
        $event = new CollectIpCountryLocatorEvent([]);

        $this->expectException(Exception::class);
        $this->expectExceptionCode(9265581551);

        // @phpstan-ignore argument.type
        $event->setLocatorClasses([stdClass::class]);
    }
}
