<?php

declare(strict_types=1);

namespace SkynetTechnologies\CookiesRegTech\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use SkynetTechnologies\CookiesRegTech\Service\ApiClient;
use SkynetTechnologies\CookiesRegTech\Service\ConfigService;
use TYPO3\CMS\Core\Http\HtmlResponse;
use TYPO3\CMS\Core\Http\JsonResponse;
use TYPO3\CMS\Core\Http\RedirectResponse;
use TYPO3\CMS\Core\Utility\PathUtility;

/**
 * The CookiesRegTech backend module: connect, pending, and connected screens.
 */
class SettingsController
{
    public function __construct(
        private readonly ConfigService $configService,
        private readonly ApiClient $apiClient
    ) {
    }

    public function handleRequest(ServerRequestInterface $request): ResponseInterface
    {
        $moduleUrl = $request->getAttribute('normalizedParams')->getRequestUri();

        if ($request->getMethod() === 'POST') {
            $redirect = $this->handleAction($request, $moduleUrl);

            if ($redirect !== null) {
                return $redirect;
            }
        }

        return new HtmlResponse($this->render($request, $moduleUrl));
    }

    private function handleAction(ServerRequestInterface $request, string $moduleUrl): ?ResponseInterface
    {
        $post = (array)$request->getParsedBody();
        $action = (string)($post['crt_action'] ?? '');
        $settings = $this->configService->all();

        if ($action === 'connect') {
            $email = trim((string)($post['email'] ?? ''));
            $consent = !empty($post['consent']);

            if (!filter_var($email, FILTER_VALIDATE_EMAIL) || !$consent) {
                $this->flash('error', 'Enter a valid email address and accept the terms to continue.');

                return new RedirectResponse($moduleUrl);
            }

            $hostname = (string)parse_url(
                (string)($GLOBALS['TYPO3_CONF_VARS']['SYS']['sitename_url'] ?? $request->getAttribute('normalizedParams')->getSiteUrl()),
                PHP_URL_HOST
            );
            $result = $this->apiClient->post('/api/plugin/provision', [
                'email' => $email,
                'hostname' => $hostname,
                'platform' => 'typo3',
                'consent' => true,
            ]);

            if (($result['status'] ?? '') === 'connected') {
                $this->configService->save([
                    'domain_id' => $result['domainId'],
                    'secret' => $result['secret'],
                    'email' => $result['email'] ?? $email,
                    'pending_token' => '',
                    'banner_enabled' => true,
                ]);
                $this->flash('ok', sprintf('The free account is ready and the consent banner is live on the site. A sign-in link was emailed to %s.', $email));
            } elseif (($result['status'] ?? '') === 'confirmation_sent') {
                $this->configService->save(['pending_token' => $result['requestToken'], 'email' => $email]);
                $this->flash('ok', 'That email already has an account. We sent it a confirmation link. Click the link, then press "Check status" here.');
            } else {
                $this->flash('error', (string)($result['error'] ?? $this->apiClient->unexpectedReply($result)));
            }

            return new RedirectResponse($moduleUrl);
        }

        if ($action === 'check') {
            $result = $this->apiClient->post('/api/plugin/status', ['requestToken' => $settings['pending_token']]);

            if (($result['status'] ?? '') === 'connected') {
                $this->configService->save([
                    'domain_id' => $result['domainId'],
                    'secret' => $result['secret'],
                    'pending_token' => '',
                    'banner_enabled' => true,
                ]);
                $this->flash('ok', 'Connected. The consent banner is live on this site.');
            } elseif (($result['status'] ?? '') === 'pending') {
                $this->flash('warn', 'Not confirmed yet. Click the link in the email first.');
            } else {
                $this->configService->save(['pending_token' => '']);
                $this->flash('error', (string)($result['error'] ?? 'The request expired. Start again.'));
            }

            return new RedirectResponse($moduleUrl);
        }

        if ($action === 'reset') {
            $this->configService->save(['pending_token' => '']);

            return new RedirectResponse($moduleUrl);
        }

        if ($action === 'save') {
            $enabled = !empty($post['banner_enabled']);
            $this->configService->save(['banner_enabled' => $enabled]);
            $this->flash('ok', $enabled ? 'Cookie consent banner is enabled.' : 'Cookie consent banner is disabled.');

            return new RedirectResponse($moduleUrl);
        }

        if ($action === 'link') {
            // Called by admin.js: return the one-time sign-in link as JSON so
            // the browser can open it in a genuinely new tab (a form posted
            // with target="_blank" loses the action when TYPO3 re-routes the
            // top-level module URL into the backend shell).
            $payload = ['secret' => $settings['secret']];

            if ((string)($post['intent'] ?? '') === 'upgrade') {
                $payload['intent'] = 'upgrade';
            }

            $result = $this->apiClient->post('/api/plugin/login-link', $payload);

            return new JsonResponse(
                !empty($result['url'])
                    ? ['url' => (string)$result['url']]
                    : ['error' => (string)($result['error'] ?? 'Could not create a sign-in link. Try again.')]
            );
        }

        if ($action === 'dashboard' || $action === 'upgrade') {
            $payload = ['secret' => $settings['secret']];

            if ($action === 'upgrade') {
                $payload['intent'] = 'upgrade';
            }

            $result = $this->apiClient->post('/api/plugin/login-link', $payload);

            if (!empty($result['url'])) {
                return new RedirectResponse((string)$result['url']);
            }

            $this->flash('error', (string)($result['error'] ?? 'Could not create a sign-in link. Try again.'));

            return new RedirectResponse($moduleUrl);
        }

        if ($action === 'disconnect') {
            if ($settings['secret'] !== '') {
                $this->apiClient->post('/api/plugin/disconnect', ['secret' => $settings['secret']]);
            }

            $this->configService->save(['domain_id' => '', 'secret' => '', 'pending_token' => '', 'email' => '']);
            $this->flash('ok', 'Disconnected. The banner is off. The CookiesRegTech account and its data are unchanged.');

            return new RedirectResponse($moduleUrl);
        }

        return null;
    }

