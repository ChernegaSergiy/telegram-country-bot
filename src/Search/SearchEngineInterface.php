<?php

namespace Chernegasergiy\TelegramCountryBot\Search;

interface SearchEngineInterface
{
    /**
     * @param string $query
     * @param array $items
     * @param array $keys Поля, за якими шукати
     * @return array
     */
    public function search(string $query, array $items, array $keys): array;
}