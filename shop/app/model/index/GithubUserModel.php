<?php

namespace App\model\index;

use Illuminate\Database\Eloquent\Model;

class GithubUserModel extends Model
{
    protected $table='github';
    protected $primaryKey='uid';
    public $timestamps=false;
}
