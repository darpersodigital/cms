<?php

namespace Darpersodigital\Cms\Controllers\admin;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use Darpersodigital\Cms\Models\Admin;
use Darpersodigital\Cms\Models\AdminRole;
use Hash;
use Auth;
use Illuminate\Support\Facades\Storage;
use Darpersodigital\Cms\Controllers\FileUploadController;

class AdminsController extends BaseController
{
    private $FileUploadController;

    public function __construct(FileUploadController $FileUploadController)
    {
        $this->FileUploadController = $FileUploadController;
    }

    public function showProfile()
    {
        return response()->view('darpersocms::cms.profile.show')->withCookie(cookie('loginDate', now(), 120));
    }

    public function editProfile(Request $request)
    {
        $admin = Auth::guard('admin')->user();
        return $this->saveAdminData($request, $admin->id, true);
    }
    public function showEditProfile()
    {
        return view('darpersocms::cms.profile.edit');
    }

    public function index()
    {
        $admin= Auth::guard('admin')->user();
        $rows = Admin::whereNotNull('admin_role_id')->where('id', '!=', $admin->id)->get();
        return view('darpersocms::cms.admins.index', compact('rows'));
    }

    public function create()
    {
        $admin_roles = [];
        $admin_roles = AdminRole::get();
        return view('darpersocms::cms.admins.form', compact('admin_roles'));
    }

    public function update(Request $request, $id)
    {
        return $this->saveAdminData($request, $id);
    }

    public function store(Request $request)
    {
        return $this->saveAdminData($request);
    }

    private function saveAdminData(Request $request, $id = null, $isEditProfile = false)
    {
        $rules = [
            'user_name' => 'required|string|min:3|max:16|regex:/^\S+$/|unique:admins,user_name,' . $id,
            'full_name' => 'required|string|min:6|max:191',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'email' =>  'required|email|unique:admins,email,' . $id,
        ];
        if (!$isEditProfile) {
            $rules['admin_role_id'] = 'required';
        }
        if (isset($request->password_confirmation) && !isset($request->password)) {
            $rules['password'] = 'required|string|min:6|confirmed';
        } else {
            $rules['password'] = $id ? 'nullable|string|min:6|max:36|confirmed' : 'required|string|min:6|max:36|confirmed';
        }
        $request->validate($rules, [
            'image.max' => 'The image must not be greater than 2MB.',
        ]);

        $row = $id ? Admin::findOrFail($id) : new Admin();
        $row->user_name = $request->user_name;
        $row->full_name = $request->full_name;
        $row->email = $request->email;
        if ($request->remove_file_image && isset($row->image)) {
            Storage::delete($row->image);
            $row->image = null;
        } elseif ($request->image) {
            if (isset($row->image)) {
                Storage::delete($row->image);
            }
            $row->image = $this->FileUploadController->compressAndUploadImage($request->image, 'admins');
        }
        if (!$isEditProfile) {
          
            $row->admin_role_id = $request->admin_role_id;
        }

        if ($request->password) {
            $row->password = Hash::make($request->password);
        }

        $row->save();

        $request->session()->flash('success', $id ? 'Profile edited successfully' : 'Profile added successfully');

        if ($isEditProfile) {
            return redirect(route('admin-profile'));
        } else {
            return redirect(config('cms_config.route_path_prefix') . '/admins');
        }
    }

    public function show($id)
    {
        $row = Admin::findOrFail($id);
        return view('darpersocms::cms.admins.show', compact('row'));
    }

    public function edit($id)
    {
        $row = Admin::findOrFail($id);
        $admin_roles = [];
        $admin_roles_db = AdminRole::get()->toArray();
        foreach ($admin_roles_db as $single_admin_roles_db) {
            $admin_roles[$single_admin_roles_db['id']] = $single_admin_roles_db;
        }
        return view('darpersocms::cms.admins.form', compact('row', 'admin_roles'));
    }

    public function destroy($id)
    {
        $array = explode(',', $id);
        foreach ($array as $id) {
            $row = Admin::find($id);
            if (isset($row->image)) {
                Storage::delete($row->image);
            }
            Admin::destroy($id);
        }
        return redirect(config('cms_config.route_path_prefix') . '/admins')->with('success', 'Record deleted successfully');
    }
}
