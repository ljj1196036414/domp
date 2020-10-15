<?php

namespace App\model;

use Illuminate\Database\Eloquent\Model;

class CategoryModel extends Model
{
    protected $table='p_category';
    protected $primaryKey='cat_id';
    public $timestamps=false;
}
