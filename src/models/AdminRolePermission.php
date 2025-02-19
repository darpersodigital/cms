<?php

namespace Darpersodigital\Cms\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class AdminRolePermission extends Model
{

    protected $fillable = ['browse','read','edit','add','delete','admin_role_id','post_type_id'];

    public function page() : BelongsTo
    {
        return $this->belongsTo(PostType::class, 'post_type_id');
    }
}