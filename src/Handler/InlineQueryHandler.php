<?php

namespace Chernegasergiy\TelegramCountryBot\Handler;

use Telegram\Bot\Api;
use Telegram\Bot\Objects\Update;
use Chernegasergiy\TelegramCountryBot\Service\CountryService;
use Psr\Log\LoggerInterface;

class InlineQueryHandler
{
    public function __construct(
        private Api $telegram,
        private CountryService $country_service,
        private LoggerInterface $logger
    ) {}

    public function handle(Update $update): void
    {
        $inline_query = $update->getInlineQuery();
        $query_text = $inline_query->getQuery();

        if (empty($query_text)) {
            $this->logger->debug('Empty query, skipping.');
            return;
        }

        $this->country_service->getAllCountries()->onResolve(function ($countries) use ($inline_query, $query_text) {
            try {
                $this->logger->debug("Processing search for: $query_text");
                $search_results = $this->country_service->search($query_text, $countries);
                
                $results = [];
                foreach ($search_results as $index => $country) {
                    $name = $country['name']['common'];
                    $cap = $country['capital'][0] ?? 'N/A';
                    $flag_url = $country['flags']['png'] ?? $country['flags']['svg'] ?? '';
                    $map_url = $country['maps']['googleMaps'] ?? '';
                    $population = number_format($country['population'] ?? 0);
                    
                    $message_text = "<b>📊 Країна: $name</b>\n";
                    $message_text .= "🏛 Столиця: $cap\n";
                    $message_text .= "👥 Населення: $population\n";
                    if (!empty($flag_url)) {
                        $message_text .= "<a href=\"$flag_url\">🖼 Прапор</a>";
                    }

                    $item = [
                        'type'                  => 'article',
                        'id'                    => md5($name . $index . time()),
                        'title'                 => "🌍 $name",
                        'description'           => "Capital: $cap | Pop: $population",
                        'thumbnail_url'         => $flag_url,
                        'input_message_content' => [
                            'message_text' => $message_text,
                            'parse_mode'   => 'HTML',
                        ],
                    ];

                    if (!empty($map_url)) {
                        $item['reply_markup'] = [
                            'inline_keyboard' => [[['text' => '📍 Google Maps', 'url' => $map_url]]]
                        ];
                    }

                    $results[] = $item;
                }

                $this->logger->info("Sending " . count($results) . " results for: $query_text");

                $this->telegram->answerInlineQuery([
                    'inline_query_id' => $inline_query->getId(),
                    'results'         => json_encode($results),
                    'cache_time'      => 300
                ]);

            } catch (\Throwable $e) {
                $this->logger->error('Error in InlineQueryHandler: ' . $e->getMessage(), [
                    'trace' => $e->getTraceAsString()
                ]);
            }
        });
    }
}
