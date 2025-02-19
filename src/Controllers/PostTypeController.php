<?php

namespace Darpersodigital\Cms\Controllers;

use Illuminate\Http\Request;

use Illuminate\Routing\Controller as BaseController;
use Darpersodigital\Cms\Models\PostType;
use Darpersodigital\Cms\Models\Language;
use Illuminate\Support\Facades\Storage;


use Darpersodigital\Cms\Controllers\ImageController;
use Illuminate\Support\Str;
use Hash;

class PostTypeController extends BaseController
{
    public $appends_to_query;
    private $adminRoleId;
    private $imageController;

    public function __construct(ImageController $imageController)
    {
        
        $this->imageController = $imageController;
        $queryParams = collect([
            'page' => request('page'),
            'per_page' => request('per_page'),
            'custom_search' => request('custom_search'),
            'sort_by' => request('sort_by'),
            'sort_by_direction' => request('sort_by_direction'),
        ])
            ->filter()
            ->toArray();
   
        $this->appends_to_query = $queryParams ? '?' . http_build_query($queryParams) : '';
       
    }

    public function index($route)
    {
     
        $page = PostType::where('route', $route)->firstOrFail();
        $page_fields = json_decode($page['fields'], true);
        $extra_variables = $this->getPostTypeConfigs($page_fields);
        $model = 'App\\Models\\' . $page['model_name'];

        if ($page['single_record']) {
            $row = $model::first();
            if (!$row) {
                return redirect(config('cms_config.route_path_prefix') . '/' . $route . '/create');
            }
            return redirect(config('cms_config.route_path_prefix') . '/' . $route . '/' . $row['id']);
        }

        $sort_direction = request('sort_by_direction') ?: $page['sort_by_direction'] ?: 'desc';
        $order_by_column_relationship = null;
        $sort_by = request('sort_by') ?: $page['order_display'] ?: $page['order_by'] ?: 'pos';

        if (request('sort_by')) {
            $order_by_column_relationship = collect($page_fields)->first(function ($page_field) use ($sort_by) {
                return $page_field['name'] === $sort_by && in_array($page_field['form_field'], ['select', 'select multiple']);
            });
        }

        $rows = $model
            ::select($page->database_table . '.*')
            ->when($sort_by, function ($query) use ($page, $sort_by, $sort_direction) {
                if (isset($page->is_form) && $page->is_form) {
                    return $query->orderBy($page->database_table . '.star', $sort_direction);
                }
                return $query->orderBy($page->database_table . '.' . $sort_by, $sort_direction);
            })
            ->when(request('search_by_relationships'), function ($query) use ($page) {
                // dd(request('search_by_relationships'));
                foreach (request('search_by_relationships') as $relationship) {
                    if ($relationship['constraint'] == 'whereHas' && isset($relationship['value']) && count($relationship['value'])) {
                        $pivot_table = Str::singular($relationship['field_name']) . '_' . Str::singular($page->database_table);
                        $selected_column = $relationship['table_name'] == $page->database_table ? 'other_' . Str::singular($relationship['table']) . '_id' : Str::singular($relationship['table_name']) . '_id';
                        $second_table = uniqid();
                        $second_table = str_replace('e', 'a', $second_table);
                        $query
                            ->join($pivot_table, $pivot_table . '.' . Str::singular($page->database_table) . '_id', $page->database_table . '.id')
                            ->join($relationship['table_name'] . ' as ' . $second_table, $pivot_table . '.' . $selected_column, $second_table . '.id')
                            ->whereRaw($second_table . '.id in (' . implode(',', $relationship['value'][0]) . ')');
                    } elseif (isset($relationship['value'])) {
                        $query = $query->whereIn($relationship['field_name'], $relationship['value']);
                    }
                }
                return $query;
            })
            ->when(request('search'), fn($query) => $this->applySearchFilter($query, $page_fields))
            ->when(
                $order_by_column_relationship,
                function ($query) use ($order_by_column_relationship, $page, $sort_direction) {
                    $query->when(
                        $order_by_column_relationship['form_field'] == 'select',
                        function ($query) use ($order_by_column_relationship, $page, $sort_direction) {
                            $query->leftJoin($order_by_column_relationship['form_field_configs_1'], $order_by_column_relationship['form_field_configs_1'] . '.id', '=', $page['database_table'] . '.' . $order_by_column_relationship['name'])->orderBy($order_by_column_relationship['form_field_configs_1'] . '.' . $order_by_column_relationship['form_field_configs_2'], $sort_direction);
                        },
                        function ($query) {},
                    );
                },
                function ($query) use ($page, $sort_by, $sort_direction) {
                    $query->orderBy($page->database_table . '.' . $sort_by, $sort_direction);
                },
            )
            ->when(
                $page['server_side_pagination'],
                function ($query) {
                    return $query->paginate(request('per_page') ? request('per_page') : 25);
                },
                function ($query) {
                    return $query->get();
                },
            );

        $appends_to_query = $this->appends_to_query;

        if (isset($page->is_form) && $page->is_form) {
            $view = view()->exists('darpersocms::cms/' . $route . '/index') ? 'darpersocms::cms/' . $route . '/index' : 'darpersocms::cms/post-type-form/index';
            return view($view, compact('page', 'page_fields', 'rows', 'extra_variables', 'appends_to_query'));
        } else {
            $view = view()->exists('darpersocms::cms/' . $route . '/index') ? 'darpersocms::cms/' . $route . '/index' : 'darpersocms::cms/post-type/index';
            return view($view, compact('page', 'page_fields', 'rows', 'extra_variables', 'appends_to_query'));
        }
    }

