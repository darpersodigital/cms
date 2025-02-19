<?php

namespace Darpersodigital\Cms\Middlewares;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;


use Darpersodigital\Cms\Models\Admin;
use Darpersodigital\Cms\Models\AdminRole;
use Darpersodigital\Cms\Models\AdminRolePermission;
use Darpersodigital\Cms\Models\PostType;


// use App\Models\CmsLog;
use Route;
use Auth;
use View;
use Str;


class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        
        $routeName = $request->route()->getName();
        $isProfileRoute = in_array($routeName, ['admin-profile', 'admin-profile-edit']);
        $hasSuccess = session()->has('success');
        $loginDateCookie = $request->cookie('loginDate');
        $adminUser = Auth::guard('admin')->user();

        if (
            ($routeName !== 'admin-profile' && $routeName !== 'admin-profile-edit') ||
            ($isProfileRoute && !$hasSuccess)
        ) {
            if ($loginDateCookie && $adminUser && $adminUser->reset_password_date) {
                if ($adminUser->reset_password_date > $loginDateCookie) {
                    Auth::guard('admin')->logout();
                }
            }
        }

        // Get admin
        $admin = Auth::guard('admin')->user();

        // Check if login page
        if (Route::currentRouteName() == 'admin-login') {
            if ($admin) return redirect(route('admin-home'));
            return $next($request);
        }

        if (!$admin) return redirect()->guest(route('admin-login'));
        $admin = $admin->toArray();

    
        $post_types = PostType::orderBy('pos')->get()->keyBy('route')->toArray();

         // If the admin does not have a role ID, they are considered a super admin; otherwise, they will be assigned admin permissions.
        if ($admin['admin_role_id'] && $admin['admin_role_id']!=1) {
            // Get role permissions in one go and map by post_type_id
            $admin_role_permissions_db = AdminRolePermission::where('admin_role_id', $admin['admin_role_id'])->get()->keyBy('post_type_id');

            foreach ($post_types as $key => $post_type) {
                // Set default permissions (if no matching permission found)
                $permissions = [
                    'browse' => 0,
                    'read' => 0,
                    'edit' => 0,
                    'add' => 0,
                    'delete' => 0,
                ];

                // Only process if the post type is not hidden
                if (!$post_type['hidden']) {
                    // Check if permissions exist for the post type
                    if (isset($admin_role_permissions_db[$post_type['id']])) {
                        $permission = $admin_role_permissions_db[$post_type['id']];
                        $permissions = [
                            'browse' => $permission->browse,
                            'read' => $permission->read,
                            'edit' => $permission->edit,
                            'add' => $permission->add,
                            'delete' => $permission->delete,
                        ];
                    }
                }

                // Assign permissions to the post type
                $post_types[$key]['permissions'] = $permissions;
            }
        } else {
            // Super admin gets all permissions
            foreach ($post_types as $key => $post_type) {
                $post_types[$key]['permissions'] = [
                    'browse' => 1,
                    'read' => 1,
                    'edit' => 1,
                    'add' => 1,
                    'delete' => 1,
                ];
            }
        }

     
        // Group post Types by parent
        $post_types_grouped = [];
        $last_page_added = null;
        foreach ($post_types as $post_type) {
            if ($post_type['permissions']['browse']) {
                 // Check if the current post type has the same parent as the last added one
                if ($last_page_added && $last_page_added['parent_icon'] == $post_type['parent_icon'] && $last_page_added['parent_title'] == $post_type['parent_title']) {
                     // Append the current post type to the last group
                    $post_types_grouped[count($post_types_grouped) - 1]['pages'][] = $post_type;
                } else {
                     // Create a new group for the post type
                    $post_types_grouped[] = [
                        'icon' => $post_type['parent_icon'],
                        'title' => $post_type['parent_title'],
                        'pages' => [$post_type],
                    ];
                }
                  // Update the last added post type
                $last_page_added = $post_type;
            }
        }
      
        // Save $admin in request
        $admin['post_types'] = $post_types;
        $admin['post_types_grouped'] = $post_types_grouped;
        $request->attributes->add(compact('admin'));

        // If admin have role id then he is not a super then, therefore we should check the permissions
        if ($admin['admin_role_id'] && $admin['admin_role_id']!=1) { 
            $route_path_prefix = config('cms_config.route_path_prefix');
            $requested_path = ltrim(substr(request()->path(), strlen($route_path_prefix)), '/');
            $request_path_array = explode('/', $requested_path);
            $request_path_array[0] = $request_path_array[0] ?? 'home';  // Set default 'home' if the first element is missing or empty

            $route = $request_path_array[0];
            if (!in_array($route, ['home', 'profile', 'logout'])) {
                if (!isset($admin['post_types'][$route])) abort(403);
                $admin_page_permission = $admin['post_types'][$route]['permissions'];
                // Define the permission needed for the current request method
                $requiredPermissions = null;
                switch ($request->method()) {
                    case 'POST':
                        $requiredPermissions = 'add';
                        break;
                    case 'DELETE':
                        $requiredPermissions = 'delete';
                        break;
                    case 'PUT':
                        $requiredPermissions = 'edit';
                        break;
                    default:
                        if (!isset($request_path_array[1])) {
                            $requiredPermissions = 'browse'; 
                        } elseif ($request_path_array[1] == 'create') {
                            $requiredPermissions = 'add'; 
                        } elseif ($request_path_array[1] == 'order') {
                            $requiredPermissions = 'edit'; 
                        } elseif (isset($request_path_array[2]) && $request_path_array[2] == 'edit') {
                            $requiredPermissions = 'edit'; 
                        } else {
                            $requiredPermissions = 'read';
                        }
                }

                if (!$requiredPermissions || !$admin_page_permission[$requiredPermissions]) {
                    abort(403);
                }
            }

        }

        return $next($request);
    }
}