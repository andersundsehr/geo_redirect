<?php

declare(strict_types=1);

namespace AUS\GeoRedirect\Service\IpCountryLocator;

use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;

#[Autoconfigure(public: true)]
interface IpCountryLocatorInterface
{
    /**
     * Get "ISO 3166 ALPHA-2" code
     *
     * @see https://en.wikipedia.org/wiki/ISO_3166-1_alpha-2
     */
    public function getIpCountry(?ServerRequestInterface $request): ?string;

    public function getDebugInfo(?ServerRequestInterface $request): string;
}
