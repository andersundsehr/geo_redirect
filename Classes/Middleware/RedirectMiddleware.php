<?php

declare(strict_types=1);

namespace AUS\GeoRedirect\Middleware;

use AUS\GeoRedirect\Service\IpCountryLocator\IpCountryLocatorInterface;
use AUS\GeoRedirect\Service\SiteLanguageFinderService;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\Exception\AspectNotFoundException;
use TYPO3\CMS\Core\Http\HtmlResponse;
use TYPO3\CMS\Core\Http\RedirectResponse;
use TYPO3\CMS\Core\Site\Entity\NullSite;
use TYPO3\CMS\Core\Site\Entity\SiteInterface;

final class RedirectMiddleware implements MiddlewareInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        private readonly SiteLanguageFinderService $siteLanguageFinderService,
        private readonly IpCountryLocatorInterface $ipCountryLocator,
        private readonly Context $context,
    ) {
    }

    /**
     * @throws AspectNotFoundException
     */
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

        /** @var null|SiteInterface $site */
        $site = $request->getAttribute('site');
        if ($site instanceof NullSite) {
            return $handler->handle($request);
        }

        try {
            // on extension set up the mmdb does not exist, do not break instance
            $targetSiteLanguage = $this->siteLanguageFinderService->findByRequest($request);
        } catch (InvalidArgumentException $invalidArgumentException) {
            $this->logger?->error('Could not find target language', ['exception' => $invalidArgumentException]);
            return $handler->handle($request);
        }

        $currentRequestLanguage = $request->getAttribute('language');
        if ($targetSiteLanguage === $currentRequestLanguage) {
            return $handler->handle($request);
        }

        if ($currentRequestLanguage) {
            if ($this->context->getPropertyFromAspect('backend.user', 'isLoggedIn')) {
                return $handler->handle($request);
            }
        }

        $ipCountryCode = $this->ipCountryLocator->getIpCountry() ?? '';
        return new RedirectResponse($targetSiteLanguage->getBase(), 307, ['X-RedirectReason' => 'geo_redirect ipCountry: "' . $ipCountryCode . '"']);
    }
}
