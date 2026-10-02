<?php

namespace Tests\Feature;

use Tests\TestCase;

class EnvironmentConfigTest extends TestCase
{
    public function test_test_environment_pins_clinic_timezone_locale_and_in_memory_db(): void
    {
        $xml = simplexml_load_file(base_path('phpunit.xml'));

        $env = [];
        foreach ($xml->php->env as $entry) {
            $env[(string) $entry['name']] = (string) $entry['value'];
        }

        // Kontrak klinik: timezone dan locale terpin di phpunit.xml supaya
        // test di mesin/CI mana pun berjalan sama tanpa bergantung pada .env.
        $this->assertSame('Asia/Jakarta', $env['APP_TIMEZONE'] ?? null);
        $this->assertSame('id', $env['APP_LOCALE'] ?? null);

        // Test wajib memakai SQLite in-memory — tidak boleh menyentuh DB dev.
        $this->assertSame('sqlite', $env['DB_CONNECTION'] ?? null);
        $this->assertSame(':memory:', $env['DB_DATABASE'] ?? null);

        $this->assertSame('Asia/Jakarta', config('app.timezone'));
        $this->assertSame('id', config('app.locale'));
    }
}
