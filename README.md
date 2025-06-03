# geo_redirect 🌍➡️

This extension redirect users based on browser language and ip country to the right language version of the website.

## Installation

```bash
composer require andersundsehr/geo_redirect
```

Go to the TYPO3 Extension Settings and configure as you need.

`!!!` If you want to use the ip to Country detection with the mmdb file you should add your `maxmindLicenseKey` (see Extension Settings).  
Get your key here: https://dev.maxmind.com/geoip/geolite2-free-geolocation-data

## Features

- Redirect domain root to detected language
- Redirect sys_redirect to detected language (TYPO3 => v12 only)

## How it works

This extension uses the user's Accept-Language header and the country derived from the IP address to find the correct language version of the site.  
For IP to Country there are mutliple Implementatinos.  
If you use Cloudflare or Sucuri, the extension will use the header that is set by the service.  
If you set a `maxmindLicenseKey` in the extension settings, the extension will use the mmdb file from maxmind.com.  
Otherwise it will ignore the request origin country. And just use the Accept-Language header.

### Options

- `ipCountryIsMoreImportantThanLanguage`: If it is more important that the user gets the content for his country than that he gets content for his languages, then turn this option on.
- `maxmindLicenseKey`: Maxmind.com License Key: if you want to use the mmdb file you should add the license key (https://dev.maxmind.com/geoip/geolite2-free-geolocation-data)

### Disable for language

By default the extension will try to redirect the user to the correct language version of the site.
You can disable this for a specific language by adding the following configuration to your `config.yaml` file:

```YAML
languages:
  - # Choose the language you want to disable the geo redirect for
    andersundsehr:
      geo_redirect:
        enabled: false
```

### mmdb file

The mmdb file is downloaded from maxmind.com and is updated *every 5 weeks* automatically.
If you always want the best performance for your users,  
you should add the `typo3 geo-redirect:update-up-database` command to run every month (4 weeks).

### Debugging

If you request the domain with this path: `/geo_redirect/debug` you will be given a detailed explenation what language was detected.

### sys_redirect based on siteLanguage

This feature makes it possible to redirect the user to a specific page based on the siteLanguage.  
You don't need to configure anything, for this to work.  
All sys_redirect records that use a t3://page?uid= link are automatically redirected to the right language version of the page.  
(if no _language= is defined in the redirect)


## Extending functionality

### PHP Api

get the ip country for the current request:
````php
$ipCountryOrNull = GeneralUtility::makeInstance(IpCountryLocatorInterface::class)->getIpCountry();
````

get the detected language:
````php
$siteLanguage = GeneralUtility::makeInstance(SiteLanguageFinderService::class)->findByRequest($request);
// or if you don't have a request object: (in cli the request object is required)
$siteLanguage = GeneralUtility::makeInstance(SiteLanguageFinderService::class)->findByRequest();
// the siteLanguage is never null, because it will always return the default language
````

### custom redirect definition

if you want a different redirect definition, you can use the `\AUS\GeoRedirect\Dto\BeforeSiteLanguageFinderEvent` to get all relevant Information and set the `siteLanguage` and `redirectUrl` yourself.

```php
<?php

declare(strict_types=1);

namespace AUS\GeoRedirect\EventListener;

final class BeforeSiteLanguageFinderEventListener
{
    public function __invoke(\AUS\GeoRedirect\Dto\BeforeSiteLanguageFinderEvent $event): void
    {
        $languageId = $this->customLanguageIdFinder($event);
        if ($languageId) {
            $event->siteLanguage = $event->site->getLanguageById($languageId);
        }
    }
    // ...
}
```

### add custom IpCountryLocator

If you want to add a custom IpCountryLocator, you can do so by adding a class that implements the `IpCountryLocatorInterface` and add it via the `CollectIpCountryLocatorEvent`.

```php
<?php

declare(strict_types=1);

namespace AUS\GeoRedirect\EventListener;

final class BeforeSiteLanguageFinderEventListener
{
    public function __invoke(\AUS\GeoRedirect\Dto\CollectIpCountryLocatorEvent $event): void
    {
        $event->addFirst(\AUS\GeoRedirect\IpCountryLocator\MyCustomIpCountryLocator::class);
        // or
        $event->addLast(\AUS\GeoRedirect\IpCountryLocator\MyCustomIpCountryLocator::class);
        // or if you want to add it at a specific position:
        $list = $event->getLocatorClasses();
        // manipulate the list as you like
        $event->setLocatorClasses($list);
    }
}
```

# with ♥️ from anders und sehr GmbH

> If something did not work 😮  
> or you appreciate this Extension 🥰 let us know.

> We are hiring https://www.andersundsehr.com/karriere/

