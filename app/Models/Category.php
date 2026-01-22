<?php

namespace App\Models;

use App\Models\Category\Attributes;
use App\Models\Category\Relationships;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory, Attributes, Relationships;

    protected $fillable = [
        'name',
        'slug',
    ];

}
