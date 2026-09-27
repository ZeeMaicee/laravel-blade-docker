<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Model contoh. Tabelnya bernama `catatan` (bukan `catatans`)
 * supaya penamaannya konsisten dengan bahasa yang dipakai di layar.
 */
class Catatan extends Model
{
    /** @use HasFactory<\Database\Factories\CatatanFactory> */
    use HasFactory;

    protected $table = 'catatan';

    /** @var list<string> */
    protected $fillable = ['judul', 'isi', 'selesai'];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'selesai' => 'boolean',
        ];
    }
}
