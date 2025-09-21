<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class RecommendedProduct extends Model
{
    protected $table = 'recommended_products';

    public $incrementing = false; 
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'product_id',
        'section',
        'sort_order',
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

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
