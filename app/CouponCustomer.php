<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CouponCustomer extends Model
{
    protected $table = 'coupon_customer';

    public function coupon()
    {
        return $this->belongsTo('App\Coupon');
    }
}
