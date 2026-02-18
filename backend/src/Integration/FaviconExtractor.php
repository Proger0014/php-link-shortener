<?php

declare(strict_types=1);

namespace App\Integration;

use App\Util\CacheUtil;
use Psr\Log\LoggerInterface;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

readonly class FaviconExtractor
{
    function __construct(
        private CacheInterface $cache,
        private HttpClientInterface $client,
        private RequestStack $requestStack,
        private LoggerInterface $logger
    ) { }

    public function extract(string $url): ?string
    {
        $this->logger->debug("Процесс экстракции favicon из {url}", [
            'url' => $url
        ]);

        $this->logger->debug("Получение из кэша html страницы");

        $content = $this->cache->get(CacheUtil::createKey($url), function (ItemInterface $item) use ($url) {
            $this->logger->debug("Получение html страницы");

            $response = $this->client->request('GET', $url, [
                'headers' => [
                    'User-Agent' => $this->requestStack->getCurrentRequest()->headers->get('User-Agent'),
                ]
            ]);

            if ($response->getStatusCode() !== Response::HTTP_OK) {
                $this->logger->warning("Не удалось получить html страницу");
                return null;
            }

            try {
                $content = $response->getContent();
                $this->logger->debug("Html страница успешно была получена");
                return $content;
            } catch (\Throwable $exception) {
                $item->expiresAfter(0);
                $this->logger->warning("Не удалось получить контент из запроса");
            }

            return null;
        });

        if (is_null($content)) {
            return null;
        }

        $crawler = new Crawler($content);
        $iconItem = $crawler
            ->filter('head > link[rel="icon"]')
            ->first();

        if ($iconItem->count() > 0) {
            $result = $iconItem->attr('href');
        }

        return $result;
    }
}
