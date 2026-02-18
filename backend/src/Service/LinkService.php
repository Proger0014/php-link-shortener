<?php

declare(strict_types=1);

namespace App\Service;

use App\Config\LinkConfig;
use App\Integration\FaviconExtractor;
use Psr\Log\LoggerInterface;

/**
 * TODO: Вынести сюда логику работы с LinkRepository
 */
class LinkService
{
    function __construct(
        private LinkConfig $linkConfig,
        private FaviconExtractor $faviconExtractor,
        private LoggerInterface $logger,
    ) { }

    public function extractIcon(string $url): ?string
    {
        $this->logger->debug("Процесс экстракции иконки с {url}", [
            'url' => $url,
        ]);

        $iconRaw = $this->faviconExtractor->extract($url);

        if (is_null($iconRaw)) {
            $this->logger->warning("Не удалось извлечь иконку");
            return $this->linkConfig->defaultIcon;
        }

        $this->logger->debug("Процесс нормализации иконки");

        $parsedItemRef = parse_url($iconRaw);
        if (!isset($parsedItemRef['scheme']) || !isset($parsedItemRef['host'])) {
            $parsedUrl = parse_url($url);
            $icon = $parsedUrl['scheme'] . '://' . $parsedUrl['host'] . $parsedItemRef['path'];
        } else {
            $icon = $iconRaw;
        }

        $this->logger->debug("Процесс нормализации завершен. Результат: {icon}", [
            'icon' => $icon,
        ]);

        return $icon;
    }
}
