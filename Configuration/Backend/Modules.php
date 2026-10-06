<?php

use SkynetTechnologies\CookiesRegTech\Controller\SettingsController;

/**
 * Registers the CookiesRegTech backend module under Admin Tools.
 */
return [
    'tools_cookiesregtech' => [
        'parent' => 'tools',
        'position' => ['after' => '*'],
        'access' => 'admin',
        'path' => '/module/tools/cookiesregtech',
        'iconIdentifier' => 'cookiesregtech-module',
        'labels' => [
            'title' => 'CookiesRegTech',
            'shortDescription' => 'CookiesRegTech cookie consent',
            'description' => 'Connect this website to CookiesRegTech and manage the consent banner.',
        ],
        'routes' => [
            '_default' => [
                'target' => SettingsController::class . '::handleRequest',
            ],
        ],
    ],
];
