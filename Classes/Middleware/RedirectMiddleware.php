<?php

declare(strict_types=1);

namespace AUS\GeoRedirect\Middleware;

use AUS\GeoRedirect\Service\IpCountryLocator\IpCountryLocatorInterface;
use AUS\GeoRedirect\Service\SiteLanguageFinderService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Http\HtmlResponse;
use TYPO3\CMS\Core\Http\RedirectResponse;
use TYPO3\CMS\Core\Site\Entity\NullSite;
use TYPO3\CMS\Core\Site\Entity\Site;

final readonly class RedirectMiddleware implements MiddlewareInterface
{
    public function __construct(
        private SiteLanguageFinderService $siteLanguageFinderService,
        private IpCountryLocatorInterface $ipCountryLocator
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($request->getUri()->getPath() === '/geo_redirect/debug') {
            $debugInfo = str_replace(PHP_EOL, '<br>', $this->ipCountryLocator->getDebugInfo());
            $statusCode = $this->ipCountryLocator->getIpCountry() ? 200 : 404;
            $siteLanguage = $this->siteLanguageFinderService->findByRequest($request);
            $debugInfo .= sprintf('<br><br>siteLanguage detected: <a href="%s">%s</a>', $siteLanguage->getBase(), $siteLanguage->getBase());
            return new HtmlResponse($debugInfo, $statusCode, ['X-Robots-Tag' => 'noindex, nofollow']);
        }

        if ($request->getUri()->getPath() !== '/') {
            //only redirect on domain root
            return $handler->handle($request);
        }

        $site = $request->getAttribute('site');
        if ($site instanceof NullSite) {
            return $handler->handle($request);
        }
        assert($site instanceof Site);
        foreach ($site->getLanguages() as $language) {
            if ($language->getBase()->getHost() !== $request->getUri()->getHost()) {
                continue;
            }

            if ($language->getBase()->getPath() !== $request->getUri()->getPath() &&
                (
                    ($language->getBase()->getPath() !== '' && $language->getBase()->getPath() !== '/') &&
                    ($request->getUri()->getPath() !== '' && $request->getUri()->getPath() !== '/')
                )
            ) {
                continue;
            }

            // there is a language with our host and path, so we can not redirect to any other.
            return $handler->handle($request);
        }

        $siteLanguage = $this->siteLanguageFinderService->findByRequest($request);
        $ipCountryCode = $this->ipCountryLocator->getIpCountry() ?? '';
        return new RedirectResponse($siteLanguage->getBase(), 307, ['X-RedirectReason' => 'geo_redirect ipCountry: "' . $ipCountryCode . '"']);
    }
}
