<?php

namespace Darpersodigital\Cms\Controllers;

use Illuminate\Http\Request;

use Illuminate\Routing\Controller as BaseController;

use Illuminate\Http\RedirectResponse;
use Darpersodigital\Cms\Models\PostType;
use Darpersodigital\Cms\Models\AdminRolePermission;
use Artisan;
use Illuminate\Validation\ValidationException;
use Storage;
use Str;
use Schema;


use Darpersodigital\Cms\Controllers\DatabaseController;

class PostTypesController extends BaseController
{
    public $migration_types,$form_fields;
    private $db_controller;

    public function __construct(DatabaseController $db_controller){

        $config = include(__DIR__ . '/constants/constants.php');
        $this->db_controller = $db_controller;
        $this->migration_types = $config['migration_types'];
        $this->form_fields =  $config['form_fields'];
    }

    public function index() {
        $rows = PostType::orderBy('pos','asc')->get();
        return view('darpersocms::cms.post-types.index', compact('rows'));
    }

    public function create() {
        return view('darpersocms::cms.post-types.create', [
            'migration_types' => $this->migration_types,
            'form_fields' => $this->form_fields,
        ]);
    }

    public function createCustom() {
        return view('darpersocms::cms.post-types.create-custom');
    }

    public function edit($id) {
        $post_type = PostType::where('custom_page', 0)->findOrFail($id);
        $migration_types = $this->migration_types;
        $form_fields = $this->form_fields;
        return view('darpersocms::cms/post-types/create', compact(
            'post_type',
            'migration_types',
            'form_fields',
        ));
    }

    public function editCustom($id) {
        $post_type = PostType::where('custom_page', 1)->findOrFail($id);
        return view('darpersocms::cms/post-types/create-custom', compact(
            'post_type',
        ));
    }

    private function storeOrUpdateCustom (Request $request,$id) {
        $request->validate([
            'route' => 'required|unique:post_types' . ($id ? ',route,'.$id : ''),
        ]);
        $post_type = $id ? PostType::where('custom_page', 1)->findOrFail($id) : new PostType;
        $post_type->icon = $request->icon;
        $post_type->display_name_plural = $request->display_name_plural;
        $post_type->route = $request->route;

        if(!$id){
            $post_type->pos = PostType::max('pos') + 1;
            $post_type->custom_page = 1;
        }else  if (!$request->display_name_plural) {
            $post_type->parent_title = null;
            $post_type->parent_icon = null;
        }

        $post_type->save();
        return redirect(config('cms_config.route_path_prefix') . '/post-types')->with('success', 'Page '. ($id ? 'edited' :'added' ) . ' successfully');

    }

    public function storeCustom(Request $request){
        return $this->storeOrUpdateCustom($request,null);
    }

    public function updateCustom(Request $request, $id) {
        return $this->storeOrUpdateCustom($request, $id);
    }
    
    // CLEAN
    private function validatePostType(Request $request, $id = null){
        $rules = [
            'database_table' => 'required|unique:post_types' . ($id ? ',database_table,' . $id : ''),
            'model_name' => 'required|unique:post_types' . ($id ? ',model_name,' . $id : ''),
            'display_name' => 'required',
            'display_name_plural' => 'required',
            'name' => 'required|array',
            'name.*' => 'required',
            'form_field' => 'required|array',
            'form_field.*' => 'required',
            'old_form_field_configs_1' => 'required|array',
            'form_field_configs_1' => 'required|array',
            'old_additional_validations' => 'required|array',
            'additional_validations' => 'required|array',
            'form_field_configs_2' => 'required|array',
            'hide_table' => 'required|array',
            'can_create' => 'required|array',
            'can_read' => 'required|array',
            'can_update' => 'required|array',
            'nullable' => 'required|array',
            'unique' => 'required|array',
        ];

        // Additional validation rules for new entries
        if (!$id) {
            $rules = array_merge($rules, [
                'old_name' => 'array',
                'migration_type' => 'array',
                'translatable_name' => 'array',
                'translatable_name.*' => 'required',
                'translatable_old_name' => 'array',
                'translatable_form_field' => 'array',
                'translatable_form_field.*' => 'required',
                'translatable_migration_type' => 'array',
                'translatable_migration_type.*' => 'required',
                'translatable_nullable' => 'array',
            ]);
        }

        $request->validate($rules);
    }


