<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use App\Models\Role;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'birthdate',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function hasRole(string $roleName): bool
    {
        return $this->roles()->where('name', $roleName)->exists();
    }

    public function verlofaanvragen()
    {
        return $this->hasMany(Verlofaanvraag::class);
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    protected static function boot()
    {
        parent::boot();

        // static::saving(function ($user) {
        //     if ($user->isDirty('password')) {
        //         $user->password = Hash::make($user->password);
        //     }
        // });
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    public function assignRole($role)
    {
        if (is_string($role)) {
            $role = Role::where('name', $role)->firstOrFail();
        }

        $this->roles()->syncWithoutDetaching([$role->id]);
    }
}
