<?php

use TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider;

/**
 * Registers the CookiesRegTech swirl logo so the backend module shows it
 * instead of TYPO3's fallback module icon.
 */
return [
    'cookiesregtech-module' => [
        'provider' => SvgIconProvider::class,
        'source' => 'EXT:cookiesregtech/Resources/Public/Icons/Extension.svg',
    ],
];