    public function translateOrNew($translatable_fields, $request, $row)
    {
        if (!count($translatable_fields)) {
            return;
        }

        foreach (Language::get() as $language) {
            foreach ($translatable_fields as $field) {
                if ($field['form_field'] == 'select multiple') {
                    continue;
                }

                $fieldName = $field['name'];
                $langSlug = $language->slug;
                $inputValue = $request[$langSlug][$fieldName] ?? null;
                $translation = $row->translateOrNew($langSlug);

                match ($field['form_field']) {
                    'password', 'password with confirmation' => ($translation->{$fieldName} = $inputValue ? Hash::make($inputValue) : null),
                    'checkbox' => ($translation->{$fieldName} = isset($request[$langSlug][$fieldName]) ? 1 : 0),
                    'time' => ($translation->{$fieldName} = $inputValue ? date('H:i', strtotime($inputValue)) : null),
                    'image', 'file' => ($translation->{$fieldName} = $this->handleTranslatedFileUpload($request, $field, $row, $langSlug)),
                    'multiple images' => ($translation->{$fieldName} = $this->handleTranslatedMultipleImagesUpload($request, $field, $row, $langSlug)),
                    default => ($translation->{$fieldName} = $inputValue),
                };
            }
        }

        $row->save();
    }

    private function handleTranslatedFileUpload($request, $field, $row, $langSlug)
    {
        $fieldName = $field['name'];
        $translation = $row->translateOrNew($langSlug);

        if ($request->hasFile("$langSlug.$fieldName")) {
            if (isset($translation->{$fieldName})) {
                Storage::delete($translation->{$fieldName});
            }

            return $field['form_field'] == 'image' ? $this->imageController->compressAndUploadImage($request->file("$langSlug.$fieldName"), $request['route']) : $this->imageController->compressAndUploadFile($request->file("$langSlug.$fieldName"), $request['route']);
        }

        if (isset($request[$langSlug]['remove_file_' . $fieldName]) && $request[$langSlug]['remove_file_' . $fieldName]) {
            Storage::delete($translation->{$fieldName});
            return null;
        }

        return $translation->{$fieldName} ?? null;
    }

