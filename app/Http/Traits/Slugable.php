<?php

namespace App\Http\Traits;

use App\Models\Post;
use Illuminate\Support\Str;
trait Slugable{
    public function slug(){
        $slug = uuid_create();
        $old = Post::where('slug', $slug)->first();
        if($old){
            $slug = $slug.'-'.Str::random(5);
        }
        return $slug;

    }
}