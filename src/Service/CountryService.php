<?php

namespace Chernegasergiy\TelegramCountryBot\Service;

use Fyennyi\AsyncCache\AsyncCacheManager;
use Fyennyi\AsyncCache\CacheOptions;
use Fyennyi\AsyncCache\Enum\CacheStrategy;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Psr16Cache;
use GuzzleHttp\Client;
use Fyennyi\AsyncCache\Core\Future;
use Psr\Log\LoggerInterface;
use Chernegasergiy\TelegramCountryBot\Search\SearchEngineInterface;
use Chernegasergiy\TelegramCountryBot\Search\FuzzySearchEngine;

class CountryService
{
    private AsyncCacheManager $cache_manager;
    private Client $http_client;
    private SearchEngineInterface $search_engine;

    public function __construct(private LoggerInterface $logger)
    {
        $psr_6_cache = new ArrayAdapter();
        $psr_16_cache = new Psr16Cache($psr_6_cache);
        
        $this->cache_manager = new AsyncCacheManager(
            AsyncCacheManager::configure($psr_16_cache)
                ->withLogger($this->logger)
                ->build()
        );
            
        $this->http_client = new Client();
        $this->search_engine = new FuzzySearchEngine();
    }

    public function getAllCountries(): Future
    {
        return $this->cache_manager->wrap(
            'all_countries',
            function () {
                $this->logger->debug('📥 Завантаження даних з API restcountries.com');
                $response = $this->http_client->get('https://restcountries.com/v3.1/all?fields=name,capital,flags,population,maps,altSpellings');
                return json_decode((string)$response->getBody(), true);
            },
            new CacheOptions(ttl: 86400, strategy: CacheStrategy::Background)
        );
    }

    public function search(string $query, array $countries): array
    {
        $this->logger->debug("🔍 Fuzzy search для: '$query'");

        $results = $this->search_engine->search($query, $countries, [
            ['name' => 'name.common', 'weight' => 0.7],
            ['name' => 'name.official', 'weight' => 0.5],
            ['name' => 'altSpellings', 'weight' => 0.3]
        ]);

        $top_results = array_slice($results, 0, 10);
        
        if (count($top_results) > 0) {
            $names = implode(', ', array_map(fn($c) => $c['name']['common'], $top_results));
            $this->logger->debug("✅ Знайдено: $names");
        }

        return $top_results;
    }

    public function clearCache(): Future
    {
        $this->logger->warning('🧹 Викликано повне очищення кешу');
        return $this->cache_manager->clear();
    }
}
