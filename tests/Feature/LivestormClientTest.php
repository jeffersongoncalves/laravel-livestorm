<?php

use Illuminate\Support\Facades\Http;
use JeffersonGoncalves\Livestorm\Exceptions\LivestormAuthenticationException;
use JeffersonGoncalves\Livestorm\LivestormClient;

beforeEach(function () {
    config(['livestorm.api_token' => 'test-token']);
});

it('pings the API', function () {
    Http::fake([
        'api.livestorm.co/v1/ping' => Http::response(['status' => 'ok']),
    ]);

    expect(LivestormClient::ping())->toBe(['status' => 'ok']);

    Http::assertSent(fn ($request) => $request->hasHeader('Authorization', 'Bearer test-token')
        && $request->hasHeader('Accept', 'application/vnd.api+json'));
});

it('lists events', function () {
    Http::fake([
        'api.livestorm.co/v1/events*' => Http::response([
            'data' => [
                ['id' => '1', 'type' => 'events', 'attributes' => ['title' => 'Launch']],
            ],
        ]),
    ]);

    $events = LivestormClient::events(title: 'Launch');

    expect($events)->toBe([
        ['id' => '1', 'type' => 'events', 'attributes' => ['title' => 'Launch']],
    ]);

    Http::assertSent(fn ($request) => str_contains((string) $request->url(), 'filter%5Btitle%5D=Launch')
        && str_contains((string) $request->url(), 'page%5Bnumber%5D=1'));
});

it('creates an event', function () {
    Http::fake([
        'api.livestorm.co/v1/events' => Http::response([
            'data' => ['id' => '42', 'type' => 'events', 'attributes' => ['title' => 'Launch']],
        ]),
    ]);

    $event = LivestormClient::createEvent('Launch');

    expect($event)->toBe(['id' => '42', 'type' => 'events', 'attributes' => ['title' => 'Launch']]);

    Http::assertSent(fn ($request) => $request->method() === 'POST'
        && $request['data']['type'] === 'events'
        && $request['data']['attributes']['title'] === 'Launch');
});

it('throws on a 401 response', function () {
    Http::fake([
        'api.livestorm.co/v1/ping' => Http::response(['error' => 'unauthorized'], 401),
    ]);

    LivestormClient::ping();
})->throws(LivestormAuthenticationException::class);
