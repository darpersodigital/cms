<?php

namespace Darpersodigital\Cms\Controllers;
use Illuminate\Routing\Controller as BaseController;

use Illuminate\Http\Request;
use Darpersodigital\Cms\Models\AdminRolePermission;
use Darpersodigital\Cms\Models\AdminRole;
use Darpersodigital\Cms\Models\PostType;


class AdminRolesController extends BaseController
{
    public function index(){
        $rows = AdminRole::get();
        return view('darpersocms::cms.admin-roles.index', compact('rows'));
    }

    public function create() {
        $post_types_permissions = PostType::where('hidden', 0)->get()->toArray();
        return view('darpersocms::cms.admin-roles.form', compact('post_types_permissions'));
    }
    public function show($id) {
        $row = AdminRole::findOrFail($id);
        $admin_role_permissions = AdminRolePermission::where('admin_role_id', $id)->get();
        return view('darpersocms::cms.admin-roles.show', compact('row', 'admin_role_permissions'));
    }

    private function syncPermissions(Request $request, $adminRoleId, $isUpdate = false) {
        $post_types = PostType::where('hidden', 0)->get();
    
        foreach ($post_types as $post_type) {
            if ($post_type->id == 1) continue;
    
            $permissions = [
                'browse' => isset($request['browse_' . $post_type->id]) ? 1 : 0,
                'read'   => isset($request['read_' . $post_type->id]) ? 1 : 0,
                'edit'   => isset($request['edit_' . $post_type->id]) ? 1 : 0,
                'add'    => isset($request['add_' . $post_type->id]) ? 1 : 0,
                'delete' => isset($request['delete_' . $post_type->id]) ? 1 : 0,
            ];
    
            $hasPermission = array_sum($permissions) > 0; // Check if any permission is granted
    
            if ($isUpdate) {
                $admin_role_permission = AdminRolePermission::where('admin_role_id', $adminRoleId)
                    ->where('post_type_id', $post_type->id)
                    ->first();
    
                if ($admin_role_permission) {
                    if (!$hasPermission) {
                        AdminRolePermission::destroy($admin_role_permission->id);
                        continue;
                    }
                } elseif ($hasPermission) {
                    $admin_role_permission = new AdminRolePermission;
                    $admin_role_permission->admin_role_id = $adminRoleId;
                    $admin_role_permission->post_type_id = $post_type->id;
                } else {
                    continue;
                }
            } else {
                if (!$hasPermission) continue;
    
                $admin_role_permission = new AdminRolePermission;
                $admin_role_permission->admin_role_id = $adminRoleId;
                $admin_role_permission->post_type_id = $post_type->id;
            }
            $admin_role_permission->fill($permissions);
            $admin_role_permission->save();
        }
    }

    public function store(Request $request) {
        return $this->saveAdminRole($request);
    }
    
    public function update(Request $request, $id) {
        return $this->saveAdminRole($request, $id);
    }
    
    private function saveAdminRole(Request $request, $id = null) {
        $request->validate(['title' => 'required']);
        $adminRole = $id ? AdminRole::findOrFail($id) : new AdminRole();
        $adminRole->title = $request->title;
        $adminRole->save();
        $this->syncPermissions($request, $adminRole->id, (bool) $id);
        $message = $id ? 'Record edited successfully' : 'Record added successfully';
        $request->session()->flash('success', $message);
        return redirect(config('cms_config.route_path_prefix') . '/admin-roles');
    }

    public function edit($id) {
        $row = AdminRole::findOrFail($id);
        $post_types = PostType::where('hidden', 0)->get()->toArray();
        $admin_role_permissions = AdminRolePermission::where('admin_role_id', $id)->get();
        $post_types_permissions = $this->getPostTypePermissions($post_types, $admin_role_permissions);
        return view('darpersocms::cms.admin-roles.form', compact(
            'row',
            'post_types_permissions'
        ));
    }

  
    public function destroy($id) {
        $array = explode(',', $id);
        foreach ($array as $id) AdminRole::destroy($id);
        return redirect(config('cms_config.route_path_prefix') . '/admin-roles')->with('success', 'Record deleted successfully');
    }

    public function getPostTypePermissions($post_types, $permissions) {
        $permissionsMap = collect($permissions)->keyBy('post_type_id');
        $defaultPermissions = [
            'browse' => 0,
            'read'   => 0,
            'edit'   => 0,
            'add'    => 0,
            'delete' => 0,
        ];
        return collect($post_types)->map(function ($post_type) use ($permissionsMap,$defaultPermissions) {
            $post_type['permissions'] = $permissionsMap->get($post_type['id'], $defaultPermissions);
            return $post_type;
        })->toArray();
    }
}