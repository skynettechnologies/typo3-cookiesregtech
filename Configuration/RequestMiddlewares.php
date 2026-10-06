<?php

use SkynetTechnologies\CookiesRegTech\Middleware\BannerInjection;

return [
    'frontend' => [
        'skynettechnologies/cookiesregtech/banner-injection' => [
            'target' => BannerInjection::class,
            'after' => [
                'typo3/cms-frontend/prepare-tsfe-rendering',
            ],
        ],
    ],
];
