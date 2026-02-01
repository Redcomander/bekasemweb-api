<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // ==========================================
    // RELATIONSHIPS
    // ==========================================

    /**
     * Roles user
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_user')
            ->withTimestamps();
    }

    /**
     * Notifikasi user
     */
    public function notifikasis(): HasMany
    {
        return $this->hasMany(Notifikasi::class);
    }

    /**
     * Berita yang ditulis
     */
    public function beritas(): HasMany
    {
        return $this->hasMany(Berita::class, 'author_id');
    }

    // ==========================================
    // ROLE & PERMISSION HELPERS
    // ==========================================

    /**
     * Cek apakah user memiliki role tertentu
     */
    public function hasRole(string $roleName): bool
    {
        return $this->roles()->where('name', $roleName)->exists();
    }

    /**
     * Cek apakah user memiliki salah satu dari roles
     */
    public function hasAnyRole(array $roleNames): bool
    {
        return $this->roles()->whereIn('name', $roleNames)->exists();
    }

    /**
     * Cek apakah user memiliki permission tertentu (melalui role)
     */
    public function hasPermission(string $permissionName): bool
    {
        foreach ($this->roles as $role) {
            if ($role->hasPermission($permissionName)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Cek apakah user adalah super admin
     */
    public function isSuperAdmin(): bool
    {
        return $this->hasRole('super_admin');
    }

    /**
     * Cek apakah user adalah admin KUA
     */
    public function isAdminKua(): bool
    {
        return $this->hasAnyRole(['super_admin', 'admin_kua']);
    }

    /**
     * Cek apakah user adalah penghulu
     */
    public function isPenghulu(): bool
    {
        return $this->hasRole('penghulu');
    }

    // ==========================================
    // NOTIFICATION HELPERS
    // ==========================================

    /**
     * Kirim notifikasi internal
     */
    public function sendNotifikasi(string $judul, string $pesan, array $options = []): Notifikasi
    {
        return Notifikasi::notify($this->id, $judul, $pesan, $options);
    }

    /**
     * Jumlah notifikasi yang belum dibaca
     */
    public function unreadNotificationsCount(): int
    {
        return $this->notifikasis()->unread()->count();
    }
}
