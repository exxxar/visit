<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MapLayer extends Model
{
    protected $fillable = ['name', 'slug', 'description', 'color', 'icon', 'is_active', 'sort_order'];
    protected $casts = ['is_active' => 'boolean'];

    public function objects(): HasMany
    {
        return $this->hasMany(MapObject::class, 'layer_id')
            ->where('is_active', true)->orderBy('sort_order');
    }
}
