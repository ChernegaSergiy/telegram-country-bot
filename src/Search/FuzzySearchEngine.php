<?php

namespace Chernegasergiy\TelegramCountryBot\Search;

use Fuse\Fuse;

class FuzzySearchEngine implements SearchEngineInterface
{
    public function search(string $query, array $items, array $keys): array
    {
        if (empty($query)) return [];

        $options = [
            'keys' => $keys,
            'threshold' => 0.3,
            'includeScore' => true,
            'shouldSort' => true,
        ];

        $fuse = new Fuse($items, $options);
        $results = $fuse->search($query);

        return array_map(function ($result) {
            $item = $result['item'];
            $item['_score'] = $result['score'];
            return $item;
        }, $results);
    }
}
