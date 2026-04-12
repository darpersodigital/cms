<?php

namespace Darpersodigital\Cms\Controllers;
use Illuminate\Http\Request;
use Darpersodigital\Cms\Models\AdminRolePermission;
use Darpersodigital\Cms\Models\AdminRole;
use Darpersodigital\Cms\Models\PostType;
use Darpersodigital\Cms\Models\Language;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Storage;
use Darpersodigital\Cms\Services\SitemapServices;
use Darpersodigital\Cms\Controllers\PostTypeController;

class LanguagesController extends BaseController
{
    public function index()
    {
        $rows = Language::get();
        return view('darpersocms::cms.languages.index', compact('rows'));
    }

    protected SitemapServices $sitemap_services;
    protected PostTypeController $postTypeController;

    public function __construct(SitemapServices $sitemap_services, PostTypeController $postTypeController)
    {
        $this->sitemap_services = $sitemap_services;
        $this->postTypeController = $postTypeController;
    }

    public function create()
    {
        return view('darpersocms::cms.languages.form');
    }

    private function saveLanguageData(Request $request, $id = null)
    {
        $request->validate([
            'title' => 'required|min:2|max:16',
            'slug' => 'required|unique:languages' . ($id ? ',slug,' . $id : ''),
            'direction' => 'required',
        ]);
        $row = $id ? Language::findOrFail($id) : new Language();
        $row->title = $request->title;
        $row->published = isset($request['published']) && in_array($request['published'], [1, '1', 'on']) ? 1 : 0;

        $row->slug = $request->slug;
        $row->direction = $request->direction;
        $row->save();
        $message = $id ? 'Record edited successfully' : 'Record added successfully';
        $request->session()->flash('success', $message);
        $this->sitemap_services->generate();
        return redirect(config('cms_config.route_path_prefix') . '/languages');
    }

    public function store(Request $request)
    {
        return $this->saveLanguageData($request);
    }

    public function show($id)
    {
        return $this->edit($id);
    }

    public function edit($id)
    {
        $row = Language::findOrFail($id);
        return view('darpersocms::cms.languages.form', compact('row'));
    }

    public function update(Request $request, $id)
    {
        return $this->saveLanguageData($request, $id);
    }

    public function destroy($id)
    {
        $array = explode(',', $id);
        // Prevent delete when there is only one language
        if (count($array) == Language::count()) {
            return redirect(config('cms_config.route_path_prefix') . '/languages')->with('error', 'Record can not be deleted');
        }
        $postTypes = PostType::where('custom_page', 0)->where('translatable_fields', '!=', '[]')->get();
        $targetFields = ['image', 'image with alt', 'multiple images with alt', 'file', 'multiple images', 'multiple files', 'video', 'multiple videos'];
        foreach ($array as $id) {
            $locale = Language::findOrFail($id)->slug;

            foreach ($postTypes as $postType) {
                $translatable_fields = json_decode($postType['translatable_fields'], true);
                $translation_table = $postType['database_table'] . '_translations';
                $translation_model = $postType['model_name'] . 'Translation';
                $model = 'App\\Models\\' . $translation_model;
                $rows = $model::where('locale', $locale)->get();
                $modelUploadRows = array_map(
                    fn($field) => [
                        'name' => $field['name'],
                        'form_field' => $field['form_field'],
                    ],
                    array_filter($translatable_fields, function ($field) use ($targetFields) {
                        return in_array($field['form_field'], $targetFields, true);
                    }),
                );
                if (count($rows) > 0 && count($modelUploadRows) > 0) {
                    foreach ($rows as $row) {
                        foreach ($modelUploadRows as $uploadFieldName) {
                            if (isset($row[$uploadFieldName['name']])) {
                                // Check for multiple file cases
                                if ($uploadFieldName['form_field'] == 'multiple files' || $uploadFieldName['form_field'] == 'multiple videos' || $uploadFieldName['form_field'] == 'multiple images' || $uploadFieldName['form_field'] == 'multiple images with alt') {
                                    $multiple_upload_list = json_decode($row[$uploadFieldName['name']], true);
                                    foreach ($multiple_upload_list as $file) {
                                        if ($uploadFieldName['form_field'] == 'multiple images with alt') {
                                            Storage::delete($file['file']);
                                        } elseif (isset($file)) {
                                            Storage::delete($file);
                                        }
                                    }
                                } elseif (isset($row[$uploadFieldName['name']])) {
                                    if ($uploadFieldName['form_field'] == 'image with alt') {
                                        if (json_decode($row[$uploadFieldName['name']])) {
                                            Storage::delete(json_decode($row[$uploadFieldName['name']])->file);
                                        }
                                    } else {
                                        Storage::delete($row[$uploadFieldName['name']]);
                                    }
                                }
                            }
                        }
                    }
                    $model::where('locale', $locale)->delete();
                } elseif (count($rows) > 0) {
                    $model::where('locale', $locale)->delete();
                }
            }

            Language::destroy($id);
        }
        $this->sitemap_services->generate();
        return redirect(config('cms_config.route_path_prefix') . '/languages')->with('success', 'Record deleted successfully');
    }
}
