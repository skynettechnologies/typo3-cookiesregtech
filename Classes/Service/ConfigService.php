<?php

declare(strict_types=1);

namespace SkynetTechnologies\CookiesRegTech\Service;

use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;

/**
 * Settings storage in the extension configuration.
 */
class ConfigService
{
    public const VERSION = '1.0.0';
    public const DEFAULT_SERVER = 'https://www.cookiesregtech.com';

    public function __construct(private readonly ExtensionConfiguration $extensionConfiguration)
    {
    }

    /**
     * @return array{server: string, domain_id: string, secret: string, pending_token: string, email: string, banner_enabled: bool}
     */
    public function all(): array
    {
        try {
            $stored = (array)$this->extensionConfiguration->get('cookiesregtech');
        } catch (\Throwable) {
            $stored = [];
        }

        return [
            'server' => rtrim((string)(($stored['server'] ?? '') ?: self::DEFAULT_SERVER), '/'),
            'domain_id' => (string)($stored['domain_id'] ?? ''),
            'secret' => (string)($stored['secret'] ?? ''),
            'pending_token' => (string)($stored['pending_token'] ?? ''),
            'email' => (string)($stored['email'] ?? ''),
            'banner_enabled' => !isset($stored['banner_enabled']) || (bool)$stored['banner_enabled'],
        ];
    }

    /**
     * @param array<string, mixed> $changes
     */
    public function save(array $changes): void
    {
        $settings = array_merge($this->all(), $changes);
        $settings['banner_enabled'] = (int)(bool)$settings['banner_enabled'];
        $this->extensionConfiguration->set('cookiesregtech', $settings);
    }
}
