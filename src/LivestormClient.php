<?php

namespace JeffersonGoncalves\Livestorm;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use JeffersonGoncalves\Livestorm\Exceptions\LivestormAuthenticationException;
use Throwable;

/**
 * Livestorm (api.livestorm.co/v1) REST API client. Wraps events, sessions,
 * people, and webhooks behind a small static client, threading the Bearer
 * token and centralising 401 detection so callers get a thrown
 * LivestormAuthenticationException rather than a silent null.
 */
class LivestormClient
{
    /**
     * @return array<string, mixed>|null
     */
    public static function ping(): ?array
    {
        return self::decode(self::get('/ping'));
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function organization(): ?array
    {
        return self::decode(self::get('/organization'));
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function events(?string $title = null, int $page = 1, int $perPage = 25): array
    {
        $query = self::pageQuery($page, $perPage);

        if ($title !== null) {
            $query['filter'] = ['title' => $title];
        }

        return self::decodeList(self::get('/events', $query));
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function event(string $id): ?array
    {
        return self::decode(self::get("/events/{$id}"));
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function createEvent(string $title, ?string $slug = null, ?string $description = null, ?int $estimatedDuration = null): ?array
    {
        $attributes = ['title' => $title];

        if ($slug !== null) {
            $attributes['slug'] = $slug;
        }

        if ($description !== null) {
            $attributes['description'] = $description;
        }

        if ($estimatedDuration !== null) {
            $attributes['estimated_duration'] = $estimatedDuration;
        }

        $body = ['data' => ['type' => 'events', 'attributes' => $attributes]];

        return self::decode(self::post('/events', $body));
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function updateEvent(string $id, ?string $title = null, ?string $description = null, ?string $slug = null): ?array
    {
        $attributes = array_filter([
            'title' => $title,
            'description' => $description,
            'slug' => $slug,
        ], fn (?string $value) => $value !== null);

        $body = ['data' => ['type' => 'events', 'id' => $id, 'attributes' => $attributes]];

        return self::decode(self::patch("/events/{$id}", $body));
    }

    public static function deleteEvent(string $id): bool
    {
        return self::successful(self::delete("/events/{$id}"));
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function eventPeople(string $id, int $page = 1, int $perPage = 25): array
    {
        return self::decodeList(self::get("/events/{$id}/people", self::pageQuery($page, $perPage)));
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function sessions(int $page = 1, int $perPage = 25): array
    {
        return self::decodeList(self::get('/sessions', self::pageQuery($page, $perPage)));
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function session(string $id): ?array
    {
        return self::decode(self::get("/sessions/{$id}"));
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function createSession(string $eventId, ?string $estimatedStartedAt = null, ?string $timezone = null): ?array
    {
        $attributes = array_filter([
            'estimated_started_at' => $estimatedStartedAt,
            'timezone' => $timezone,
        ], fn (?string $value) => $value !== null);

        $body = ['data' => ['type' => 'sessions', 'attributes' => $attributes]];

        return self::decode(self::post("/events/{$eventId}/sessions", $body));
    }

    public static function deleteSession(string $id): bool
    {
        return self::successful(self::delete("/sessions/{$id}"));
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function sessionPeople(string $id, int $page = 1, int $perPage = 25): array
    {
        return self::decodeList(self::get("/sessions/{$id}/people", self::pageQuery($page, $perPage)));
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function registerToSession(string $id, string $email, ?string $firstName = null, ?string $lastName = null): ?array
    {
        $fields = array_filter([
            'email' => $email,
            'first_name' => $firstName,
            'last_name' => $lastName,
        ], fn (?string $value) => $value !== null);

        $body = ['data' => ['type' => 'people', 'attributes' => ['fields' => $fields]]];

        return self::decode(self::post("/sessions/{$id}/people", $body));
    }

    public static function unregisterFromSession(string $id, string $email): bool
    {
        return self::successful(self::delete("/sessions/{$id}/people?filter[email]=".urlencode($email)));
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function sessionChatMessages(string $id, int $page = 1, int $perPage = 25): array
    {
        return self::decodeList(self::get("/sessions/{$id}/chat-messages", self::pageQuery($page, $perPage)));
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function sessionQuestions(string $id, int $page = 1, int $perPage = 25): array
    {
        return self::decodeList(self::get("/sessions/{$id}/questions", self::pageQuery($page, $perPage)));
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function sessionRecordings(string $id): array
    {
        return self::decodeList(self::get("/sessions/{$id}/recordings"));
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function people(?string $email = null, int $page = 1, int $perPage = 25): array
    {
        $query = self::pageQuery($page, $perPage);

        if ($email !== null) {
            $query['filter'] = ['email' => $email];
        }

        return self::decodeList(self::get('/people', $query));
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function person(string $id): ?array
    {
        return self::decode(self::get("/people/{$id}"));
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function webhooks(): array
    {
        return self::decodeList(self::get('/webhooks'));
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function createWebhook(string $url, string $event = 'attendance'): ?array
    {
        $body = ['data' => ['type' => 'webhooks', 'attributes' => ['target_url' => $url, 'event_name' => $event]]];

        return self::decode(self::post('/webhooks', $body));
    }

    public static function deleteWebhook(string $id): bool
    {
        return self::successful(self::delete("/webhooks/{$id}"));
    }

    /**
     * @return array<string, array<string, int>>
     */
    private static function pageQuery(int $page, int $perPage): array
    {
        return ['page' => ['number' => $page, 'size' => $perPage]];
    }

    /**
     * @param  array<string, mixed>  $query
     *
     * @throws LivestormAuthenticationException
     */
    private static function get(string $path, array $query = []): ?Response
    {
        return self::request(fn () => self::client()->get(self::baseUrl().$path, $query), 'livestorm_get', $path);
    }

    /**
     * @param  array<string, mixed>  $body
     *
     * @throws LivestormAuthenticationException
     */
    private static function post(string $path, array $body): ?Response
    {
        return self::request(fn () => self::client()->post(self::baseUrl().$path, $body), 'livestorm_post', $path);
    }

    /**
     * @param  array<string, mixed>  $body
     *
     * @throws LivestormAuthenticationException
     */
    private static function patch(string $path, array $body): ?Response
    {
        return self::request(fn () => self::client()->patch(self::baseUrl().$path, $body), 'livestorm_patch', $path);
    }

    /**
     * @throws LivestormAuthenticationException
     */
    private static function delete(string $path): ?Response
    {
        return self::request(fn () => self::client()->delete(self::baseUrl().$path), 'livestorm_delete', $path);
    }

    /**
     * @param  callable(): Response  $send
     *
     * @throws LivestormAuthenticationException
     */
    private static function request(callable $send, string $context, string $target): ?Response
    {
        try {
            $response = $send();
        } catch (Throwable $e) {
            self::logFailure($context, $target, $e);

            return null;
        }

        // 401 detection runs outside the catch above so the thrown exception
        // propagates to the caller instead of being swallowed and logged as
        // a generic fetch failure.
        if ($response->status() === 401) {
            throw new LivestormAuthenticationException;
        }

        return $response;
    }

    private static function successful(?Response $response): bool
    {
        return $response !== null && $response->successful();
    }

    /**
     * @return array<string, mixed>|null
     */
    private static function decode(?Response $response): ?array
    {
        if ($response === null || ! $response->successful()) {
            return null;
        }

        $json = $response->json();

        if (! is_array($json)) {
            return null;
        }

        // Livestorm uses the JSON:API format: the payload lives under a
        // "data" key. Unwrap it when present, otherwise return the raw body.
        return is_array($json['data'] ?? null) ? $json['data'] : $json;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private static function decodeList(?Response $response): array
    {
        $data = self::decode($response);

        return is_array($data) ? array_values($data) : [];
    }

    private static function client(): PendingRequest
    {
        return Http::timeout(self::timeout())->withHeaders([
            'Authorization' => 'Bearer '.(string) config('livestorm.api_token'),
            'Content-Type' => 'application/vnd.api+json',
            'Accept' => 'application/vnd.api+json',
        ]);
    }

    private static function logFailure(string $context, string $target, Throwable $e): void
    {
        Log::warning('LivestormClient outbound fetch failed', [
            'context' => $context,
            'target' => $target,
            'exception' => $e::class,
            'message' => $e->getMessage(),
        ]);
    }

    private static function baseUrl(): string
    {
        $baseUrl = config('livestorm.base_url');

        return is_string($baseUrl) && $baseUrl !== '' ? rtrim($baseUrl, '/') : 'https://api.livestorm.co/v1';
    }

    private static function timeout(): int
    {
        return (int) config('livestorm.timeout', 8);
    }
}
