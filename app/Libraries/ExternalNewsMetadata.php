<?php

namespace App\Libraries;

use InvalidArgumentException;
use RuntimeException;

final class ExternalNewsMetadata
{
    private const MAX_RESPONSE_BYTES = 1_048_576;
    private const MAX_IMAGE_BYTES = 10 * 1024 * 1024;
    private const IMAGE_EXTENSIONS = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    public function normalizeUrl(string $url): string
    {
        $url = trim($url);
        if ($url === '' || strlen($url) > 2048 || filter_var($url, FILTER_VALIDATE_URL) === false) {
            throw new InvalidArgumentException('Masukkan tautan berita yang valid.');
        }

        $parts = parse_url($url);
        if (! is_array($parts)) {
            throw new InvalidArgumentException('Masukkan tautan berita yang valid.');
        }

        $scheme = strtolower((string) ($parts['scheme'] ?? ''));
        $host = strtolower(rtrim((string) ($parts['host'] ?? ''), '.'));
        $port = $parts['port'] ?? null;

        if (
            ! in_array($scheme, ['http', 'https'], true)
            || $host === ''
            || isset($parts['user'])
            || isset($parts['pass'])
            || ($port !== null && $port !== ($scheme === 'https' ? 443 : 80))
            || filter_var($host, FILTER_VALIDATE_IP) !== false
            || filter_var($host, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME) === false
            || preg_match('/(?:^|\.)(?:localhost|local|internal)\z/i', $host) === 1
        ) {
            throw new InvalidArgumentException('Tautan harus menggunakan HTTP atau HTTPS dan domain publik.');
        }

        $normalized = $scheme . '://' . $host;
        $path = (string) ($parts['path'] ?? '/');
        $normalized .= $path === '' ? '/' : $path;
        if (isset($parts['query'])) {
            $normalized .= '?' . $parts['query'];
        }

        return $normalized;
    }

    /**
     * @return array{title: string, source_name: string, image_url: ?string}
     */
    public function fetch(string $url): array
    {
        $url = $this->normalizeUrl($url);
        $resource = $this->fetchPublicResource($url, self::MAX_RESPONSE_BYTES);

        if (
            preg_match('~\A(?:text/html|application/xhtml\+xml)(?:\s*;|\z)~i', $resource['content_type']) !== 1
        ) {
            throw new RuntimeException('Metadata berita tidak dapat diambil. Silakan isi secara manual.');
        }

        return $this->parseHtml($resource['body'], $url);
    }

    /**
     * @return array{contents: string, mime: string}
     */
    public function fetchImage(string $url): array
    {
        $url = $this->normalizeUrl($url);
        $resource = $this->fetchPublicResource($url, self::MAX_IMAGE_BYTES);
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->buffer($resource['body']);
        $image = @getimagesizefromstring($resource['body']);

        if (
            ! is_string($mime)
            || ! isset(self::IMAGE_EXTENSIONS[$mime])
            || $image === false
            || ($image['mime'] ?? null) !== $mime
            || ($image[0] ?? 0) < 1
            || ($image[1] ?? 0) < 1
            || $image[0] * $image[1] > 40_000_000
        ) {
            throw new RuntimeException('Gambar dari sumber tidak valid. Pilih gambar kover secara manual.');
        }

        return [
            'contents' => $resource['body'],
            'mime' => $mime,
        ];
    }

    /**
     * @return array{body: string, content_type: string}
     */
    private function fetchPublicResource(string $url, int $maxBytes): array
    {
        $parts = parse_url($url);
        $host = (string) $parts['host'];
        $port = $parts['scheme'] === 'https' ? 443 : 80;
        $address = $this->resolvePublicIpv4($host);

        if (! function_exists('curl_init')) {
            throw new RuntimeException('Pengambilan metadata tautan belum tersedia pada server.');
        }

        $handle = curl_init($url);
        if ($handle === false) {
            throw new RuntimeException('Konten sumber tidak dapat diambil. Silakan isi secara manual.');
        }

        $body = '';
        curl_setopt_array($handle, [
            CURLOPT_RETURNTRANSFER => false,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_PROTOCOLS => CURLPROTO_HTTP | CURLPROTO_HTTPS,
            CURLOPT_RESOLVE => [$host . ':' . $port . ':' . $address],
            CURLOPT_PROXY => '',
            CURLOPT_USERAGENT => 'JemaatLeahariNewsMetadata/1.0',
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_WRITEFUNCTION => static function ($curl, string $chunk) use (&$body, $maxBytes): int {
                if (strlen($body) + strlen($chunk) > $maxBytes) {
                    return 0;
                }

                $body .= $chunk;

                return strlen($chunk);
            },
        ]);

        $success = curl_exec($handle);
        $status = (int) curl_getinfo($handle, CURLINFO_RESPONSE_CODE);
        $contentType = (string) curl_getinfo($handle, CURLINFO_CONTENT_TYPE);
        curl_close($handle);

        if ($success === false || $status < 200 || $status >= 300) {
            throw new RuntimeException('Konten sumber tidak dapat diambil. Silakan isi secara manual.');
        }

        return [
            'body' => $body,
            'content_type' => $contentType,
        ];
    }