    public function store(Request $request){
        return $this->storeOrUpdate($request,null);
    }
    public function update(Request $request, $id){
        return $this->storeOrUpdate($request,$id);
    }

    private function storeOrUpdate(Request $rq,$id) {
        $this->validatePostType($rq,$id);
        $request = $rq;
        $post_type = $id ? PostType::where('custom_page', 0)->findOrFail($id) : new PostType;
        if (isset($rq->with_seo) && $rq->with_seo) {
            $seoFields = ["seo_image", "seo_title", "seo_description", "seo_keywords"];
            $seoMigrationTypes = ["text", "string", "string", "string"];
            $seoFormFields = ["image", "text", "text", "text"];
            $seoAdditionalValidations = ["max:1000", "max:60", "max:160", ""];
            $defaultNulls = array_fill(0, 4, null);
            $defaultZeros = array_fill(0, 4, "0");
            $defaultOnes = array_fill(0, 4, "1");
            $emptyStrings = array_fill(0, 4, "");
            $fieldsToMerge = [
                'translatable_name' => $seoFields,
                'translatable_migration_type' => $seoMigrationTypes,
                'translatable_form_field' => $seoFormFields,
                'translatable_form_field_configs_1' => $defaultNulls,
                'translatable_form_field_configs_2' => $defaultNulls,
                'translatable_additional_validations' => $seoAdditionalValidations,
                'translatable_description' => $emptyStrings,
                'translatable_hide_table' => $defaultZeros,
                'translatable_can_create' => $defaultOnes,
                'translatable_can_read' => $defaultOnes,
                'translatable_can_update' => $defaultOnes,
                'translatable_nullable' => $defaultOnes,
            ];
            foreach ($fieldsToMerge as $field => $values) {
                $coppied_array = $rq->input($field, []);
                $request->merge([$field => array_merge($coppied_array, $values)]);
            }
        }

        $fields = $this->beautifyFields($request,false);
        if (!is_array($fields)) return $fields;

        $translatable_fields = $this->beautifyFields($request,true);
        if (!is_array($translatable_fields)) return $translatable_fields;

        if (count($request['old_name']) != count($request['name']) && $id) abort(500);

        if(!$id) {
            $this->db_controller->createDatabase($request);
        }else {
            $edit_db_res = $this->db_controller->editDatabase($request, $post_type);
            if (!is_null($edit_db_res)) return $edit_db_res;
            if ($post_type->database_table != $request->database_table) $this->db_controller->deleteModel($post_type);
        }
        $this->db_controller->createModel($request);

        $post_type->icon = $request->icon;
        $post_type->display_name = $request->display_name;
        $post_type->display_name_plural = $request->display_name_plural;
        $post_type->database_table = $request->database_table;
        $post_type->route = Str::slug($request->database_table);
        $post_type->model_name = $request->model_name;
        $post_type->order_display = $request->order_display;
        $post_type->sort_by = $request->sort_by;
        $post_type->sort_by_direction = $request->sort_by_direction;
        $post_type->fields = json_encode($fields);
        $post_type->translatable_fields = json_encode($translatable_fields);
        $post_type->add = isset($request->single_record) ? 0 : (isset($request->add) ? 1 : 0);
        $post_type->edit = isset($request->edit) ? 1 : 0;
        $post_type->delete = isset($request->single_record) ? 0 : (isset($request->delete) ? 1 : 0);
        $post_type->show = isset($request->show) ? 1 : 0;
        $post_type->single_record = isset($request->single_record) ? 1 : 0;
        $post_type->server_side_pagination = isset($request->server_side_pagination) ? 1 : 0;
        $post_type->with_export = isset($request->with_export) ? 1 : 0;
        $post_type->hidden = isset($request->hidden) ? 1 : 0;
        $post_type->is_form = isset($request->is_form) ? 1 : 0;
        if($id) $post_type->pos = PostType::max('pos') + 1;
        $post_type->save();
        if(!$id){ 
            AdminRolePermission::create([
                'admin_role_id' => 1,
                'post_type_id' => $post_type->id,
                'browse' => 1,
                'read' => 1,
                'edit' => 1,
                'add' => 1,
                'delete' => 1,
            ]);
        }
        $request->session()->flash('success', 'Page '.  ($id ? 'edited' :'added') .' successfully');
        return redirect(config('cms_config.route_path_prefix') . '/' . $post_type->route);
    }



