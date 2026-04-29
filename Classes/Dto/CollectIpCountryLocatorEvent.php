<?php

declare(strict_types=1);

namespace AUS\GeoRedirect\Dto;

use AUS\GeoRedirect\Service\IpCountryLocator\IpCountryLocatorInterface;
use Exception;

final class CollectIpCountryLocatorEvent
{
    /** @param class-string<IpCountryLocatorInterface>[] $locatorClasses */
    public function __construct(private array $locatorClasses)
    {
    }

    /** @param class-string<IpCountryLocatorInterface> $locatorClass */
    public function addFirst(string $locatorClass): void
    {
        $this->validate($locatorClass);
        array_unshift($this->locatorClasses, $locatorClass);
    }

    /** @param class-string<IpCountryLocatorInterface> $countryLocator */
    public function addLast(string $countryLocator): void
    {
        $this->validate($countryLocator);
        $this->locatorClasses[] = $countryLocator;
    }

    /**
     * @return class-string<IpCountryLocatorInterface>[]
     */
    public function getLocatorClasses(): array
    {
        return array_unique($this->locatorClasses);
    }

    /** @param class-string<IpCountryLocatorInterface>[] $locatorClasses */
    public function setLocatorClasses(array $locatorClasses): void
    {
        foreach ($locatorClasses as $locatorClass) {
            $this->validate($locatorClass);
        }

        $this->locatorClasses = $locatorClasses;
    }

    /** @param class-string<IpCountryLocatorInterface> $countryLocator */
    private function validate(string $countryLocator): void
    {
        if (!class_exists($countryLocator)) {
            throw new Exception('Class ' . $countryLocator . ' does not exist.', 7668017858);
        }

        if (!is_a($countryLocator, IpCountryLocatorInterface::class, true)) {
            throw new Exception('Class ' . $countryLocator . ' dose not implement the ' . IpCountryLocatorInterface::class . '.', 9265581551);
        }
    }
}
