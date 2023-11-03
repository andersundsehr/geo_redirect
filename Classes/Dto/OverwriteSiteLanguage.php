<?php

declare(strict_types=1);

namespace AUS\GeoRedirect\Dto;

use Psr\Http\Message\UriInterface;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Error\PageErrorHandler\PageErrorHandlerInterface;
use TYPO3\CMS\Core\Site\Entity\SiteInterface;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;

final readonly class OverwriteSiteLanguage implements SiteInterface
{
    public function __construct(
        private SiteInterface $site,
        private SiteLanguage $defaultSiteLanguage,
    ) {
    }

    public function getRootPageId(): int
    {
        return $this->site->getRootPageId();
    }

    public function getIdentifier(): string
    {
        return $this->site->getIdentifier();
    }

    public function getBase(): UriInterface
    {
        return $this->site->getBase();
    }

    public function getLanguages(): array
    {
        return $this->site->getLanguages();
    }

    public function getLanguageById(int $languageId): SiteLanguage
    {
        return $this->site->getLanguageById($languageId);
    }

    public function getDefaultLanguage(): SiteLanguage
    {
        return $this->defaultSiteLanguage;
    }

    public function getAvailableLanguages(BackendUserAuthentication $user, bool $includeAllLanguagesFlag = false, int $pageId = null): array
    {
        return $this->site->getAvailableLanguages($user, $includeAllLanguagesFlag, $pageId);
    }

    public function getErrorHandler(int $statusCode): PageErrorHandlerInterface
    {
        return $this->site->getErrorHandler($statusCode);
    }
}
