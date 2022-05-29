<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'slug',
        'body',
        'post_image',
        'disk',
        'upload_successful',
        'category_id',
        'post_type'
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function comments() {
        return $this->hasMany(Comment::class)->whereNull('parent_id');
    }

    public function category() {
        return $this->belongsTo(Category::class);
    }

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
