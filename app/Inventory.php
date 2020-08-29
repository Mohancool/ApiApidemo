<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    protected $hidden = [
        'created_at','updated_at','deleted_at'
    ];
}
