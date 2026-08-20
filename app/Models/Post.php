<?php

namespace App\Models;

use App\Enums\PostStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Post extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['author_id', 'title', 'slug', 'excerpt', 'body', 'cover', 'tag', 'status', 'published_at'];

    protected $casts = [
        'status'       => PostStatus::class,
        'published_at' => 'datetime',
    ];

    public function author(): BelongsTo { return $this->belongsTo(User::class, 'author_id'); }

    public function places(): BelongsToMany
    {
        return $this->belongsToMany(Place::class)->withPivot('sort')->orderBy('pivot_sort');
    }
}
