<?php
declare(strict_types=1);

namespace Ex6\VarnishPurge\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Psr\Log\LoggerInterface;

class VarnishPurger
{
    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function purge(): VarnishPurgeResult
    {
        $host = (string) $this->scopeConfig->getValue('system/full_page_cache/varnish/backend_host');
        $port = (string) $this->scopeConfig->getValue('system/full_page_cache/varnish/backend_port');

        $this->logger->info('[VarnishPurge] Purge requested', ['host' => $host, 'port' => $port]);

        if (!$host || !$port) {
            $this->logger->error('[VarnishPurge] Host or port not configured.');
            return new VarnishPurgeResult(false, (string) __('Varnish host or port not configured.'));
        }

        $url = sprintf('http://%s:%s/', $host, $port);

        $this->logger->info('[VarnishPurge] Sending PURGE request', ['url' => $url]);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_CUSTOMREQUEST  => 'PURGE',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => ['X-Magento-Tags-Pattern: .*'],
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT        => 10,
        ]);

        $response = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error    = curl_error($ch);
        $errno    = curl_errno($ch);
        curl_close($ch);

        $this->logger->info('[VarnishPurge] cURL result', [
            'http_code' => $httpCode,
            'error'     => $error,
            'errno'     => $errno,
            'response'  => is_string($response) ? substr($response, 0, 500) : null,
        ]);

        if ($error !== '' && $error !== '0') {
            $this->logger->error('[VarnishPurge] cURL error', ['errno' => $errno, 'error' => $error]);
            return new VarnishPurgeResult(false, (string) __('cURL error: %1', $error));
        }

        if ($httpCode >= 200 && $httpCode < 300) {
            $this->logger->info('[VarnishPurge] Purge succeeded', ['http_code' => $httpCode]);
            return new VarnishPurgeResult(
                true,
                (string) __('Varnish cache purged successfully (HTTP %1).', $httpCode)
            );
        }

        $this->logger->error('[VarnishPurge] Purge failed', ['http_code' => $httpCode, 'response' => $response]);
        return new VarnishPurgeResult(false, (string) __('Purge request returned HTTP %1.', $httpCode));
    }
}
