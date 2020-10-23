<?php

namespace App\model;
use Encore\Admin\Traits\AdminBuilder;
use Encore\Admin\Traits\ModelTree;
use Illuminate\Database\Eloquent\Model;

class CategoryModel extends Model
{
    use ModelTree, AdminBuilder;
    protected $table='p_category';
    protected $primaryKey = 'cat_id';
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->setParentColumn('parent_id');
        $this->setOrderColumn('sort_order');
        $this->setTitleColumn('cat_name');
    }
}
