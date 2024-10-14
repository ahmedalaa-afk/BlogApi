<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Tags\HasTags;

class Post extends Model
{
    use HasFactory;
    use HasTags;

    protected $fillable = [
        'content',
        'slug',
        'user_id',
        'photo',
        'video',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function images()
    {
        return $this->morphMany(Image::class, 'imageable');
    }

    public function videos()
    {
        return $this->morphMany(Video::class, 'videoable');
    }
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function emoji()
    {
        return $this->morphMany(Emoji::class, 'emojiable');
    }

    public function countLikes()
    {
        return $this->emoji()->where('emoji', 'like')->count();
    }
    public function countDislikes()
    {
        return $this->emoji()->where('emoji', 'dislike')->count();
    }

    public function favorite(){
        return $this->belongsTo(Favorite::class);
    }
}
