<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class Blog extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable =
        [
            'title',
            'body',
            'status',
            'tags',
            'is_private',
            'thumbnail',
            'restricted_to',
            'blog_category_id'
        ];

    protected $casts = ['tags' => 'array'];

    /**
     * Category of the blog.
     *
     * @return BelongsToMany
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(BlogCategory::class);
    }
}


