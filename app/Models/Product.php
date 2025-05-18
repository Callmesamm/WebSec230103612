<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model  {

    protected $fillable = [
        'code',
        'name',
        'price',
        'model',
        'description',
        'photo',
        'stock',
    ];

    public function favoritedBy()
    {
        return $this->belongsToMany(User::class, 'favorite_products', 'product_id', 'user_id')->withTimestamps();
    }
}