    public function destroy($id)
    {
        $array = explode(',', $id);

        if (!count($array)) return redirect(config('cms_config.route_path_prefix') . '/post-types')->with('error', 'No record selected');
        foreach ($array as $id) $this->deletePage($id);
        return redirect(config('cms_config.route_path_prefix') . '/post-types')->with('success', 'Record deleted successfully');
    }

    public function deletePage($id) {
        $post_type = PostType::whereNotIn('id', [1, 2, 3, 4])->findorfail($id);
        $this->db_controller->deleteDatabase($post_type);
        $this->db_controller->deleteModel($post_type);
        PostType::destroy($id);
    }

    public function order(){
        return view('darpersocms::cms.post-types.order');
    }

    public function saveOrder(Request $request)
    {
        $pos = 1;
        foreach ($request->id as $key => $id) {
            if ($id) {
                $row = PostType::findOrFail($id);
                $row->parent_title = $request->parent_title[$key];
                $row->parent_icon = $request->parent_icon[$key];
                $row->pos = $pos;
                $row->save();
                $pos++;
            }
        }

        return redirect(config('cms_config.route_path_prefix') . '/post-types')->with('success', 'Records ordered successfully');
    }

    public function icons(){
       $config = include(__DIR__ . '/constants/constants.php');
        return view('darpersocms::cms.general.icons', compact('config'));
    }


    public function beautifyFields($request,$isTranslation) {
        $fields = [];
        $name =$isTranslation ? $request['translatable_name'] : $request['name'];
        $form_field = $isTranslation ? $request['translatable_form_field'] : $request['form_field'];
        $migration_type = $isTranslation ? $request['translatable_migration_type'] : $request['migration_type'];


       if(isset($name)){
        for ($i = 0; $i < count($name); $i++) {
            // Check if field is unique
            foreach ($fields as $field) if ($field['name'] ==  $name[$i]) throw ValidationException::withMessages(['Column "' .  $name[$i] . '" already exists']);

            // Check if migration type does not exist
            if ($form_field[$i] != 'select multiple') {
                if (!$migration_type[$i]) throw ValidationException::withMessages(['The ' . ($isTranslation ? 'translatable_':'') .'migration_type.' . $i . ' field is required.']);
                elseif (!in_array($migration_type[$i], $this->migration_types)) throw ValidationException::withMessages(['The ' . ($isTranslation ? 'translatable_':'') .'migration_type.' . $i . ' field is not valid.']);
            }

            if (!in_array($form_field[$i], $this->form_fields)) {
                $fieldType = $isTranslation ? 'translatable_form_field' : 'form_field';
                throw ValidationException::withMessages(["The ". $fieldType . "." .$i ," field is not valid."]);
            }

            if (!$isTranslation && in_array($form_field[$i], ['select', 'select multiple'])) {
                if (!PostType::where('database_table', $request['form_field_configs_1'][$i])->first()) {
                    throw ValidationException::withMessages(['Database table not found in "' . $name[$i] . '" field']);
                }
            }
            $fields[] = [
                'name' => $isTranslation ? $request['translatable_name'][$i] : $name[$i],
                'migration_type' => $isTranslation ? $request['translatable_migration_type'][$i] : $migration_type[$i],
                'form_field' => $isTranslation ? $request['translatable_form_field'][$i] : $request['form_field'][$i],
                'description' => $isTranslation ? $request['translatable_description'][$i] : $request['description'][$i],
                'additional_validations' => $isTranslation ? $request['translatable_additional_validations'][$i] : $request['additional_validations'][$i],
                'can_create' => $isTranslation ? $request['translatable_can_create'][$i] : $request['can_create'][$i],
                'hide_table' => $isTranslation ? $request['translatable_hide_table'][$i] : $request['hide_table'][$i],
                'can_read' => $isTranslation ? $request['translatable_can_read'][$i] : $request['can_read'][$i],
                'can_update' => $isTranslation ? $request['translatable_can_update'][$i] : $request['can_update'][$i],
                'nullable' => $isTranslation ? $request['translatable_nullable'][$i] : $request['nullable'][$i],
                // Conditionally add 'unique' only if it's not a translation
                'unique' => $isTranslation ? null : $request['unique'][$i],
                // Conditionally add 'form_field_configs_1' and 'form_field_configs_2' only for non-translations
                'form_field_configs_1' => $isTranslation ? null : $request['form_field_configs_1'][$i],
                'form_field_configs_2' => $isTranslation ? null : $request['form_field_configs_2'][$i],
            ];
        }
       }

        return $fields;
    }

    
}
