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
use App\Models\SiteSetting;
use Darpersodigital\Cms\Models\PostType;

use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;
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
        $settings= SiteSetting::first();
        $postTypes =  PostType::where('show_dashboard',1)->get();
        return view('darpersocms::cms.dashboard.index', compact('settings','postTypes'));
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
            return redirect()->intended(route('admin-dashboard'))->withCookie($cookie);
        }

        $messageBag = new MessageBag([
            'auth' => 'Trouble signing in? Double-check your email and password.',
        ]);

        $errors = new ViewErrorBag();
        $errors->put('default', $messageBag);

        return redirect()->back()->withInput($request->only('email'))->with('errors', $errors);;
    }


    public function logout()
    {
        Auth::guard('admin')->logout();
        return redirect(route('admin-login'));
    }


    public function getPageData(){
        $isLoggedIn = true;
        $globalPostTypes = array();
        $admin =  null;
        $isLoggedIn =Auth::guard('admin')->check();
        $globalPostTypes= PostType::all();
        if($isLoggedIn) {
            $admin = Auth::guard('admin')->user();
        }
       return compact('isLoggedIn','admin','globalPostTypes');
    }

    
    public function showDashboard(){
        $response= $this->getPageData();
 
        return view('darpersocms::admin.dashboard',compact('postTypes'))->with($response);
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
