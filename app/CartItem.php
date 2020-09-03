<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    protected $fillable = ['cart_id','inventory_id','item_description','quantity','unit_price','pbulk'];
}
