<?php

namespace App\Models;

use App\Models\Author\Attributes;
use App\Models\Author\Relationships;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Author extends Model
{
    use HasFactory, Attributes, Relationships;

    protected $fillable = [
        'name',
        'slug',
    ];

}
