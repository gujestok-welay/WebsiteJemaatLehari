<?php

use App\Libraries\ExternalNewsMetadata;
use CodeIgniter\Test\CIUnitTestCase;

final class ExternalNewsMetadataTest extends CIUnitTestCase
{
    public function testUrlIsNormalizedForStorageWithoutFragmentsOrDefaultPorts(): void
    {
        $metadata = new ExternalNewsMetadata();

        $this->assertSame(
            'https://sinodegpm.org/berita/pelayanan?q=jemaat',
            $metadata->normalizeUrl(' HTTPS://SINODEGPM.ORG:443/berita/pelayanan?q=jemaat#bagian '),
        );
    }

    public function testUrlRejectsNonWebSchemesPrivateAddressesAndCredentials(): void
    {
        $metadata = new ExternalNewsMetadata();
        foreach ([
            'javascript:alert(1)',
            'ftp://sinodegpm.org/berita',
            'http://127.0.0.1/admin',
            'http://user:pass@sinodegpm.org/berita',
            'https://portal.internal/berita',
            'https://sinodegpm.org:8443/berita',
        ] as $url) {
            try {
                $metadata->normalizeUrl($url);
                $this->fail('Expected the URL to be rejected: ' . $url);
            } catch (InvalidArgumentException) {
                $this->addToAssertionCount(1);
            }
        }
    }

    public function testParserReturnsTitleSourceAndImageCandidateForOperatorReview(): void
    {
        $metadata = new ExternalNewsMetadata();
        $result = $metadata->parseHtml(
            '<html><head>'
            . '<meta property="og:title" content="Pelayanan Kasih Jemaat">'
            . '<meta property="og:site_name" content="Sinode GPM">'
            . '<meta property="og:description" content="Anak dan remaja mengembangkan talenta untuk melayani gereja dan masyarakat.">'
            . '<meta property="og:image" content="//media.sinodegpm.org/cover.jpg">'
            . '</head><body><h1>Different body heading</h1></body></html>',
            'https://sinodegpm.org/berita/pelayanan',
        );

        $this->assertSame('Pelayanan Kasih Jemaat', $result['title']);
        $this->assertSame('Sinode GPM', $result['source_name']);
        $this->assertSame('Anak dan remaja mengembangkan talenta untuk melayani gereja dan masyarakat.', $result['summary']);
        $this->assertSame('https://media.sinodegpm.org/cover.jpg', $result['image_url']);
    }

    public function testParserUsesThePageTitleAndHostnameWhenMetadataIsUnavailable(): void
    {
        $metadata = new ExternalNewsMetadata();
        $result = $metadata->parseHtml(
            '<html><head><title>Judul dari halaman</title>'
            . '<meta property="og:image" content="javascript:alert(1)"></head></html>',
            'https://media.example.org/berita',
        );

        $this->assertSame('Judul dari halaman', $result['title']);
        $this->assertSame('media.example.org', $result['source_name']);
        $this->assertNull($result['image_url']);
    }
}
