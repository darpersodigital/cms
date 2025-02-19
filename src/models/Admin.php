<?php

namespace Darpersodigital\Cms\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Foundation\Auth\User as Authenticatable;

use Darpersodigital\Cms\Models\AdminRole;
class Admin extends Authenticatable
{
    public function role()
    {
    	return $this->belongsTo(AdminRole::class, 'admin_role_id');
    }
}