<?php

namespace Darpersodigital\Cms\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use Darpersodigital\Cms\Models\PostType;

class FormsController extends BaseController
{
   
    public function star($id, $route) {
        $page = PostType::where('route', $route)->when(request()->get('admin')['admin_role_id'])->firstOrFail();
        $model = 'App\\Models\\' . $page['model_name'];
        if (!class_exists($model))  abort(404);
        $row = $model::findOrFail($id);
        if($row['star']==1){
            $row->star=0;
        }else {
            $row->star=1;
        }
        $row->save();
        return  json_encode($row);
    }

       
    public function read($id, $route) {
        $page = PostType::where('route', $route)->when(request()->get('admin')['admin_role_id'])->firstOrFail();
        $model = 'App\\Models\\' . $page['model_name'];
        if (!class_exists($model))  abort(404);
        $row = $model::findOrFail($id);
        if($row['read']==1){
            $row->read=0;
        }else {
            $row->read=1;
        }
        $row->save();
        return  json_encode($row);
    }
}
