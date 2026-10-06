# CookiesRegTech for Typo3 CMS

Cookie consent banner with automatic tracker blocking, Google Consent Mode, a cookie scanner, and a verifiable consent log — powered by [CookiesRegTech](https://www.cookiesregtech.com).

Helps with GDPR, UK GDPR, CCPA/CPRA, LGPD, POPIA, and PIPEDA compliance.

Supports Typo3 CMS Open Source 2.4.x and Adobe Commerce 2.4.x.

## Installation

### Prerequisites

- Typo version 13* & 14*

### Steps

To install the plugin, follow these instructions.

## Using Composer

Requirements

Must have installed your project used by composer.

1. Open your terminal and go to your TYPO3 project:

   cd /path/to/project

2. Then tell Composer to load the plugin:

   composer require skynettechnologies/typo3-cookiesregtech

3. In the Control Panel, go to Administration → CookiesRegTech.

## Using download zip

1. Zip File upload using plugin manager.

2. Then active the plugin:

3. In the Control Panel, go to Administration → CookiesRegTech.

## Activation and Deactivation

1. Navigate to **Extensions**.
2. Locate *CookiesRegTech* in the list.
3. Use the **Enable** or **Disable** toggle to activate or deactivate the extension as needed.

### Requirements

- Typo3 CMS v14 & v13 (built and tested against v4.4 / Laravel 12)
- PHP 7.4+ with the `curl` extension
- Outbound HTTPS to the remediation service

## After Connecting

After connecting or registering the website, the CookiesRegTech loader script and consent banner are automatically injected into the **Typo3 CMS frontend**. The Typo3 CMS Admin Panel is excluded.

The plugin page shows the account, plan, banner status, latest cookie scan results, **Open Dashboard**, **Upgrade Plan**, **Disconnect**, and a banner on/off toggle.

## How It Works

CookiesRegTech automatically injects a single loader script immediately after the `<head>` tag on frontend pages, before analytics and tag managers, to enable consent management and tracker blocking.

The connection secret is kept on the server and is not exposed in the frontend page HTML.

The service provides cookie scanning, consent management, Google Consent Mode support, and a verifiable consent log.

## Support

- Website: [https://www.cookiesregtech.com](https://www.cookiesregtech.com)
- Email: support@skynettechnologies.com