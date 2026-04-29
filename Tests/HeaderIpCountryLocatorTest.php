<?php

declare(strict_types=1);

namespace AUS\GeoRedirect\Tests;

use Generator;
use AUS\GeoRedirect\Service\IpCountryLocator\CloudflareHeader;
use AUS\GeoRedirect\Service\IpCountryLocator\IpCountryLocatorInterface;
use AUS\GeoRedirect\Service\IpCountryLocator\NullLocator;
use AUS\GeoRedirect\Service\IpCountryLocator\SucuriHeader;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Http\ServerRequest;

final class HeaderIpCountryLocatorTest extends TestCase
{
    #[Test]
    #[DataProvider('headerLocatorDataProvider')]
    public function testHeaderLocators(IpCountryLocatorInterface $locator, ?ServerRequestInterface $request, ?string $country, string $debugInfo): void
    {
        self::assertSame($country, $locator->getIpCountry($request));
        self::assertSame($debugInfo, $locator->getDebugInfo($request));
    }

    /**
     * @return Generator<array{locator: IpCountryLocatorInterface, request: ?ServerRequestInterface, country: ?string, debugInfo: string}>
     */
    public static function headerLocatorDataProvider(): Generator
    {
        yield [
            'locator' => new NullLocator(),
            'request' => null,
            'country' => null,
            'debugInfo' => ''
        ];
        yield [
            'locator' => new CloudflareHeader(),
            'request' => null,
            'country' => null,
            'debugInfo' => ''
        ];
        yield [
            'locator' => new CloudflareHeader(),
            'request' => new ServerRequest('https://example.com/', 'GET', null, ['CF-IPCountry' => 'DE']),
            'country' => 'de',
            'debugInfo' => ''
        ];
        yield [
            'locator' => new SucuriHeader(),
            'request' => null,
            'country' => null,
            'debugInfo' => ''
        ];
        yield [
            'locator' => new SucuriHeader(),
            'request' => new ServerRequest('https://example.com/', 'GET', null, ['X-Sucuri-Country' => 'DE']),
            'country' => 'de',
            'debugInfo' => ''
        ];
    }
}
