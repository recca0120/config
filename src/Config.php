<?php

namespace Recca0120\Config;

use Illuminate\Database\Eloquent\Model;

class Config extends Model
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];
}