    private function handleTranslatedMultipleImagesUpload($request, $field, $row, $langSlug)
    {
        $fieldName = "$langSlug." . $field['name'];
        $currentImages = isset($request['current_' . $fieldName]) && $request['current_' . $fieldName] != null ? json_decode($request['current_' . $fieldName][0] ?? '[]') : [];

        $newImages = $this->imageController->compressAndUploadMultipleImages($request, $field['name'], $request->route, $langSlug);
        $allImages = array_merge($currentImages, $newImages);

        $translation = $row->translateOrNew($langSlug);
        if (isset($translation->{$field['name']})) {
            foreach (json_decode($translation->{$field['name']}) as $val) {
                if (!in_array($val, $currentImages)) {
                    Storage::delete($val);
                }
            }
        }

        return json_encode($allImages);
    }

    public function store(Request $request, $route)
    {
        return $this->saveOrUpdate($request, null, $route);
    }

    public function update(Request $request, $id, $route)
    {
        return $this->saveOrUpdate($request, $id, $route);
    }

    private function saveOrUpdate(Request $request, $id, $route)
    {
        // Fetch Page Info
        $page = PostType::where('route', $route)
            ->when(request()->get('admin')['admin_role_id'], function ($query) use ($id) {
                if ($id) {
                    $query->where('edit', 1);
                }
            })
            ->firstOrFail();

        // Handle single record restriction
        if (!$id && $page->single_record == 1) {
            $model = 'App\\Models\\' . $page['model_name'];
            if ($model::exists()) {
                abort(404);
            }
        }

        if (!$id && !$page->add && $page->single_record == 0) {
            abort(404);
        }

        // Decode fields
        $page_fields = json_decode($page['fields'], true);
        $translatable_fields = json_decode($page['translatable_fields'], true);

        // Get Model
        $model = 'App\\Models\\' . $page['model_name'];
        $row = $id ? $model::findOrFail($id) : new $model();

        // Validate Input
        $validation_rules = array_merge($this->getValidationRules($page_fields, $page['database_table'], $id), $this->getTranslatableValidationRules($translatable_fields, $page['database_table'] . '_translations', $id));
        $request->validate($validation_rules);

        // Process Fields
        $query = [];
        foreach ($page_fields as $field) {
            if ($this->shouldSkipField($field, $id)) {
                continue;
            }
            $query[$field['name']] = $this->handleFieldInput($request, $field, $route, $row);
        }

        // Set Position and Publish Status
        if (!$id) {
            $query['pos'] = $model::max('pos') + 1 ?? 1;
        }
        $query['published'] = isset($request['published']) && in_array($request['published'], [1, '1', 'on']) ? 1 : 0;

        // Save Model
        $row->fill($query)->save();

        // Handle Many-to-Many Relations
        $this->syncSelectMultipleFields($request, $row, $page_fields);

        // Handle Translations
        $this->translateOrNew($translatable_fields, $request, $row);

        // Flash Message & Redirect
        $request->session()->flash('success', $id ? 'Record edited successfully' : 'Record added successfully');
        return redirect(config('cms_config.route_path_prefix') . '/' . $route);
    }

    private function shouldSkipField($field, $isUpdate)
    {
        return $field['form_field'] == 'select multiple' || ($isUpdate && isset($field['can_update']) && !$field['can_update']);
    }

    private function handleFieldInput(Request $request, $field, $route, $row)
    {
        $fieldName = $field['name'];
        $formField = $field['form_field'];

        return match ($formField) {
            'password', 'password with confirmation' => $request[$fieldName] ? Hash::make($request[$fieldName]) : null,
            'checkbox' => isset($request[$fieldName]) ? 1 : 0,
            'time' => date('H:i', strtotime($request[$fieldName] ?? '')),
            'image', 'file' => $this->handleFileUpload($request, $field, $route, $row),
            'multiple images' => $this->handleMultipleImagesUpload($request, $field, $route, $row),
            default => $request[$fieldName] ?? null,
        };
    }

