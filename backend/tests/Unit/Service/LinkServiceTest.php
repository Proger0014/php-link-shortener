<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Config\LinkConfig;
use App\Integration\FaviconExtractor;
use App\Service\LinkService;
use App\Tests\Unit\TestCase;
use PHPUnit\Framework\Attributes\Test;
use Psr\Log\LoggerInterface;

class LinkServiceTest extends TestCase
{

    #[Test]
    public function extractIcon_notValidUrl_shouldReturnDefaultIcon()
    {
        // Arrange
        $defaultFavicon = 'defaultFavicon';
        $config = new LinkConfig($defaultFavicon);
        $url = 'invalidUrl';

        $extractor = $this->createMock(FaviconExtractor::class);
        $extractor
            ->method('extract')
            ->with($url)
            ->willReturn(null);

        $linkService = new LinkService(
            $config,
            $extractor,
            $this->createMock(LoggerInterface::class)
        );

        // Act
        $actualFavicon = $linkService->extractIcon($url);

        // Assert
        $this->assertEquals($defaultFavicon, $actualFavicon);
    }

    #[Test]
    public function extractIcon_absoluteUrlFavicon_shouldReturnThatFavicon()
    {
        // Arrange
        $config = new LinkConfig('');
        $url = 'url';
        $absoluteUrlFavicon = 'http://example.com/favicon.ico';
        $extractor = $this->createMock(FaviconExtractor::class);
        $extractor
            ->method('extract')
            ->with($url)
            ->willReturn($absoluteUrlFavicon);

        $linkService = new LinkService(
            $config,
            $extractor,
            $this->createMock(LoggerInterface::class)
        );

        // Act
        $actualFavicon = $linkService->extractIcon($url);

        // Assert
        $this->assertEquals($absoluteUrlFavicon, $actualFavicon);
    }

    #[Test]
    public function extractIcon_relativeUrlFavicon_shouldReturnAbsoluteUrlFavicon()
    {
        // Arrange
        $config = new LinkConfig('');
        $url = 'http://example.com/asdasd/asd/fdfd';
        $relativeUrlFavicon = '/favicon.ico';
        $extractor = $this->createMock(FaviconExtractor::class);
        $extractor
            ->method('extract')
            ->with($url)
            ->willReturn($relativeUrlFavicon);

        $expectedFavicon = 'http://example.com/favicon.ico';

        $linkService = new LinkService(
            $config,
            $extractor,
            $this->createMock(LoggerInterface::class)
        );

        // Act
        $actualFavicon = $linkService->extractIcon($url);

        // Assert
        $this->assertEquals($expectedFavicon, $actualFavicon);
    }
}
