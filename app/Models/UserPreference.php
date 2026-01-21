<?php

namespace App\Models;

use App\Models\UserPreference\Attributes;
use App\Models\UserPreference\Relationships;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class UserPreference
 *
 * @package App\Models
 *
 * @mixin Attributes
 * @mixin Relationships
 */
class UserPreference extends Model
{
    use Attributes, Relationships, HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'preferred_sources',
        'preferred_categories',
        'preferred_authors',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'preferred_sources'    => 'array',
        'preferred_categories' => 'array',
        'preferred_authors'    => 'array'
    ];
}
