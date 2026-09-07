<div class="filament-hidden">

<!-- banner: art/jeffersongoncalves-laravel-livestorm.png (generate via portfolio-banner skill) -->

</div>

# Laravel Livestorm

[![Tests](https://github.com/jeffersongoncalves/laravel-livestorm/actions/workflows/tests.yml/badge.svg)](https://github.com/jeffersongoncalves/laravel-livestorm/actions/workflows/tests.yml)
[![PHPStan](https://github.com/jeffersongoncalves/laravel-livestorm/actions/workflows/phpstan.yml/badge.svg)](https://github.com/jeffersongoncalves/laravel-livestorm/actions/workflows/phpstan.yml)
[![Code Style](https://github.com/jeffersongoncalves/laravel-livestorm/actions/workflows/pint.yml/badge.svg)](https://github.com/jeffersongoncalves/laravel-livestorm/actions/workflows/pint.yml)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/jeffersongoncalves/laravel-livestorm.svg?style=flat-square)](https://packagist.org/packages/jeffersongoncalves/laravel-livestorm)
[![Total Downloads](https://img.shields.io/packagist/dt/jeffersongoncalves/laravel-livestorm.svg?style=flat-square)](https://packagist.org/packages/jeffersongoncalves/laravel-livestorm)
[![License](https://img.shields.io/packagist/l/jeffersongoncalves/laravel-livestorm.svg?style=flat-square)](LICENSE.md)

A lightweight [Livestorm](https://livestorm.co) webinar API client for Laravel. It wraps the `api.livestorm.co/v1` JSON:API endpoints behind a small static client, threads your Bearer token, and centralises 401 detection so callers get a thrown `LivestormAuthenticationException` rather than a silent failure during a bad-credential window.

## Features

- **Events** — `events()`, `event()`, `createEvent()`, `updateEvent()`, `deleteEvent()`, `eventPeople()`
- **Sessions** — `sessions()`, `session()`, `createSession()`, `deleteSession()`, `sessionPeople()`, `registerToSession()`, `unregisterFromSession()`, `sessionChatMessages()`, `sessionQuestions()`, `sessionRecordings()`
- **People** — `people()`, `person()`
- **Webhooks** — `webhooks()`, `createWebhook()`, `deleteWebhook()`
- **Health check** — `ping()`, `organization()`
- **Credential aware** — throws `LivestormAuthenticationException` on a 401 so a bad token doesn't look like "no data"

## Installation

```bash
composer require jeffersongoncalves/laravel-livestorm
```

Optionally publish the config file:

```bash
php artisan vendor:publish --tag="livestorm-config"
```

## Configuration

Add to your `.env`:

```env
LIVESTORM_API_TOKEN=your-api-token
```

Find yours at [app.livestorm.co/settings/integrations/api](https://app.livestorm.co/settings/integrations/api).

### Config Options

```php
// config/livestorm.php
return [
    'api_token' => env('LIVESTORM_API_TOKEN'),
    'base_url' => env('LIVESTORM_BASE_URL', 'https://api.livestorm.co/v1'),
    'timeout' => (int) env('LIVESTORM_TIMEOUT', 8),
];
```

## Usage

```php
use JeffersonGoncalves\Livestorm\LivestormClient;

// Health check
LivestormClient::ping();
LivestormClient::organization();

// Events
$events = LivestormClient::events(title: 'Product Launch');
$event = LivestormClient::event('event-id');
$event = LivestormClient::createEvent('Product Launch', description: 'Q4 launch webinar');
LivestormClient::updateEvent('event-id', title: 'Product Launch 2.0');
LivestormClient::deleteEvent('event-id');
$attendees = LivestormClient::eventPeople('event-id');

// Sessions
$sessions = LivestormClient::sessions();
$session = LivestormClient::createSession('event-id', estimatedStartedAt: '2026-10-01T10:00:00Z');
LivestormClient::registerToSession('session-id', 'jane@example.com', firstName: 'Jane', lastName: 'Doe');
LivestormClient::unregisterFromSession('session-id', 'jane@example.com');
$recordings = LivestormClient::sessionRecordings('session-id');

// People
$people = LivestormClient::people(email: 'jane@example.com');

// Webhooks
LivestormClient::createWebhook('https://example.com/webhooks/livestorm', event: 'attendance');
```

### Handling authentication failures

```php
use JeffersonGoncalves\Livestorm\Exceptions\LivestormAuthenticationException;

try {
    $events = LivestormClient::events();
} catch (LivestormAuthenticationException $e) {
    // LIVESTORM_API_TOKEN is missing, wrong, or revoked.
}
```

## Testing

```bash
composer test
```

## Static Analysis

```bash
composer analyse
```

## Code Formatting

```bash
composer format
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email the author instead of using the issue tracker.

## Credits

- [jeffersongoncalves](https://github.com/jeffersongoncalves)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
