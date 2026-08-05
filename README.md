# Telegram Country Bot

A Telegram bot for searching countries via inline queries. It uses fuzzy search, asynchronous caching with `fyennyi/async-cache-php`, and structured logging with Monolog.

## Features

- Inline query search for countries by name
- Fuzzy matching via `loilo/fuse`
- Asynchronous caching with background refresh using `fyennyi/async-cache-php`
- Structured logging to stdout and `bot.log`
- `/start` and `/clear` commands
- Country details: name, capital, population, flag, Google Maps link

## Requirements

- PHP 8.2+
- Composer
- Telegram Bot Token (from [@BotFather](https://t.me/BotFather))

## Installation

```bash
git clone git@github.com:ChernegaSergiy/telegram-country-bot.git
cd telegram-country-bot
composer install
```

## Usage

Set your Telegram bot token in `bot.php`:

```php
$token = 'YOUR_BOT_TOKEN';
```

Then run:

```bash
php bot.php
```

The bot works in **inline mode**. Open any chat, type `@your_bot_name <query>`, and select a country from the results.

- `/start` — show welcome message
- `/clear` — clear cached country data

## Architecture

- `src/Core/Application.php` — main loop, update handling, commands
- `src/Service/CountryService.php` — fetch, cache, and search countries
- `src/Search/FuzzySearchEngine.php` — fuzzy search over country names
- `src/Handler/InlineQueryHandler.php` — inline query response formatting

## License

This project is licensed under the CSSM Unlimited License v2.0 (CSSM-ULv2). See the [LICENSE](LICENSE) file for details.
