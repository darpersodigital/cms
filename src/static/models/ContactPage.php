<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract; use Astrotomic\Translatable\Translatable;

class ContactPage extends Model  implements TranslatableContract
{
	use Translatable;

    protected $table = 'contact_pages';

    protected $guarded = ['id'];

    protected $hidden = ['translations'];

    public $translatedAttributes = ["seo_image","seo_title","seo_description","seo_keywords"];

	protected static function booted(){}

    /* Start custom functions */



    /* End custom functions */
}