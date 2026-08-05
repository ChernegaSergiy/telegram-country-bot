# Telegram Country Bot

A Telegram bot for searching countries via inline queries. It uses fuzzy search, asynchronous caching with `fyennyi/async-cache-php`, and structured logging with Monolog.

## Features

- **Inline Query Search**: Fuzzy matching for country names via inline Telegram queries, powered by `loilo/fuse`.
- **Asynchronous Caching**: Country data is cached with background refresh using `fyennyi/async-cache-php`.
- **Structured Logging**: Logs are written to stdout and `bot.log` via Monolog with configurable formatting.
- **Country Details**: Results include the country name, capital, population, flag image, and a Google Maps link.
- **Cache Management**: The `/clear` command invalidates cached country data on demand.
- **Long Polling Loop**: The bot continuously fetches updates from Telegram and handles messages and inline queries.

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

## Contributing

Contributions are welcome and appreciated! Here's how you can contribute:

1. Fork the project
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

Please make sure to update tests as appropriate and adhere to the existing coding style.

## License

This project is licensed under the CSSM Unlimited License v2.0 (CSSM-ULv2). See the [LICENSE](LICENSE) file for details.
