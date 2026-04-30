<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class Admin extends Authenticatable
{
    use HasFactory, HasRoles, Notifiable, SoftDeletes;

    /** Central DB — admins are global across all tenants */
    protected $connection = 'central';

    protected $guard_name = 'admin';

    protected $fillable = [
        'username', 'name', 'email', 'password', 'legacy_password',
        'avatar', 'last_login_ip', 'last_login_at',
    ];

    protected $hidden = [
        'password', 'legacy_password', 'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'last_login_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function articles()
    {
        return $this->hasMany(Article::class, 'author_id');
    }
}
