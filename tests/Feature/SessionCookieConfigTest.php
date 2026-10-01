<?php

namespace Tests\Feature;

use Tests\TestCase;

class SessionCookieConfigTest extends TestCase
{
    /**
     * Load the shipped session config with APP_URL forced to the given value.
     *
     * The Secure flag is derived when the config file is evaluated, so it cannot
     * be exercised by mutating the already-booted config repository.
     *
     * @return array<string, mixed>
     */
    private function sessionConfigForAppUrl(string $appUrl): array
    {
        $previous = $_ENV['APP_URL'] ?? null;

        putenv("APP_URL={$appUrl}");
        $_ENV['APP_URL'] = $appUrl;
        $_SERVER['APP_URL'] = $appUrl;

        try {
            return require config_path('session.php');
        } finally {
            if ($previous === null) {
                unset($_ENV['APP_URL']);
                putenv('APP_URL');
            } else {
                putenv("APP_URL={$previous}");
                $_ENV['APP_URL'] = $previous;
            }
        }
    }

    public function test_secure_flag_is_enabled_for_https_app_urls(): void
    {
        $this->assertTrue($this->sessionConfigForAppUrl('https://badbaado.example')['secure']);
    }

    public function test_secure_flag_stays_off_for_local_http_development(): void
    {
        $this->assertFalse($this->sessionConfigForAppUrl('http://localhost:8000')['secure']);
    }

    public function test_session_cookie_defaults_are_hardened(): void
    {
        $config = $this->sessionConfigForAppUrl('https://badbaado.example');

        $this->assertTrue($config['http_only'], 'Session cookie should be HttpOnly.');
        $this->assertSame('lax', $config['same_site']);
    }
}