    private function flash(string $type, string $message): void
    {
        $key = 'tx_cookiesregtech_flash';
        $GLOBALS['BE_USER']->setAndSaveSessionData($key, ['type' => $type, 'message' => $message]);
    }

    private function takeFlash(): ?array
    {
        $key = 'tx_cookiesregtech_flash';
        $flash = $GLOBALS['BE_USER']->getSessionData($key);

        if ($flash) {
            $GLOBALS['BE_USER']->setAndSaveSessionData($key, null);
        }

        return is_array($flash) ? $flash : null;
    }

    private function render(ServerRequestInterface $request, string $moduleUrl): string
    {
        $settings = $this->configService->all();
        $state = 'connect';
        $overview = null;

        if ($settings['secret'] !== '' && $settings['domain_id'] !== '') {
            $state = 'connected';
            $result = $this->apiClient->post('/api/plugin/overview', ['secret' => $settings['secret']]);

            if (($result['_status'] ?? 0) === 200) {
                $overview = $result;
            }
        } elseif ($settings['pending_token'] !== '') {
            $state = 'pending';
        }

        $flash = $this->takeFlash();
        $adminEmail = (string)($GLOBALS['BE_USER']->user['email'] ?? '');
        $cssPath = PathUtility::getPublicResourceWebPath('EXT:cookiesregtech/Resources/Public/Css/admin.css');
        $jsPath = PathUtility::getPublicResourceWebPath('EXT:cookiesregtech/Resources/Public/JavaScript/admin.js');
        $e = static fn($value): string => htmlspecialchars((string)$value, ENT_QUOTES);
        $action = $e($moduleUrl);
        $labels = [
            'ESSENTIAL' => 'Essential',
            'FUNCTIONALITY' => 'Functionality',
            'ANALYTICS' => 'Analytics',
            'ADS' => 'Ads',
            'UNCATEGORISED' => 'Uncategorised',
        ];

        ob_start();
        include __DIR__ . '/../../Resources/Private/settings.php';

        return (string)ob_get_clean();
    }
}
