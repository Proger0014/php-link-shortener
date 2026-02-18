<?php

declare(strict_types=1);

namespace App\Integration;

use Psr\Log\LoggerInterface;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\HttpClientInterface;

readonly class FaviconExtractor
{
    function __construct(
        private string $defaultIcon,
        private HttpClientInterface $client,
        private RequestStack $requestStack,
        private LoggerInterface $logger
    ) { }

    public function extract(string $url): ?string
    {
        $this->logger->debug("Процесс экстракции favicon из {url}", [
            'url' => $url
        ]);

        $this->logger->debug("Получение html страницы");

        $response = $this->client->request('GET', $url, [
            'headers' => [
                'User-Agent' => $this->requestStack->getCurrentRequest()->headers->get('User-Agent'),
            ]
        ]);

        if ($response->getStatusCode() !== Response::HTTP_OK) {
            $this->logger->warning("Не удалось получить html страницу");

            return $this->defaultIcon;
        }

        try {
            $content = $response->getContent();
        } catch (\Throwable $exception) {
            $this->logger->warning("Не удалось получить контент из запроса");
            return $this->defaultIcon;
        }

        $crawler = new Crawler($content);
        $iconItem = $crawler
            ->filter('head > link[rel="icon"]')
            ->first();

        if ($iconItem->count() !== 0) {
            $iconItemRef = $iconItem->attr('href');

            $parsedItemRef = parse_url($iconItemRef);
            if (!isset($parsedItemRef['scheme']) || !isset($parsedItemRef['host'])) {
                $parsedUrl = parse_url($url);
                $icon = $parsedUrl['scheme'] . '://' . $parsedUrl['host'] . $parsedItemRef['path'];
            } else {
                $icon = $iconItemRef;
            }


            $this->logger->debug("Успешно удалось извлечь иконку {icon}", [
                'icon' => $icon
            ]);
        } else {
            $icon = $this->defaultIcon;

            $this->logger->debug("Была установлена дефолтная иконка");
        }


        return $icon;
    }
}
