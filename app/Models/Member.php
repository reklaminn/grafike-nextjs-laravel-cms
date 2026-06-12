<?php

namespace App\Models;

use App\Notifications\MemberResetPasswordNotification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Member extends Authenticatable
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 'email', 'password', 'phone', 'group_id',
        'is_active', 'language_id', 'legacy_id',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'is_active' => 'boolean',
            'password' => 'hashed',
        ];
    }

    /**
     * Send a branded password-reset notification.
     * Overrides the default Laravel notification so the email carries
     * the tenant's name, logo and primary colour from SiteSettings.
     */
    public function sendPasswordResetNotification(#[\SensitiveParameter] $token): void
    {
        $this->notify(new MemberResetPasswordNotification($token));
    }

    public function group()
    {
        return $this->belongsTo(MemberGroup::class, 'group_id');
    }

    public function language()
    {
        return $this->belongsTo(Language::class);
    }
}
