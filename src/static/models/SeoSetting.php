<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract; use Astrotomic\Translatable\Translatable;

class SeoSetting extends Model  implements TranslatableContract
{
	use Translatable;

    protected $table = 'seo_settings';

    protected $guarded = ['id'];

    protected $hidden = ['translations'];

    public $translatedAttributes = ["seo_image","seo_title","seo_page_title","seo_description","seo_keywords","seo_robots","seo_og_type","seo_site_name"];

	protected static function booted(){}

    /* Start custom functions */



    /* End custom functions */
}