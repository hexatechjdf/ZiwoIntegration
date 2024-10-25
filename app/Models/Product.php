<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'name',
        'description',
        'sku',
        'crm_product_id',
        'crm_price_id',
        'price',
        'rrp',
        'group_price'
    ];

    public static function manageProduct($data)
    {
        return self::insert($data);
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
