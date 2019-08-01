<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Photo extends Model
{
    public static function avatar()
    {
        return asset('images/avatar.png');
    }

    public function profile()
    {
        return $this->morphTo();
    }

    public function file()
    {
        return $this->belongsTo(File::class);
    }

    public function getLinkAttribute()
    {
        return $this->file->link;
    }

}
