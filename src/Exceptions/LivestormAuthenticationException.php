<?php

namespace JeffersonGoncalves\Livestorm\Exceptions;

use RuntimeException;

/**
 * Raised when Livestorm answers a call with a 401 — the api_token is
 * missing, wrong, or revoked. Thrown instead of returning a silent
 * null/[] so a misconfigured credential doesn't look like "no data".
 */
class LivestormAuthenticationException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Livestorm API authentication failed. Check the LIVESTORM_API_TOKEN credential.');
    }
}
