<?php

declare(strict_types=1);

namespace SkynetTechnologies\CookiesRegTech\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use SkynetTechnologies\CookiesRegTech\Service\ConfigService;
use TYPO3\CMS\Core\Http\StreamFactory;

/**
 * Injects the CookiesRegTech consent bootstrap right after <head> on
 * frontend HTML responses, so it runs before analytics and tag managers.
 */
class BannerInjection implements MiddlewareInterface
{
    public function __construct(private readonly ConfigService $configService)
    {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);

        if (!str_contains($response->getHeaderLine('Content-Type'), 'text/html')) {
            return $response;
        }

        $settings = $this->configService->all();

        if (!$settings['banner_enabled']) {
            return $response;
        }

        $domainId = trim($settings['domain_id']);

        if ($domainId === '' || !preg_match('/^[a-z0-9]{10,40}$/i', $domainId)) {
            return $response;
        }

        $html = (string)$response->getBody();

        if (stripos($html, '<head') === false) {
            return $response;
        }

        $tag = '<script src="'
            . htmlspecialchars($settings['server'] . '/embed/boot/' . rawurlencode($domainId) . '.js', ENT_QUOTES)
            . '"></script>';
        // First occurrence only: the bootstrap must be the first script in head.
        $html = (string)preg_replace('/<head([^>]*)>/i', '<head$1>' . $tag, $html, 1);

        return $response->withBody((new StreamFactory())->createStream($html));
    }
}
