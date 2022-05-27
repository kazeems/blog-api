<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'body',
        'post_image'
    ];

    public function getImagesAttribute()
    {
        return [
            "original" => $this->getImagePath("original"),
            "featured_image" => $this->getImagePath("featured"),
        ];
    }

    public function getImagePath($size)
    {
        return Storage::disk($this->disk)->url("uploads/post_images/{$size}/" . $this->post_image);
    }
}
