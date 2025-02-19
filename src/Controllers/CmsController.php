<?php

namespace Darpersodigital\Cms\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Cookie;
use Illuminate\Routing\Controller as BaseController;
use League\Flysystem\Util;
use Str;
use Auth;
use Hash;
use File;


use Illuminate\Support\Facades\Storage;
class CmsController extends BaseController
{


    public function redirectToLoginForm(){
        return redirect(route('admin-login'));
    }

    public function showLoginForm() {
        return view('darpersocms::cms/login/index');
    }


    public function showHome() {
        return view('darpersocms::cms.dashboard.index');
    }


    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required',
            'password' => 'required'
        ]);

        if (Auth::guard('admin')->attempt(['email' => $request['email'], 'password' => $request['password']])) {
            $cookieValue = now(); // or \Carbon\Carbon::now() if you prefer Carbon
            $cookie = Cookie::make('loginDate', $cookieValue, 120); // 120 minutes expiration time

            return redirect()->intended(route('admin-home'))->withCookie($cookie);
        }

        return redirect()->back()->withInput($request->only('email'))->with('error', 'Wrong credentials');;
    }


    public function logout()
    {
        Auth::guard('admin')->logout();
        return redirect(route('admin-login'));
    }

    public function showProfile()
    {
       return response()->view('darpersocms::cms.profile.show')->withCookie(cookie('loginDate', now(), 120));
    }


    public function showEditProfile()
    {
        return view('darpersocms::cms.profile.edit');
    }

    public function editProfile(Request $request)
    {
      
        $request->validate([
            'name' => 'required',
            'password' => 'confirmed',
            'image' => 'image|mimes:jpeg,png,jpg,gif,svg',
        ]);

        $admin = Auth::guard('admin')->user();
        $admin->name = $request->name;

        if ($request->password){
            $admin->password = Hash::make($request->password);
            $admin->reset_password_date = now();
        }
        if ($request->remove_file_image) {
            Storage::delete($admin->image);
            $admin->image = '';
        } elseif ($request->image) {
           if(isset($admin->image)) Storage::delete($admin->image);
            $admin->image = $request->file('image')->store('admins','public');
        }

        $admin->save();

        $request->session()->flash('success', 'Profile updated successfully');
        return redirect(route('admin-profile'));
    }


    public function getPageData(){
        $isLoggedIn = true;

        $globalPostTypes = array();
        $admin =  null;
        // return compact('isLoggedIn','admin','globalPostTypes');
    //     return compact('')


        $isLoggedIn =Auth::guard('admin')->check();
        $globalPostTypes= PostType::all();

        if($isLoggedIn) {
            $admin = Auth::guard('admin')->user();
        }
   
       return compact('isLoggedIn','admin','globalPostTypes');
    }

    
    public function showDashboard(){
        $response= $this->getPageData();
        return view('darpersocms::admin.dashboard')->with($response);
    }

    public function getAssets(Request $request)
    {
   
        try {
            if (class_exists(\League\Flysystem\Util::class)) {
                // Flysystem 1.x
                $path = dirname(__DIR__).'/assets/'.\League\Flysystem\Util::normalizeRelativePath(urldecode($request->path));
            } elseif (class_exists(\League\Flysystem\WhitespacePathNormalizer::class)) {
                // Flysystem >= 2.x
                $normalizer = new \League\Flysystem\WhitespacePathNormalizer();
                $path = dirname(__DIR__).'/assets/'. $normalizer->normalizePath(urldecode($request->path));
            }
            
        } catch (\LogicException $e) {
            abort(404);
        }

        if (File::exists($path)) {
            $mime = '';
            if (Str::endsWith($path, '.js')) {
                $mime = 'text/javascript';
            } elseif (Str::endsWith($path, '.css')) {
                $mime = 'text/css';
            } else {
                $mime = File::mimeType($path);
            }
            $response = response(File::get($path), 200, ['Content-Type' => $mime]);
            $response->setSharedMaxAge(31536000);
            $response->setMaxAge(31536000);
            $response->setExpires(new \DateTime('+1 year'));

            return $response;
        }

        return response('', 404);
    }


}
