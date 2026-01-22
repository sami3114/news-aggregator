<?php

namespace App\Models;

use App\Models\Article\Attributes;
use App\Models\Article\Relationships;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Article
 *
 * @package App\Models
 *
 * @mixin Attributes
 * @mixin Relationships
 */
class Article extends Model
{
    use Attributes, Relationships, HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'external_id',
        'source',
        'source_name',
        'author_id',
        'title',
        'description',
        'content',
        'url',
        'image_url',
        'category',
        'published_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'published_at' => 'datetime',
    ];
}
