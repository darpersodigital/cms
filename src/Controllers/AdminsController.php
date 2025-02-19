<?php

namespace Darpersodigital\Cms\Controllers;
use Illuminate\Routing\Controller as BaseController;


use Illuminate\Http\Request;
use Darpersodigital\Cms\Models\Admin;
use Darpersodigital\Cms\Models\AdminRole;
use Hash;
use Illuminate\Support\Facades\Storage;

class AdminsController extends BaseController
{
    public function index() {
        $rows = Admin::whereNotNull('admin_role_id')->get();
        return view('darpersocms::cms.admins.index', compact('rows'));
    }

    public function create() {
        $admin_roles = [];
        $admin_roles = AdminRole::get();
        return view('darpersocms::cms.admins.form', compact('admin_roles'));
    }

    public function update(Request $request, $id) {
        return $this->saveAdminData($request,$id);
    }

    public function store(Request $request) {
        return $this->saveAdminData($request);
    }

    private function saveAdminData(Request $request, $id = null){
        $request->validate([
            'name' => 'required',
            'image' => 'nullable|image',
            'email' => 'required|unique:admins' . ($id ? ',email,' . $id : ''),
            'password' => $id ? 'nullable|confirmed' : 'required|confirmed',
            'admin_role_id' => 'required',
        ]);
        $row = $id ? Admin::findOrFail($id) : new Admin();
        $row->name = $request->name;
        if ($request->remove_file_image) {
            Storage::delete($row->image);
            $row->image = '';
        } elseif ($request->image) {
            $row->image = request()->file('image')->store('admins');
        }
        $row->email = $request->email;
        if ($request->password) $row->password = Hash::make($request->password);
        $row->admin_role_id = $request->admin_role_id;
        $row->save();
        $message = $id ? 'Record edited successfully' : 'Record added successfully';
        $request->session()->flash('success', $message);
        return redirect(config('cms_config.route_path_prefix') . '/admins');
    }

    public function show($id){
        $row = Admin::findOrFail($id);
        return view('darpersocms::cms.admins.show', compact('row'));
    }

    public function edit($id) {
        $row = Admin::findOrFail($id);
        $admin_roles = [];
        $admin_roles_db = AdminRole::get()->toArray();
        foreach ($admin_roles_db as $single_admin_roles_db) $admin_roles[$single_admin_roles_db['id']] = $single_admin_roles_db;
        return view('darpersocms::cms.admins.form', compact('row', 'admin_roles'));
    }

    public function destroy($id){
        $array = explode(',', $id);
        foreach ($array as $id) {
            $row = Admin::find($id);
            if(isset($row->image)) {
                Storage::delete($row->image);
            }
            Admin::destroy($id);
        }
        return redirect(config('cms_config.route_path_prefix') . '/admins')->with('success', 'Record deleted successfully');
    }
}