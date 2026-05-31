<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Form extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 'slug', 'description', 'is_active', 'requires_captcha',
        'notification_email', 'smtp_host', 'smtp_port', 'smtp_username',
        'smtp_password', 'smtp_encryption', 'allow_submissions', 'allow_listing',
        'save_to_database', 'language_id', 'legacy_id',
        'webhook_url', 'webhook_enabled',
        // is_system intentionally excluded from fillable — set only via migration/seeder
    ];

    protected function casts(): array
    {
        return [
            'is_active'        => 'boolean',
            'is_system'        => 'boolean',
            'requires_captcha' => 'boolean',
            'allow_submissions'=> 'boolean',
            'allow_listing'    => 'boolean',
            'save_to_database' => 'boolean',
            'webhook_enabled'  => 'boolean',
        ];
    }

    public function fields()
    {
        return $this->hasMany(FormField::class)->orderBy('sort_order');
    }

    public function submissions()
    {
        return $this->hasMany(FormSubmission::class);
    }

    public function language()
    {
        return $this->belongsTo(Language::class);
    }
}