    private function handleFileUpload(Request $request, $field, $route, $row)
    {
        $fieldName = $field['name'];
        if ($request->hasFile($fieldName)) {
            if (isset($row[$fieldName])) {
                Storage::delete($row[$fieldName]);
            }
            return $field['form_field'] == 'image' ? $this->imageController->compressAndUploadImage($request->file($fieldName), $route) : $this->imageController->compressAndUploadFile($request->file($fieldName), $route);
        }

        if ($request->has("remove_file_$fieldName") && $request["remove_file_$fieldName"] != null) {
            if (isset($row[$fieldName])) {
                Storage::delete($row[$fieldName]);
            }
            return null;
        }
        return $row[$fieldName] ?? null;
    }

    private function handleMultipleImagesUpload(Request $request, $field, $route, $row)
    {
        $fieldName = $field['name'];
        $currentImages = json_decode($request["current_$fieldName"][0] ?? '[]');
        $newImages = $this->imageController->compressAndUploadMultipleImages($request, $fieldName, $route);
        $allImages = array_merge($currentImages, $newImages);

        if (isset($row[$fieldName])) {
            foreach (json_decode($row[$fieldName]) as $existingImage) {
                if (!in_array($existingImage, $currentImages)) {
                    Storage::delete($existingImage);
                }
            }
        }
        return json_encode($allImages);
    }

    private function syncSelectMultipleFields(Request $request, $row, $page_fields)
    {
        foreach ($page_fields as $field) {
            if ($field['form_field'] == 'select multiple') {
                $syncValues = [];
                if (!empty($request[$field['name']])) {
                    foreach ($request[$field['name']] as $syncId) {
                        $syncValues[$syncId] = ['pos' => $request['pos_' . $field['name']][$syncId] ?? null];
                    }
                    try {
                        $row->{str_replace('_id', '', $field['name'])}()->sync($syncValues);
                    } catch (\Throwable $th) {
                        $row->{str_replace('_id', '', $field['name'])}()->sync($request[$field['name']]);
                    }
                } else {
                    $row->{str_replace('_id', '', $field['name'])}()->sync([]);
                }
            }
        }
    }

    private function getTranslatableValidationRules($translatable_fields, $table, $id)
    {
        $rules = $this->getValidationRules($translatable_fields, $table, $id);
        $result = [];
        foreach ($rules as $field => $rule) {
            foreach (Language::get() as $language) {
                $result[$language->slug . '.' . $field] = $rule;
            }
        }
        return $result;
    }

    public function destroy($id, $route)
    {
        $appends_to_query = $this->appends_to_query;
  
        if ($route=="" ) return redirect()->back()->with('error', 'No record selected');
        $page = PostType::where('route', $route)
            ->when(request()->get('admin')['admin_role_id'], function ($query) {
                $query->where('delete', 1);
            })
            ->firstOrFail();
        $model = 'App\\Models\\' . $page['model_name'];
        $translatedModel = 'App\\Models\\' . $page['model_name'] . 'Translation';
        $fields = json_decode($page->fields);
        $translatable_fields = json_decode($page->translatable_fields);
        $array = explode(',', $id);

        if (!count($array) ) return redirect(config('cms_config.route_path_prefix') . '/' . $route . $appends_to_query)->with('error', 'No record selected');

        foreach ($array as $id) {
            $record = $model::find($id);
            foreach ($fields as $field) {
                if ($field->form_field == 'file' || $field->form_field == 'image') {
                    if (isset($record[$field->name])) {
                        Storage::delete($record[$field->name]);
                    }
                } elseif ($field->form_field == 'multiple images') {
                    foreach (json_decode($record[$field->name]) as $val) {
                        Storage::delete($val);
                    }
                }
            }
            if (count($translatable_fields) > 0) {
                $translated_records = $translatedModel::where(strtolower($page['model_name']) . '_id', $record->id)->get();
                foreach ($translatable_fields as $field) {
                    if ($field->form_field == 'file' || $field->form_field == 'image') {
                        foreach ($translated_records as $translated_record) {
                            if (isset($translated_record[$field->name])) {
                                Storage::delete($translated_record[$field->name]);
                            }
                        }
                    } elseif ($field->form_field == 'multiple images') {
                        foreach ($translated_records as $translated_record) {
                            foreach (json_decode($translated_record[$field->name]) as $val) {
                                Storage::delete($val);
                            }
                        }
                    }
                }
            }

            if ($record) {
                $record->delete();
            }
        }
       
        return redirect(config('cms_config.route_path_prefix') . '/' . $route . $appends_to_query)->with('success', 'Record deleted successfully');
    }
    public function order($route)
    {
        $page = PostType::where('route', $route)->firstOrFail();
        $page_fields = json_decode($page['fields'], true);
        $page_translatable_fields = json_decode($page['translatable_fields'], true);

        $model = 'App\\Models\\' . $page['model_name'];
        $sort_direction = $page['sort_by_direction'] ?: 'desc';
        $rows = $model::orderBy('pos', $sort_direction)->get();
        $view = view()->exists('cms/' . $route . '/order') ? 'cms/' . $route . '/order' : 'cms/post-type/order';
        return view($view, compact('page', 'page_fields', 'page_translatable_fields', 'rows'));
    }

