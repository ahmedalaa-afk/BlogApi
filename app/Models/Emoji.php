<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Emoji extends Model
{
    use HasFactory;

    protected $fillable = ['emoji', 'user_id'];

    public function emojiable(){
        return $this->morphTo();
    }

    public function user(){
        return $this->belongsTo(User::class);
    }
}
