<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ProductStore extends Model
{
    use HasFactory;

    protected $table = 'product_stores';

    protected $guarded = ['id'];

    public $incrementing = false;
    protected $keyType = 'string';

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }

    // relasi ke Product
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // relasi ke Store
    public function store()
    {
        return $this->belongsTo(Store::class);
    }
}
