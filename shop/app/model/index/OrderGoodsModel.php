<?php

namespace App\model\index;

use Illuminate\Database\Eloquent\Model;

class OrderGoodsModel extends Model
{
    protected $table='p_order_goods';
    protected $primaryKey='rec_id';
    public $timestamps=false;
}
