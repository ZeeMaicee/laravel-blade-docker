<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use RuntimeException;

abstract class TestCase extends BaseTestCase
{
    /**
     * Nilai lingkungan yang dipaksa untuk pengujian.
     *
     * Kenapa tidak cukup mengandalkan <env force="true"> di phpunit.xml?
     * Karena docker-compose mengirim `APP_ENV=local` dan `DB_CONNECTION=mysql` sebagai
     * variabel lingkungan SUNGGUHAN ke dalam container. Laravel (Dotenv) membaca
     * $_SERVER lebih dulu, sedangkan PHPUnit dengan force="true" hanya menulis $_ENV
     * dan putenv() — sehingga di dalam container nilai dari compose tetap menang.
     * Akibatnya: CSRF aktif (uji POST/PUT/DELETE gagal dengan HTTP 419) dan
     * `RefreshDatabase` dapat MENGHAPUS basis data MySQL pengembangan.
     *
     * Karena itu ketiga saluran ($_SERVER, $_ENV, putenv) diisi ulang di sini,
     * sebelum aplikasi di-boot oleh parent::setUp().
     *
     * @var array<string, string>
     */
    private const LINGKUNGAN_UJI = [
        'APP_ENV' => 'testing',
        'APP_MAINTENANCE_DRIVER' => 'file',
        'BCRYPT_ROUNDS' => '4',
        'BROADCAST_CONNECTION' => 'null',
        'CACHE_STORE' => 'array',
        'DB_CONNECTION' => 'sqlite',
        'DB_DATABASE' => ':memory:',
        'DB_URL' => '',
        'MAIL_MAILER' => 'array',
        'QUEUE_CONNECTION' => 'sync',
        'SESSION_DRIVER' => 'array',
        'PULSE_ENABLED' => 'false',
        'TELESCOPE_ENABLED' => 'false',
        'NIGHTWATCH_ENABLED' => 'false',
    ];

    protected function setUp(): void
    {
        $this->paksaLingkunganUji();

        parent::setUp();

        $this->pastikanTidakMenyentuhBasisDataNyata();
    }

    private function paksaLingkunganUji(): void
    {
        foreach (self::LINGKUNGAN_UJI as $nama => $nilai) {
            $_ENV[$nama] = $nilai;
            $_SERVER[$nama] = $nilai;
            putenv("{$nama}={$nilai}");
        }
    }

    /**
     * Jaring pengaman terakhir: kalau karena satu dan lain hal uji tetap tersambung ke
     * MySQL (mis. `laravel` milik container), lebih baik gagal keras di sini daripada
     * diam-diam mengosongkan basis data pengembangan.
     */
    private function pastikanTidakMenyentuhBasisDataNyata(): void
    {
        $koneksi = config('database.default');
        $basisData = config("database.connections.{$koneksi}.database");

        if ($koneksi !== 'sqlite') {
            throw new RuntimeException(
                "Uji berjalan dengan koneksi basis data '{$koneksi}', bukan 'sqlite'. ".
                'Ini berbahaya: RefreshDatabase dapat menghapus basis data pengembangan. '.
                'Lihat tests/TestCase.php::LINGKUNGAN_UJI.'
            );
        }

        if ($basisData !== ':memory:') {
            throw new RuntimeException(
                "Uji berjalan dengan basis data '{$basisData}', bukan ':memory:'. ".
                'Jangan memakai basis data di disk untuk pengujian.'
            );
        }
    }
}
