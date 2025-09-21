<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Str;

class BlogTagPivot extends Model
{
    protected $table = 'blog_tags';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = 'id';

    protected $guarded = [
        'id'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }
}
