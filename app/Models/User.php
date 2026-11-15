<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    public $timestamps = true;

    protected $fillable = [
        'name',
        'email',
        'password_hash',
        'role',
        'phone_number',
    ];

    protected $hidden = [
        'password_hash',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            // Hash otomatis: nilai polos tidak akan pernah tersimpan apa adanya.
            'password_hash' => 'hashed',
        ];
    }

    public function getAuthPassword()
    {
        return $this->password_hash;
    }

    /**
     * Beri tahu guard nama kolom password yang dipakai, agar rehash otomatis
     * (saat algoritma/cost berubah) menulis ke kolom yang benar.
     */
    public function getAuthPasswordName()
    {
        return 'password_hash';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'ADMIN';
    }

    public function isOwner(): bool
    {
        return $this->role === 'OWNER';
    }
}
