<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;



class CfMessage extends Model 
{
	

    protected $table = 'cf_messages';

    protected $guarded = ['id'];

    

	protected static function booted(){}

    /* Start custom functions */



    /* End custom functions */
}