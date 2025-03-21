<?php

declare(strict_types=1);

namespace AUS\GeoRedirect\Listener;

use SFC\Staticfilecache\Event\CacheRuleEvent;

/**
 * For EXT:staticfilecache do not cache the root page for further language redirections.
 */
class CacheRuleListener
{
    public function __invoke(CacheRuleEvent $event): void
    {
        $request = $event->getRequest();
        if ($request->getUri()->getPath() === '/') {
            $event->setSkipProcessing(true);
            $event->addExplanation('GeoRedirect', 'CacheRuleListener: Skip processing');
        }
    }
}
