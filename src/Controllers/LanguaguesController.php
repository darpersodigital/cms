<?php

namespace Darpersodigital\Cms\Controllers;
use Illuminate\Http\Request;
use Darpersodigital\Cms\Models\AdminRolePermission;
use Darpersodigital\Cms\Models\AdminRole;
use Darpersodigital\Cms\Models\PostType;
use Darpersodigital\Cms\Models\Language;
use Illuminate\Routing\Controller as BaseController;

class LanguaguesController extends BaseController
{
    public function index() {
        $rows = Language::get();
        return view('darpersocms::cms.languages.index', compact('rows'));
    }

    public function create() {
        return view('darpersocms::cms.languages.form');
    }

     private function saveLanguageData(Request $request, $id = null){
        $request->validate([
           'title' => 'required',
            'slug' => 'required|unique:languages'. ($id ? ',slug,' . $id : ''),
            'direction' => 'required',
        ]);
        $row = $id ? Language::findOrFail($id) : new Language();
        $row->title = $request->title;
        $row->slug = $request->slug;
        $row->direction = $request->direction;
        $row->save();
        $message = $id ? 'Record edited successfully' : 'Record added successfully';
        $request->session()->flash('success', $message);
        return redirect(config('cms_config.route_path_prefix') . '/languages');
    }

    public function store(Request $request) {
        return $this->saveLanguageData($request);
    }

    public function show($id) {
        return $this->edit($id);
    }

    public function edit($id) {
        $row = Language::findOrFail($id);
        return view('darpersocms::cms.languages.form', compact('row'));
    }

    public function update(Request $request, $id) {
        return $this->saveLanguageData($request,$id);
    }

    public function destroy($id) {
        $array = explode(',', $id);
        // Prevent delete when there is only one language
        if (count($array) == Language::count()) return redirect(config('cms_config.route_path_prefix') . '/languages')->with('error', 'Record can not be deleted');
        foreach ($array as $id) Language::destroy($id);
        return redirect(config('cms_config.route_path_prefix') . '/languages')->with('success', 'Record deleted successfully');
    }
}