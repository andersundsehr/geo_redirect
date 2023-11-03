<?php

namespace AUS\GeoRedirect\Service\IpCountryLocator;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Http\ServerRequestFactory;

final class SucuriHeader implements IpCountryLocatorInterface
{
    public function getIpCountry(): ?string
    {
        if (Environment::isCli()) {
            return null;
        }

        $request = $GLOBALS['TYPO3_REQUEST'] ?? ServerRequestFactory::fromGlobals();
        assert($request instanceof ServerRequestInterface);
        return strtolower($request->getHeaderLine('X-Sucuri-Country')) ?: null;
    }

    public function getDebugInfo(): string
    {
        return '';
    }
}
