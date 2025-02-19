public function store(Request $request, $route) {
        $page = PostType::where('route', $route)->when($this->adminRoleId)->first();
        if ($page->single_record==1 ) {
            $model = 'App\\Models\\' . $page['model_name'];
            $rows = $model::get();
            if(count($rows)>=1) abort('404');
        }
        if (!$page->add==1 && $page->single_record==0) abort('404');


        $page_fields = json_decode($page['fields'], true);
        $translatable_fields = json_decode($page['translatable_fields'], true);
        
        $field_validation_rules = [];
        $translatable_field_validation_rules = [];

        $field_validation_rules = $this->getValidationRules($page_fields, $page['database_table'], null, null);
        $translatable_field_validation_rules_languages = $this->getValidationRules($translatable_fields, $page['database_table'] . '_translations', null, null);
        $validation_rules = array_merge($field_validation_rules, $translatable_field_validation_rules_languages);

        $request->validate($validation_rules);

        $query = [];
        foreach ($page_fields as $field) {
            $fieldName = $field['name'];
            $formField = $field['form_field'];
            if ($formField == 'select multiple' || (isset($field['can_create']) && $field['can_create'] == 0)) {
                continue;
            }
            // Handle different form field types
            switch ($formField) {
                case 'password':
                case 'password with confirmation':
                    $query[$fieldName] = Hash::make($request[$fieldName]);
                    break;
                case 'checkbox':
                    $query[$fieldName] = isset($request[$fieldName]) ? 1 : 0;
                    break;
                case 'time':
                    $query[$fieldName] = date('H:i', strtotime($request[$fieldName]));
                    break;
                case 'image':
                case 'file':
                    if ($request->hasFile($fieldName)) {
                        // Delete the old file if it exists
                        if(isset($query[$fieldName])) Storage::delete($query[$fieldName] ?? null);
                        if($formField == 'image'){
                            $query[$fieldName] = $this->imageController->compressAndUploadImage($request->file($field['name']), $route);
                        } else {
                            $query[$fieldName] = $this->imageController->compressAndUploadFile($request->file($field['name']), $route);
                        }
                    }
                   
                    break;
                case 'multiple images':
                    $newImages =$this->imageController->compressAndUploadMultipleImages($request,$field['name'], $route);
                    $query[$fieldName] = json_encode($newImages);
                    break;
                default:
                    // Default case to handle all other form fields
                    $query[$fieldName] = $request[$fieldName] ?? null;
                    break;
            }
        }

        // Model
        $model = 'App\\Models\\' . $page['model_name'];

        // Get pos // if ($page['order_display'])
        $min = $model::max('pos');
        $query['pos'] = $min ? $min+1 : 1;
        $query['published'] =(isset($request['published']) && $request['published']==1 || $request['published']=='1') ? 1 : 0;

        // Create
        $row = $model::create($query);

        
        // Select multiple insert query
        foreach ($page_fields as $field) {
            if ($field['form_field'] == 'select multiple') {
                // No need for pos here because it's being saved by order
                $row->{str_replace('_id', '', $field['name'])}()->sync($request[$field['name']]);
            }
        }

        $row->save();
        $this->translateOrNew($translatable_fields, $request, $row);

        $request->session()->flash('success', 'Record added successfully');
        return redirect(config('cms_config.route_path_prefix') . '/' . $route);
    }


    public function update(Request $request, $id, $route)
    {
        $page = PostType::where('route', $route)->when($this->adminRoleId, function ($query) {
            $query->where('edit', 1);
        })->firstOrFail();
        $page_fields = json_decode($page['fields'], true);
        $page_translatable_fields = json_decode($page['translatable_fields'], true);
        $translatable_fields = json_decode($page['translatable_fields'], true);

        // Get row
        $model = 'App\\Models\\' . $page['model_name'];
        $row = $model::findOrFail($id);

        // Request validations
        $field_validation_rules = [];
        $translatable_field_validation_rules = [];
        
        $field_validation_rules = $this->getValidationRules($page_fields, $page['database_table'], $id, $row);
        $translatable_field_validation_rules = $this->getValidationRules($translatable_fields, $page['database_table'] . '_translations', $id, $row);
        
        $translatable_field_validation_rules_languages = [];
        
        foreach ($translatable_field_validation_rules as $translatable_field => $translatable_rule) {
            foreach (Language::get() as $language) {
                $translatable_field_validation_rules_languages[$language->slug . '.' . $translatable_field] = $translatable_rule;
            }
        }

        $validation_rules = array_merge($field_validation_rules, $translatable_field_validation_rules_languages);

        $request->validate($validation_rules);
  


        // Update query
        $query = [];
        foreach ($page_fields as $field) {
            // (!isset($request->draft_cms_field) || (isset($request->draft_cms_field) && $request->draft_cms_field != 1)) &&
            if ( ($field['form_field'] == 'slug' && !$field['form_field_configs_2']) || $field['form_field'] == 'select multiple' || (isset($field['can_update']) && $field['can_update']==0)) continue;

            if (($field['form_field'] == 'password' || $field['form_field'] == 'password with confirmation')) {
                if ($request[$field['name']]) {
                    $query[$field['name']] = Hash::make($request[$field['name']]);
                }
            } elseif ($field['form_field'] == 'checkbox') {
                $query[$field['name']] = isset($request[$field['name']]) ? 1 : 0;
            } elseif ($field['form_field'] == 'time') {
                $query[$field['name']] = date('H:i', strtotime($request[$field['name']]));
            } elseif ($field['form_field'] == 'image' || $field['form_field'] == 'file') {
               
                if ($request[$field['name']]) {
                    if(isset($row[$field['name']])) Storage::delete($row[$field['name']]);
                   if($field['form_field'] == 'image'){
                    $query[$field['name']] = $this->imageController->compressAndUploadImage($request->file($field['name']), $route);
                   } else {
                    $query[$field['name']] = $this->imageController->compressAndUploadFile($request->file($field['name']), $route);
                   }
               
                } elseif ($request['remove_file_' . $field['name']]) {
                    if(isset($row[$field['name']])) Storage::delete($row[$field['name']]);
                    $query[$field['name']] = null;
                }
            } 
            elseif ($field['form_field'] == 'multiple images' ) {
          
                $currentImages= isset($request["current_".$field['name']]) && $request["current_".$field['name']]!=null  && $request["current_".$field['name']]!=''? $request["current_".$field['name']] : '';
                $newImages =$this->imageController->compressAndUploadMultipleImages($request,$field['name'], $route,'');
                $allImages =  array_merge(json_decode($currentImages[0] ?:"[]"),$newImages);
                if(isset($row[$field['name']]) && $row[$field['name']]) {
                    foreach( json_decode($row[$field['name']]) as $val) {
                        if (!in_array($val,json_decode($currentImages[0] ?:"[]")))  {
                            Storage::delete($val);
                        }
                    }
                }
               
                $query[$field['name']] = json_encode($allImages);
            } else {
                $query[$field['name']] = $request[$field['name']];
            }
        }
        // $query['published']= isset($request['published']) ? 1 : 0;
 
        $row->update($query);
        $row['published']= (isset($request['published']) && $request['published']==1 || $request['published']=='on') ? 1 : 0;

        // Select multiple update query
        foreach ($page_fields as $field) {
            if ($field['form_field'] == 'select multiple') {
                $sync_values = [];
                if ($request[$field['name']]) {
                    try {
                        foreach ($request[$field['name']] as $sync_id) {
                           
                            $sync_values[$sync_id] =['pos' => $request['pos_'.$field['name']][$sync_id]];
                        }
                        // dd($request[$field['name']]);
           
                        $row->{str_replace('_id', '', $field['name'])}()->sync($sync_values);
                       
                    } catch (\Throwable $th) {
                        $row->{str_replace('_id', '', $field['name'])}()->sync($request[$field['name']]);
                    }
                } else {
                    $row->{str_replace('_id', '', $field['name'])}()->sync([]);
                }
            }
        }

        $row->save();

        $this->translateOrNew($translatable_fields, $request, $row);

        $request->session()->flash('success', 'Record edited successfully');
        return redirect(config('cms_config.route_path_prefix') . '/' . $route . $this->appends_to_query);
    }


    public function translateOrNew($translatable_fields, $request, $row) {
        // Translatable insert query
        if (count($translatable_fields)) {
            foreach (Language::get() as $language) {
                foreach ($translatable_fields as $field) {
                    if ($field['form_field'] == 'select multiple') continue;
                    elseif ($field['form_field'] == 'password' || $field['form_field'] == 'password with confirmation') {
                        $row->translateOrNew($language->slug)->{$field['name']} = Hash::make($request[$language->slug][$field['name']]);
                    } elseif ($field['form_field'] == 'checkbox') {
                        $row->translateOrNew($language->slug)->{$field['name']} = isset($request[$language->slug][$field['name']]) ? 1 : 0;
                    } elseif ($field['form_field'] == 'time') {
                        $row->translateOrNew($language->slug)->{$field['name']} = date('H:i', strtotime($request[$language->slug][$field['name']]));
                    } elseif ($field['form_field'] == 'image' || $field['form_field'] == 'file') {
                        if (isset($request[$language->slug][$field['name']]) && $request[$language->slug][$field['name']]) {
                            if(isset($row->translateOrNew($language->slug)->{$field['name']})) Storage::delete($row->translateOrNew($language->slug)->{$field['name']});
                           if($field['form_field'] == 'image'){
                                $row->translateOrNew($language->slug)->{$field['name']} = $this->imageController->compressAndUploadImage($request->file($language->slug . '.' . $field['name']), $request['route']);
                           }else {
                                $row->translateOrNew($language->slug)->{$field['name']} = $this->imageController->compressAndUploadFile($request->file($language->slug . '.' . $field['name']), $request['route']);
                           }
                        } elseif (isset($request[$language->slug]['remove_file_' . $field['name']]) && $request[$language->slug]['remove_file_' . $field['name']]) {
                            Storage::delete($row->translateOrNew($language->slug)->{$field['name']});
                            $row->translateOrNew($language->slug)->{$field['name']} = null;

                        }
                    }elseif ($field['form_field'] == 'multiple images' ) {
                        $field_name = $language->slug.'.'.$field['name'];
                        $currentImages= isset($request["current_".$field_name]) && $request["current_".$field_name]!=null  && $request["current_".$field_name]!=''? $request["current_".$field_name] : [''];
                        $newImages =$this->imageController->compressAndUploadMultipleImages($request,$field['name'], $request->route,$language->slug);
                        $allImages =  array_merge(json_decode($currentImages[0] ?:"[]"),$newImages);
                        if(isset($row->translateOrNew($language->slug)->{$field['name']}) && $row->translateOrNew($language->slug)->{$field['name']}!='') {
                            foreach( json_decode($row->translateOrNew($language->slug)->{$field['name']}) as $val) {
                                if (!in_array($val,json_decode($currentImages[0] ?:"[]")))   Storage::delete($val);
                            }
                        }
                        $row->translateOrNew($language->slug)->{$field['name']} = json_encode($allImages);
                    }
                    else {
                        $row->translateOrNew($language->slug)->{$field['name']} = isset($request[$language->slug][$field['name']]) ? $request[$language->slug][$field['name']] : null;
                    }  
                }
            }
            $row->save();
        }
    }



    // p2

    public function store(Request $request){
        // $this->validatePostType($request);
        // $fields = $this->beautifyFields($request);
        // if (!is_array($fields)) return $fields;
        // $translatable_fields = $this->beautifyTranslatableFields($request);
        // if (!is_array($translatable_fields)) return $translatable_fields;
        // $this->createDatabase($request);
        // $this->createModel($request);
        // $post_type = new PostType;
        // $post_type->icon = $request->icon;
        // $post_type->display_name = $request->display_name;
        // $post_type->display_name_plural = $request->display_name_plural;
        // $post_type->database_table = $request->database_table;
        // $post_type->route = Str::slug($request->database_table);
        // $post_type->model_name = $request->model_name;
        // $post_type->order_display = $request->order_display;
        // $post_type->sort_by = $request->sort_by;
        // $post_type->sort_by_direction = $request->sort_by_direction;

        // $post_type->fields = json_encode($fields);
        // $post_type->translatable_fields = json_encode($translatable_fields);
        // $post_type->add = isset($request->single_record) ? 0 : (isset($request->add) ? 1 : 0);
        // $post_type->edit = isset($request->edit) ? 1 : 0;
        // $post_type->delete = isset($request->single_record) ? 0 : (isset($request->delete) ? 1 : 0);
        // $post_type->show = isset($request->show) ? 1 : 0;
        // $post_type->single_record = isset($request->single_record) ? 1 : 0;
        // $post_type->server_side_pagination = isset($request->server_side_pagination) ? 1 : 0;
        // $post_type->with_export = isset($request->with_export) ? 1 : 0;
        // $post_type->hidden = isset($request->hidden) ? 1 : 0;
        // $post_type->is_form = isset($request->is_form) ? 1 : 0;
        // $post_type->pos = PostType::max('pos') + 1;
        // $post_type->save();

        
        // $admin_role_permission = new AdminRolePermission;
        // $admin_role_permission->admin_role_id = 1;
        // $admin_role_permission->post_type_id = $post_type->id;
        // $admin_role_permission->browse = 1;
        // $admin_role_permission->read = 1;
        // $admin_role_permission->edit = 1;
        // $admin_role_permission->add =1;
        // $admin_role_permission->delete = 1;
        // $admin_role_permission->save();

        // $request->session()->flash('success', 'Page added successfully');
        // return redirect(config('cms_config.route_path_prefix') . '/' . $post_type->route);
    }
    public function update(Request $rq, $id){
        $request = $rq;
        $post_type = PostType::where('custom_page', 0)->findOrFail($id);
        $this->validatePostType($request,$id);
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
        $fields = $this->beautifyFields($request);
        if (!is_array($fields)) return $fields;
        $translatable_fields = $this->beautifyTranslatableFields($request);
        if (!is_array($translatable_fields)) return $translatable_fields;
        if (count($request['old_name']) != count($request['name'])) abort(500);
        $r = $this->editDatabase($request, $post_type);
        if (!is_null($r)) return $r;
        if ($post_type->database_table != $request->database_table) $db_controller->deleteModel($post_type);
        $this->createModel($request);
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
 
        $post_type->save();

        $request->session()->flash('success', 'Page edited successfully');

        return redirect(config('cms_config.route_path_prefix') . '/' . $post_type->route);

    }





    // if ($old_table['database_table'] != $request['database_table']) {
        //     $fields = json_decode($old_table['fields'], true);
        //     $hasPivotField = collect($fields)->contains(fn($field) => $field['form_field'] === 'select multiple');
        //     if ($hasPivotField) {
        //         throw ValidationException::withMessages(['Remove fields with pivot table (form field `select multiple`) before changing database table name']);
        //     }
        //     // Rename tables (with translation handling)
        //     if ($request['translatable_name']) {
        //         Schema::rename($old_table['database_table'], $request['database_table'] . '_translations');
        //     }
        //     Schema::rename($old_table['database_table'], $request['database_table']);
        // }

    // foreach ($request['translatable_name'] as $i => $name) {
    //     // Skip 'select' and 'select multiple' fields
    //     if (in_array($request['translatable_form_field'][$i], ['select', 'select multiple'], true)) {
    //         continue;
    //     }
    //     // New field: If there's no old name
    //     if (empty($request['translatable_old_name'][$i])) {
    //         // Find the previous column that isn't a 'select multiple'
    //         $afterColumn = 'id';
    //         for ($j = $i - 1; $j >= 0; $j--) {
    //             if ($request->translatable_form_field[$j] !== 'select multiple') {
    //                 $afterColumn = $request->translatable_name[$j];
    //                 break;
    //             }
    //         }

    //         // Add new field to the translations table
    //         Schema::table($request['database_table'] . '_translations', function ($table) use ($request, $i, $name, $afterColumn) {
    //             $column = $table->{$request->translatable_migration_type[$i]}($name);

    //             // Add unsigned for 'select' fields and nullable if set
    //             if ($request['translatable_form_field'][$i] === 'select') {
    //                 $column->unsigned();
    //             }
    //             if (!empty($request->translatable_nullable[$i])) {
    //                 $column->nullable();
    //             }

    //             $column->after($afterColumn)->change();
    //         });
    //     }
    //     // Existing field: Update column name if changed
    //     elseif ($name !== $request['translatable_old_name'][$i]) {
    //         Schema::table($request['database_table'] . '_translations', function ($table) use ($request, $i, $name) {
    //             $table->renameColumn($request['translatable_old_name'][$i], $name);
    //         });
    //     }
    // }
    
    //     foreach ($request['name'] as $i => $name) {
    //         if ($request['form_field'][$i] === 'select multiple')  continue;
    //         // Handle new fields
    //         if (empty($request['old_name'][$i])) {
    //             // Find the previous column that isn't 'select multiple'
    //             $afterColumn = 'id';
    //             for ($j = $i - 1; $j >= 0; $j--) {
    //                 if ($request['form_field'][$j] !== $excludedField) {
    //                     $afterColumn = $request['name'][$j];
    //                     break;
    //                 }
    //             }
    //             Schema::table($request['database_table'], function ($table) use ($request, $i, $name, $afterColumn) {
    //                 $column = $table->{$request->migration_type[$i]}($name);
    //                 if ($request['form_field'][$i] === 'select') {
    //                     $column->unsigned();
    //                 }
    //                 if (!empty($request['nullable'][$i])) {
    //                     $column->nullable();
    //                 }
    //                 $column->after($afterColumn);
    //             });
    //         }
    //         // Handle existing fields (renaming columns)
    //         elseif ($name !== $request['old_name'][$i]) {
    //             Schema::table($request['database_table'], function ($table) use ($request, $i, $name) {
    //                 $table->renameColumn($request['old_name'][$i], $name);
    //             });
    //         }
    //     }