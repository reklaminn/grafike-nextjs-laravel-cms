<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'location', 'theme_variant', 'language_id', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function items()
    {
        return $this->hasMany(MenuItem::class);
    }

    public function language()
    {
        return $this->belongsTo(Language::class);
    }

    public function rootItems()
    {
        return $this->items()->whereNull('parent_id')->orderBy('sort_order');
    }
}
