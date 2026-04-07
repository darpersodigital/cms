<?php

namespace Darpersodigital\Cms\Controllers\seo;
use Illuminate\Http\Request;
use Darpersodigital\Cms\Models\PostType;
use Darpersodigital\Cms\Models\Language;
use Darpersodigital\Cms\Models\Sitemap;
use Darpersodigital\Cms\Models\RobotsTxt;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\File;

use Darpersodigital\Cms\Services\SitemapServices;

class RobotsTXTController extends BaseController
{

    public function index()
    {
        $rows = RobotsTxt::get();
        return view('darpersocms::cms.seo.robots-txt.index', compact('rows'));
    }

    public function create()
    {
        return view('darpersocms::cms.seo.robots-txt.form');
    }

    private function saveRobotsTXTData(Request $request, $id = null)
    {
        $request->validate([
            'content' => ['nullable', 'string'],
        ]);

        $row = $id ? RobotsTxt::findOrFail($id) : new RobotsTxt();
        $row->content = $request->content;
        $row->save();
        $message = $id ? 'Record edited successfully' : 'Record added successfully';
        $request->session()->flash('success', $message);

        $robotsTxtPath = public_path('robots.txt');
        if (File::exists($robotsTxtPath)) {
            File::delete($robotsTxtPath);
        }

             File::put(public_path('robots.txt'), $request->content);
        return redirect(config('cms_config.route_path_prefix') . '/robots-txts');
    }

    public function store(Request $request)
    {
        return $this->saveRobotsTXTData($request);
    }

    public function show($id)
    {
        return $this->edit($id);
    }

    public function edit($id)
    {
        $row =RobotsTxt::findOrFail($id);
        return view('darpersocms::cms.seo.robots-txt.form', compact('row'));
    }

    public function update(Request $request, $id)
    {
        return $this->saveRobotsTXTData($request, $id);
    }

    public function destroy($id)
    {
        $array = explode(',', $id);

        foreach ($array as $id) {
           RobotsTxt::destroy($id);
        }
 
        return redirect(config('cms_config.route_path_prefix') . '/robots-txts')->with('success', 'Record deleted successfully');
    }
}