    public function saveOrder(Request $request, $route)
    {
        $page = PostType::where('route', $route)->firstOrFail();
        $model = 'App\\Models\\' . $page['model_name'];
        foreach ($request['pos'] as $id => $pos) {
            $row = $model::findOrFail($id);
            $row->pos = $pos;
            $row->save();
        }
        return redirect(config('cms_config.route_path_prefix') . '/' . $route)->with('success', 'Records ordered successfully');
    }
    private function applySearchFilter($query, $page_fields)
    {
        $excludedFields = ['password', 'password with confirmation', 'select', 'select multiple', 'checkbox', 'image', 'multiple images', 'file'];

        foreach ($page_fields as $field) {
            if (in_array($field['form_field'], $excludedFields)) {
                continue;
            }
            $query->orWhere($field['name'], 'like', '%' . request('search') . '%');
        }
        return $query;
    }

    public function getValidationRules($page_fields, $database_table, $id)
    {
        $validation_rules = [];
        $excludeRequiredTypes = ['image', 'file', 'password with confirmation', 'checkbox'];
        $nullableFieldTypes = ['image', 'file'];
        $simpleValidationTypes = [
            'image' => 'image|',
            'password with confirmation' => 'confirmed|',
            'number' => 'numeric|',
            'multiple images' => 'array|',
            'email' => 'email|',
        ];

        foreach ($page_fields as $field) {
            // Skip fields based on specific conditions
            if ($field['form_field'] === 'slug' || (isset($field['can_update']) && $field['can_update'] == 0) || ($field['form_field'] === 'slug' && $field['form_field_configs_2'] == 0 && !$field['unique'])) {
                continue;
            }
            $fieldName = $field['name'];
            $validation_rules[$fieldName] = '';

            if (isset($field['additional_validations']) && $field['additional_validations'] != '' && $field['form_field'] !== 'multiple images') {
                $validation_rules[$fieldName] = $field['additional_validations'] . '|';
            }

            if ($field['form_field'] == 'multiple images') {
                $validation_rules[$fieldName . '.*'] = $field['additional_validations'] ?? '';
            }
            // Add validation rules based on field properties
            if (!$field['nullable'] && !in_array($field['form_field'], $excludeRequiredTypes, true)) {
                $validation_rules[$fieldName] .= 'required|';
            }

            if (!$field['nullable'] && in_array($field['form_field'], $nullableFieldTypes, true)) {
                $validation_rules[$fieldName] .= 'required_with:remove_file_' . $fieldName . '|';
            }

            if (isset($id) && !empty($field['unique'])) {
                $validation_rules[$fieldName] .= 'unique:' . $database_table . ',' . $fieldName . ',' . $id . '|';
            }

            // Simple validations based on field type
            if (isset($simpleValidationTypes[$field['form_field']])) {
                $validation_rules[$fieldName] .= $simpleValidationTypes[$field['form_field']];
            }

            if ($field['form_field'] === 'number' && $field['nullable']) {
                $validation_rules[$fieldName] .= 'nullable|';
            }

            if ($field['migration_type'] === 'string' && !in_array($field['form_field'], ['number', 'image', 'file'], true)) {
                $validation_rules[$fieldName] .= 'max:191|';
            }

            // This ensures that the string does not end with an unnecessary |
            $validation_rules[$fieldName] = rtrim($validation_rules[$fieldName], '|');
        }

        return $validation_rules;
    }

