<?php

declare(strict_types=1);

namespace AUS\GeoRedirect\XClass\Redirects\Service;

use TYPO3\CMS\Core\Information\Typo3Version;

// phpcs:disable PSR1.Classes.ClassDeclaration.MultipleClasses

if ((new Typo3Version())->getMajorVersion() >= 14) {
    final readonly class RedirectService extends \TYPO3\CMS\Redirects\Service\RedirectService
    {
        use RedirectServiceTrait;
    }
} else {
    final class RedirectService extends \TYPO3\CMS\Redirects\Service\RedirectService
    {
        use RedirectServiceTrait;
    }
}
