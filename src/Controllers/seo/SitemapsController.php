<?php

namespace Darpersodigital\Cms\Controllers\seo;
use Illuminate\Http\Request;
use Darpersodigital\Cms\Models\PostType;
use Darpersodigital\Cms\Models\Language;
use Darpersodigital\Cms\Models\Sitemap;
use Illuminate\Routing\Controller as BaseController;

use Darpersodigital\Cms\Services\SitemapServices;

class SitemapsController extends BaseController
{
    protected SitemapServices $sitemap_services;

    public function __construct(SitemapServices $sitemap_services)
    {
        $this->sitemap_services = $sitemap_services;
    }

    public function index()
    {
        $rows = Sitemap::get();
        return view('darpersocms::cms.seo.sitemaps.index', compact('rows'));
    }

    public function create()
    {
        $postTypes = PostType::where('has_sitemap', 1)->where('single_record', 1)->get();
        $postTypes_multiple = PostType::where('has_sitemap', 1)->where('single_record', 0)->get();
        $languages = Language::get();
        return view('darpersocms::cms.seo.sitemaps.form', compact('postTypes', 'languages', 'postTypes_multiple'));
    }

    private function saveSitemapData(Request $request, $id = null)
    {
        $request->validate([
            'post_type_id' => ['nullable', 'exists:post_types,id', 'required_with:post_type_children_id', 'required_without:url', 'prohibits:locale'],
            'post_type_children_id' => ['nullable', 'exists:post_types,id', 'prohibits:locale'],
            'locale' => ['nullable', 'prohibits:post_type_id,post_type_children_id'],
            'url' => ['nullable', 'string', 'required_without:post_type_id'],
            'priority' => ['required'],
            'change_frequency' => ['required'],
        ]);

        $row = $id ? Sitemap::findOrFail($id) : new Sitemap();
        $row->published = isset($request->published) ? 1 : 0;
        $row->url = $request->url;
        $row->locale = $request->locale;
        $row->priority = $request->priority;
        $row->change_frequency = $request->change_frequency;
        $row->post_type_id = $request->post_type_id;
        $row->post_type_children_id = $request->post_type_children_id;
        $row->save();
        if(isset($request->published))  $this->sitemap_services->generate();
        $message = $id ? 'Record edited successfully' : 'Record added successfully';
        $request->session()->flash('success', $message);
        return redirect(config('cms_config.route_path_prefix') . '/sitemaps');
    }

    public function store(Request $request)
    {
        return $this->saveSitemapData($request);
    }

    public function show($id)
    {
        return $this->edit($id);
    }

    public function edit($id)
    {
        $row = Sitemap::findOrFail($id);
        $postTypes = PostType::where('has_sitemap', 1)->where('single_record', 1)->get();
        $postTypes_multiple = PostType::where('has_sitemap', 1)->where('single_record', 0)->get();
        $languages = Language::get();
        return view('darpersocms::cms.seo.sitemaps.form', compact('row', 'postTypes', 'languages', 'postTypes_multiple'));
    }

    public function update(Request $request, $id)
    {
        return $this->saveSitemapData($request, $id);
    }

    public function destroy($id)
    {
        $array = explode(',', $id);

        foreach ($array as $id) {
            Sitemap::destroy($id);
        }
        $this->sitemap_services->generate();
        return redirect(config('cms_config.route_path_prefix') . '/sitemaps')->with('success', 'Record deleted successfully');
    }
}
