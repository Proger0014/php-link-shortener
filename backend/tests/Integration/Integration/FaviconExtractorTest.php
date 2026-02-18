<?php

declare(strict_types=1);

namespace App\Tests\Integration\Integration;

use App\Integration\FaviconExtractor;
use App\Tests\Integration\NonTest\Config\FaviconExtractorConfig;
use App\Tests\Integration\TestCase;
use App\Util\CacheUtil;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Cache\CacheInterface;

class FaviconExtractorTest extends TestCase
{
    #[Test]
    public function extract_haveFavicon_shouldReturnUrl()
    {
        // Arrange
        /** @var FaviconExtractorConfig $config */
        $config = static::getContainer()->get(FaviconExtractorConfig::class);

        $requestUri = $config->haveFavicon;
        $expectedResponse = 'http://localhost/favicon.ico';

        $cache = static::getContainer()->get(CacheInterface::class);
        $cache->delete(CacheUtil::createKey($requestUri));

        /** @var RequestStack $requestStack */
        $requestStack = static::getContainer()->get(RequestStack::class);
        $requestStack->push(self::createRequest($requestUri));

        /** @var FaviconExtractor $faviconExtractor */
        $faviconExtractor = static::getContainer()->get(FaviconExtractor::class);

        // Act
        $actualResponse = $faviconExtractor->extract($requestUri);

        // Assert
        $this->assertEquals($expectedResponse, $actualResponse);
    }

    public static function createRequest(String $uri): Request
    {
        return Request::create(
            uri: $uri,
            parameters: [
                'HTTP_USER_AGENT'
            ]
        );
    }
}
