<?php

namespace Chernegasergiy\TelegramCountryBot\Core;

use Telegram\Bot\Api;
use Chernegasergiy\TelegramCountryBot\Service\CountryService;
use Chernegasergiy\TelegramCountryBot\Handler\InlineQueryHandler;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\LineFormatter;
use Psr\Log\LoggerInterface;

class Application
{
    private Api $telegram;
    private CountryService $country_service;
    private InlineQueryHandler $inline_handler;
    private LoggerInterface $logger;

    public function __construct(string $token)
    {
        $log_format = "[%datetime%] %level_name%: %message% %context% %extra%\n";
        $formatter = new LineFormatter($log_format, "H:i:s", true, true);

        $stdout_handler = new StreamHandler('php://stdout', Logger::DEBUG);
        $stdout_handler->setFormatter($formatter);

        $file_handler = new StreamHandler('bot.log', Logger::INFO);
        $file_handler->setFormatter(new LineFormatter(null, null, true, true));

        $this->logger = new Logger('country-bot');
        $this->logger->pushHandler($stdout_handler);
        $this->logger->pushHandler($file_handler);

        $this->telegram = new Api($token);
        $this->country_service = new CountryService($this->logger);
        $this->inline_handler = new InlineQueryHandler($this->telegram, $this->country_service, $this->logger);
    }

    public function run(): void
    {
        $this->logger->info('🚀 Бот запущений у режимі Long Polling');
        
        $last_update_id = 0;
        while (true) {
            try {
                $updates = $this->telegram->getUpdates([
                    'offset' => $last_update_id + 1,
                    'timeout' => 20
                ]);

                foreach ($updates as $update) {
                    $last_update_id = $update->getUpdateId();
                    
                    if ($update->has('inline_query')) {
                        $this->logger->debug('🔎 Отримано Inline Query', [
                            'from' => $update->getInlineQuery()->getFrom()->getUsername(),
                            'query' => $update->getInlineQuery()->getQuery()
                        ]);
                        $this->inline_handler->handle($update);
                    }
                    
                    if ($update->has('message')) {
                        $this->handleMessage($update);
                    }
                }
            } catch (\Throwable $e) {
                $this->logger->error('🔴 Помилка циклу оновлень', [
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                sleep(2);
            }
            usleep(100000);
        }
    }

    private function handleMessage($update): void
    {
        $message = $update->getMessage();
        $chat_id = $message->getChat()->getId();
        $text = $message->getText();

        $this->logger->info('💬 Отримано повідомлення', [
            'from' => $message->getFrom()->getUsername(),
            'text' => $text
        ]);

        if ($text === '/start') {
            $this->telegram->sendMessage([
                'chat_id' => $chat_id,
                'text' => "🌍 Привіт! Я класичний бот для пошуку країн з Monolog та AsyncCache.\nВикористовуй мене в інлайн-режимі: @твій_бот назва"
            ]);
        }

        if ($text === '/clear') {
            try {
                $success = $this->country_service->clearCache()->wait();
                $this->telegram->sendMessage([
                    'chat_id' => $chat_id,
                    'text' => $success ? "✅ Кеш успішно очищено!" : "❌ Помилка при очищенні кешу."
                ]);
            } catch (\Throwable $e) {
                $this->logger->error('Помилка очищення кешу: ' . $e->getMessage());
            }
        }
    }
}