    /**
     * @return array{title: string, source_name: string, summary: string, image_url: ?string}
     */
    public function parseHtml(string $html, string $url): array
    {
        $url = $this->normalizeUrl($url);
        $document = new \DOMDocument();
        $previousErrors = libxml_use_internal_errors(true);
        $loaded = $document->loadHTML(
            '<?xml encoding="UTF-8">' . $html,
            LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING,
        );
        libxml_clear_errors();
        libxml_use_internal_errors($previousErrors);

        if (! $loaded) {
            throw new RuntimeException('Halaman sumber tidak berisi metadata yang dapat dibaca.');
        }

        $metadata = [];
        foreach ($document->getElementsByTagName('meta') as $tag) {
            $key = strtolower(trim($tag->getAttribute('property') ?: $tag->getAttribute('name')));
            $content = trim($tag->getAttribute('content'));
            if ($key !== '' && $content !== '') {
                $metadata[$key] ??= $content;
            }
        }

        $title = $metadata['og:title'] ?? $metadata['twitter:title'] ?? $metadata['title'] ?? '';
        if ($title === '') {
            $titles = $document->getElementsByTagName('title');
            $title = $titles->length > 0 ? trim($titles->item(0)?->textContent ?? '') : '';
        }

        $summary = $metadata['og:description'] ?? $metadata['twitter:description'] ?? $metadata['description'] ?? '';
        $summary = preg_replace('/\s+/u', ' ', trim($summary)) ?? trim($summary);
        $host = (string) parse_url($url, PHP_URL_HOST);
        $sourceName = $metadata['og:site_name'] ?? $metadata['application-name'] ?? $host;
        $imageUrl = $this->absoluteHttpUrl($metadata['og:image'] ?? $metadata['twitter:image'] ?? '', $url);

        if ($title === '') {
            throw new RuntimeException('Judul artikel tidak ditemukan. Silakan isi judul secara manual.');
        }

        return [
            'title' => mb_substr($title, 0, 255),
            'source_name' => mb_substr($sourceName, 0, 255),
            'summary' => mb_substr($summary, 0, 500),
            'image_url' => $imageUrl,
        ];
    }

    private function resolvePublicIpv4(string $host): string
    {
        $records = dns_get_record($host, DNS_A | DNS_AAAA);
        if ($records === false || $records === []) {
            throw new RuntimeException('Domain sumber tidak dapat dijangkau. Silakan isi metadata secara manual.');
        }

        $ipv4 = null;
        foreach ($records as $record) {
            $address = $record['ip'] ?? $record['ipv6'] ?? null;
            if (! is_string($address)) {
                continue;
            }

            if (filter_var($address, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
                throw new InvalidArgumentException('Tautan ke alamat jaringan privat tidak dapat diproses.');
            }

            if (isset($record['ip']) && $ipv4 === null) {
                $ipv4 = $address;
            }
        }

        if ($ipv4 === null) {
            throw new RuntimeException('Domain sumber tidak memiliki alamat IPv4 publik untuk pengambilan metadata.');
        }

        return $ipv4;
    }

    private function absoluteHttpUrl(string $candidate, string $pageUrl): ?string
    {
        if ($candidate === '') {
            return null;
        }

        if (str_starts_with($candidate, '//')) {
            $candidate = (string) parse_url($pageUrl, PHP_URL_SCHEME) . ':' . $candidate;
        } elseif (str_starts_with($candidate, '/')) {
            $parts = parse_url($pageUrl);
            $candidate = $parts['scheme'] . '://' . $parts['host'] . $candidate;
        } elseif (filter_var($candidate, FILTER_VALIDATE_URL) === false) {
            return null;
        }

        $parts = parse_url($candidate);
        if (
            ! is_array($parts)
            || ! in_array(strtolower((string) ($parts['scheme'] ?? '')), ['http', 'https'], true)
            || empty($parts['host'])
            || isset($parts['user'])
            || isset($parts['pass'])
        ) {
            return null;
        }

        try {
            return $this->normalizeUrl($candidate);
        } catch (InvalidArgumentException) {
            return null;
        }
    }
}
