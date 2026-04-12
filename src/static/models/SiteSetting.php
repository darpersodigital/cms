<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract; use Astrotomic\Translatable\Translatable;

class SiteSetting extends Model  implements TranslatableContract
{
	use Translatable;

    protected $table = 'site_settings';

    protected $guarded = ['id'];

    protected $hidden = ['translations'];

    public $translatedAttributes = [];

	protected static function booted(){}

    /* Start custom functions */



    /* End custom functions */
}