    public function show($id, $route)
    {
        $page = PostType::where('route', $route)->where('show', 1)->firstOrFail();
        $page_fields = json_decode($page['fields'], true);
        $translatable_fields = json_decode($page['translatable_fields'], true);
        $model = 'App\\Models\\' . $page['model_name'];
        $row = $model::findOrFail($id);
        $languages= Language::get();

        if (isset($page->is_form) && $page->is_form) {
            $row->read = 1;
            $row->save();
            $view = view()->exists('darpersocms::cms.' . $route . '.show') ? 'darpersocms::cms.' . $route . '.show' : 'darpersocms::cms.post-type-form.show';
            return view($view, compact('page', 'page_fields','languages' ,'translatable_fields', 'row'));
        } else {
            $view = view()->exists('darpersocms::cms.' . $route . '.show') ? 'darpersocms::cms.' . $route . '.show' : 'darpersocms::cms.post-type.show';
            return view($view, compact('page', 'page_fields','languages', 'translatable_fields', 'row'));
        }
    }

    public function create($route)
    {
        $page = PostType::where('route', $route)->when(request()->get('admin')['admin_role_id'])->firstOrFail();
        $languages= Language::get();
        if (isset($page->single_record) && $page->single_record == 1) {
            $model = 'App\\Models\\' . $page['model_name'];
            $rows = $model::get();
            if (count($rows) >= 1) {
                abort('404');
            }
        }
        if (!$page->add == 1 && $page->single_record == 0) {
            abort('404');
        }

        $page = $page;
        $page_fields = json_decode($page['fields'], true) ?? [];
        $page_translatable_fields = json_decode($page['translatable_fields'], true) ?? [];
        $extra_variables = $this->getPostTypeConfigs($page_fields);
        $view = view()->exists('darpersocms::cms.' . $route . '.form') ? 'darpersocms::cms.' . $route . '.form' : 'darpersocms::cms.post-type.form';
        return view($view, compact('page', 'page_fields','languages', 'page_translatable_fields', 'extra_variables'));
    }

    public function edit($id, $route)
    {
        $page = PostType::where('route', $route)->when(request()->get('admin')['admin_role_id'], fn($query) => $query->where('edit', 1))->firstOrFail();
        $languages= Language::get();
        $page_fields = json_decode($page['fields'], true) ?? [];
        $page_translatable_fields = json_decode($page['translatable_fields'], true) ?? [];

        $extra_variables = $this->getPostTypeConfigs($page_fields);

        $model = 'App\\Models\\' . $page['model_name'];

        if (!class_exists($model)) {
            abort(404);
        }
        $row = $model::findOrFail($id);

        $appends_to_query = $this->appends_to_query;

        // check for custom edit cms
        $view = view()->exists('darpersocms::cms.' . $route . '.form') ? 'darpersocms::cms.' . $route . '.form' : 'darpersocms::cms.post-type.form';
        return view($view, compact('page','languages' ,'page_fields', 'page_translatable_fields', 'row', 'extra_variables', 'appends_to_query'));
    }

    public function getPostTypeConfigs($page_fields)
    {
        $extra_variables = [];
        foreach ($page_fields as $field) {
            if (in_array($field['form_field'], ['select', 'select multiple'])) {
                $databaseTable = $field['form_field_configs_1'];

                $extra_page = PostType::where('database_table', $databaseTable)->firstOrFail();
                $extra_model = 'App\\Models\\' . $extra_page->model_name;

                if (class_exists($extra_model)) {
                    $extra_variables[$databaseTable] = $extra_model::get();
                }
            }
        }
        return $extra_variables;
    }
}
