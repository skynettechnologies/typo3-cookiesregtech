<?php

declare(strict_types=1);

namespace SkynetTechnologies\CookiesRegTech\Service;

use TYPO3\CMS\Core\Http\RequestFactory;

/**
 * CookiesRegTech plugin API client. Returns the decoded body with the HTTP
 * status added as `_status`. Explicit Accept and User-Agent headers keep
 * strict firewalls from mistaking the request for a bot.
 */
class ApiClient
{
    public function __construct(
        private readonly RequestFactory $requestFactory,
        private readonly ConfigService $configService
    ) {
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function post(string $path, array $payload): array
    {
        try {
            $response = $this->requestFactory->request(
                $this->configService->all()['server'] . $path,
                'POST',
                [
                    'timeout' => 20,
                    'http_errors' => false,
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json',
                        'User-Agent' => 'CookiesRegTech-TYPO3/' . ConfigService::VERSION . ' (+https://www.cookiesregtech.com)',
                    ],
                    'body' => json_encode($payload),
                ]
            );
        } catch (\Throwable $exception) {
            return ['_status' => 0, '_error' => $exception->getMessage()];
        }

        $body = json_decode((string)$response->getBody(), true);
        $body = is_array($body) ? $body : [];
        $body['_status'] = $response->getStatusCode();

        return $body;
    }

    /**
     * @param array<string, mixed> $result
     */
    public function unexpectedReply(array $result): string
    {
        return sprintf(
            'Unexpected reply from the consent server (HTTP %d). If this repeats, the request may be blocked by a firewall.',
            (int)($result['_status'] ?? 0)
        );
    }
}
