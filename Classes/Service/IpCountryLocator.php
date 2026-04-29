<?php

declare(strict_types=1);

namespace AUS\GeoRedirect\Service;

use AUS\GeoRedirect\Dto\CollectIpCountryLocatorEvent;
use AUS\GeoRedirect\Service\IpCountryLocator\CloudflareHeader;
use AUS\GeoRedirect\Service\IpCountryLocator\IpCountryLocatorInterface;
use AUS\GeoRedirect\Service\IpCountryLocator\MmdbFile;
use AUS\GeoRedirect\Service\IpCountryLocator\SucuriHeader;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

use function assert;

final class IpCountryLocator implements IpCountryLocatorInterface, SingletonInterface
{
    private string $ipCountry;

    /** @var string[] */
    private array $debugInfo = [];

    public function __construct(private readonly EventDispatcherInterface $eventDispatcher)
    {
    }

    public function getIpCountry(?ServerRequestInterface $request): ?string
    {
        if (isset($this->ipCountry)) {
            return $this->ipCountry ?: null;
        }

        /** @var CollectIpCountryLocatorEvent $event */
        $event = $this->eventDispatcher
            ->dispatch(
                new CollectIpCountryLocatorEvent([
                    SucuriHeader::class,
                    CloudflareHeader::class,
                    MmdbFile::class,
                ])
            );
        $geoLocatorClasses = $event->getLocatorClasses();

        foreach ($geoLocatorClasses as $locatorClass) {
            $locator = GeneralUtility::makeInstance($locatorClass);
            if (!$locator instanceof IpCountryLocatorInterface) {
                throw new RuntimeException('IpCountryLocatorInterface expected, class ' . $locatorClass . ' does not implement it.', 4307491102);
            }

            if ($locator instanceof IpCountryLocator) {
                // prevent infinite loop
                continue;
            }

            $countryCode = $locator->getIpCountry($request);
            $this->debugInfo[] = $locatorClass . ':';
            $this->debugInfo[] = $locator->getDebugInfo($request);
            if ($countryCode) {
                $this->debugInfo[] = 'countryCode: ' . $countryCode;
                return $this->ipCountry = $countryCode;
            }

            $this->debugInfo[] = 'not found, trying next locator';
            $this->debugInfo[] = '-';
        }

        $this->debugInfo[] = 'countryCode: not found';
        $this->ipCountry = '';
        return null;
    }

    public function getDebugInfo(?ServerRequestInterface $request): string
    {
        $this->getIpCountry($request);
        return implode(PHP_EOL, array_filter($this->debugInfo));
    }
}
