<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dokter extends Model
{
    // Nama tabel (opsional, jika nama tabel sesuai Laravel bisa dihapus)
    protected $table = 'dokter';

    // Kolom yang boleh diisi dari form
    protected $fillable = ['nama', 'spesialisasi', 'status', 'foto'];
}