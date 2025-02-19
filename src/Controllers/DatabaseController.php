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

class DatabaseController extends BaseController
{

    public function createDatabase($request)  {
        $this->createTable($request);
        $this->managePivotTables($request);
        $this->createTranslationsTable($request,false);
    }

    public function deleteDatabase($post_type) {
        if ($post_type->database_table) {
            foreach (json_decode($post_type['fields'], true) as $field) {
                if ($field['form_field'] == 'select multiple') {
                    $pivot_table = Str::singular($field['form_field_configs_1']) . '_id_' . Str::singular($post_type->database_table);
                    Schema::drop($pivot_table);
                }
            }
            if (count(json_decode($post_type['translatable_fields'], true))) {
                Schema::drop($post_type->database_table . '_translations');
            }
      
            Schema::drop($post_type->database_table);
          
        }
    }
    private function addColumn($table, $request, $index, $isTranslation = false) {
        $name = $isTranslation ? $request->translatable_name[$index] : $request->name[$index];
        $type = $isTranslation ? $request->translatable_migration_type[$index] : $request->migration_type[$index];
        $nullable = $isTranslation ? $request->translatable_nullable[$index] : $request->nullable[$index];
        $form_field = $isTranslation ? $request->translatable_form_field[$index] : $request->form_field[$index];
        $column = $table->{$type}($name);
        if ($form_field == 'select') {
            $column->unsigned();
            $table->foreign($name)->references('id')->on($request->form_field_configs_1[$index])->onDelete('cascade');
        }
        if ($nullable) $column->nullable();
    }

    // MODEL
    public function deleteModel($post_type)  {
        if (file_exists(app_path('/Models//' . $post_type->model_name . '.php')))
            unlink(app_path('/Models//' . $post_type->model_name . '.php'));

            if (file_exists(app_path('/Models//' . $post_type->model_name . 'Translation.php')))
            unlink(app_path('/Models//' . $post_type->model_name . 'Translation.php'));
    }

    private function createTable($request)  {
        Schema::create($request['database_table'], function ($table) use ($request) {
            $table->increments('id');
            $table->integer('published')->default(0);
            foreach ($request->form_field as $f => $form_field) {
                if ($form_field == 'select multiple') continue;
                $this->addColumn($table, $request, $f);
            }
            $table->integer('pos')->nullable();
            $table->timestamps();
        });
    }

    private function managePivotTables($request, $old_page = null) {
        foreach ($request->form_field as $f => $form_field) {
            if ($form_field == 'select multiple') {
                $pivot_table = null;
                $old_pivot_table = null;
                $column_name = $request->form_field_configs_1[$f] == $request->database_table
                ? 'other_' . Str::singular($request->form_field_configs_1[$f]) . '_id'
                : Str::singular($request->form_field_configs_1[$f]) . '_id';
    
                // Handle renaming or creating a new pivot table
                if (!$request->old_name[$f]) {
                    $pivot_table = $this->getPivotTableName($request, $f);
                } elseif ($request->old_name[$f] != $request->name[$f]) {
                    $old_pivot_table = $this->getPivotTableName($request, $f, true);
                    Schema::dropIfExists($old_pivot_table);
                    $pivot_table = $this->getPivotTableName($request, $f);
                }
                
                if ($pivot_table) {
                    $this->createPivotSchema($pivot_table, $column_name, $request, $f, !$request->old_name[$f]);
                }
            }
        }
    }
    private function getPivotTableName($request, $index, $isOld = false) {
        $name = $isOld ? $request->old_name[$index] : $request->name[$index];
        return Str::singular($name) . '_' . Str::singular($request->database_table);
    }
    private function createPivotSchema($pivot_table, $column_name, $request, $index, $includeExtraFields) {
        Schema::create($pivot_table, function ($table) use ($column_name, $request, $index, $includeExtraFields) {
            $table->increments('id');
            $table->integer($column_name)->unsigned();
            $table->integer(Str::singular($request->database_table) . '_id')->unsigned();
            
            if ($includeExtraFields) {
                $table->integer('pos')->unsigned()->nullable();
                $table->integer('published')->default(0);
            }
            
            $table->timestamps();
            $table->foreign($column_name)->references('id')->on($request->form_field_configs_1[$index])->onDelete('cascade');
            $table->foreign(Str::singular($request->database_table) . '_id')->references('id')->on($request->database_table)->onDelete('cascade');
        });
    }


    private function createTranslationsTable($request,$isUpdate)  {
        if ($request->translatable_form_field || $isUpdate) {
            Schema::create($request['database_table'] . '_translations', function ($table) use ($request,$isUpdate) {
                $table->increments('id');
                $table->string('locale');
                $table->integer(Str::singular($request->database_table) . '_id')->unsigned();
                if(!$isUpdate) {
                    foreach ($request->translatable_form_field as $f => $form_field) {
                        if (!in_array($form_field, ['select', 'select multiple'])) {
                            $this->addColumn($table, $request, $f, true);
                        }
                    }
                }
                $table->timestamps();
                $table->foreign(Str::singular($request->database_table) . '_id')->references('id')->on($request->database_table)->onDelete('cascade');
            });
        }
    }

  

    public function editDatabase($request, $old_page)
    {
        // Database table name changed
        $this->handleDatabaseRename($request, $old_page);
    
        // Modify the migration type and nullable property for all columns.
        $this->modifyMigrationTypesAndNullable($request,$old_page,false);

        // Update pivot tables
        $this->managePivotTables($request,$old_page);

        // Delete columns
        $this->deleteColumns($request,$old_page,false);


        // Check pivot tables
        foreach (json_decode($old_page['fields'], true) as $old_field) {
            // Only process 'select multiple' fields
            if ($old_field['form_field'] !== 'select multiple') continue;

            // Check if the field exists in the request form fields
            $field_found = false;
            foreach ($request->form_field as $i => $form_field) {
                if ($form_field === 'select multiple' && $old_field['form_field_configs_1'] === $request->form_field_configs_1[$i]) {
                    $field_found = true;
                    break; // Exit loop once the field is found
                }
            }

            // If the field is not found, drop the pivot table
            if (!$field_found) {
                $pivot_table = Str::singular($old_field['name']) . '_' . Str::singular($old_page->database_table);
                Schema::drop($pivot_table);
            }
        }
        
        // Edit translations table
        if ($request['translatable_name']) {
            // Create table if not exists
            if (!Schema::hasTable($request['database_table'] . '_translations')) {
                $this->createTranslationsTable($request,true);
            }
            $this->modifyMigrationTypesAndNullable($request,$old_page,true);

            // Delete columns
            $this->deleteColumns($request,$old_page,true);

        } else {
            // Drop table table if exists
            Schema::dropIfExists($request['database_table'] . '_translations');
        }
    }


    private function modifyMigrationTypesAndNullable($request,$old_page,$isTranslation){

        $tableSuffix = $isTranslation ? '_translations' : '';
        $formFieldKey = $isTranslation ? 'translatable_form_field' : 'form_field';
        $migrationTypeKey = $isTranslation ? 'translatable_migration_type' : 'migration_type';
        $nullableKey = $isTranslation ? 'translatable_nullable' : 'nullable';
        $oldNameKey = $isTranslation ? 'translatable_old_name' : 'old_name';
        $nameKey = $isTranslation ? 'translatable_name' : 'name';
        
        $excludedField = $isTranslation ? ['select', 'select multiple'] : ['select multiple'];

        foreach ($request[$nameKey] as $i => $name) {
            $formField = $request[$formFieldKey][$i];

            // Skip 'select' and 'select multiple' fields
            if (in_array($formField, $excludedField, true)) continue;

            // New field: If there's no old name (this is a new field)
            if (empty($request[$oldNameKey][$i]) || !$request[$oldNameKey][$i]) {
                // Find the previous column that isn't 'select multiple'
                $afterColumn = 'id';
                if ($i > 0) {
                    for ($j = $i - 1; $j >= 0; $j--) {
                        if ($request[$formFieldKey][$j] !== 'select multiple') {
                            $afterColumn = $request[$nameKey][$j];
                            break;
                        }
                    }
                }

                // Add new field to the table (either translations or regular table)
                Schema::table($request['database_table'] . $tableSuffix, function ($table) use ($request, $i, $name, $afterColumn, $formField, $migrationTypeKey, $nullableKey) {
                    $table->{$request[$migrationTypeKey][$i]}($name)->{$formField == 'select' ? 'unsigned' : ''}()->{$request[$nullableKey][$i] ? 'nullable' : ''}()->after($afterColumn);
                });
            }
            // Existing field: Update column name if changed
            elseif ($name !== $request[$oldNameKey][$i]) {
                Schema::table($request['database_table'] . $tableSuffix, function ($table) use ($request, $i, $name, $oldNameKey) {
                    $table->renameColumn($request[$oldNameKey][$i], $name);
                });
            }
        }

        $rqName= $isTranslation ? $request['translatable_name'] :  $request['name'];
  
        $field_name = $isTranslation ? $request['translatable_form_field'] :  $request['form_field'];
 
        foreach ($rqName as $i => $name) {
            if (in_array($field_name[$i], ['select multiple', 'select'], true))  continue;

            Schema::table($request['database_table'] . $tableSuffix, function ($table) use ($request, $i, $name,$isTranslation) {
                $columnType = $isTranslation ?  $request->translatable_migration_type[$i] : $request->migration_type[$i];
                $column = $table->$columnType($name);
                if ($isTranslation &&!empty($request->translatable_nullable[$i])) {
                    $column->nullable();
                }else if (!empty($request->nullable[$i])) $column->nullable();
                $column->change();
            });
        }
    }

    public function getStringBetween($string, $start, $end) {
        $string = ' ' . $string;
        $ini = strpos($string, $start);
        if ($ini == 0) return '';
        $ini += strlen($start);
        $len = strpos($string, $end, $ini) - $ini;
        return substr($string, $ini, $len);
    }

    private function deleteColumns($request,$old_page,$isTranslation){
        $excludedColumns = ['id', 'published', 'pos', 'created_at', 'updated_at'];
        $databaseTable = $request['database_table'] . ($isTranslation ? '_translations' :'');
        $requestedNames = $isTranslation  ? $request->translatable_name : $request->name;

        if($isTranslation){
            array_push($excludedColumns, 'locale',(Str::singular($request->database_table) . '_id'));
        }

        // Get the list of database columns
        $columns = Schema::getColumnListing($databaseTable);

        // Filter out excluded columns
        $columnsToDelete = array_diff($columns, $excludedColumns, $requestedNames);
        if (!empty($columnsToDelete)) {
            Schema::table($databaseTable, function ($table) use ($columnsToDelete) {
                $table->dropColumn($columnsToDelete);
            });
        }
    }

    private function handleDatabaseRename($request, $old_table) {
        if ($old_table['database_table'] === $request['database_table']) {
            return;
        }
        
        $fields = json_decode($old_table['fields'], true);
        $containsPivotField = collect($fields)->contains(fn($field) => $field['form_field'] === 'select multiple');
        
        if ($containsPivotField) {
            throw ValidationException::withMessages([
                'database_table' => 'Remove fields with pivot table (form field `select multiple`) before changing the database table name.'
            ]);
        }
        
        // Rename tables, including translations if applicable
        if ($request['translatable_name'] && Schema::hasTable($old_table['database_table'].'_translations')) {
            try {
                Schema::rename($old_table['database_table'].'_translations', $request['database_table'] . '_translations');
            }catch (Exception $e) {

            }
            
        }
        Schema::rename($old_table['database_table'], $request['database_table']);


        
    }

    public function createModel($request) {
        $head = '';
        $implements = '';
        $use = '';
        $translated_attributes = '';
        if ($request['translatable_name']) {
            $head = 'use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract; use Astrotomic\Translatable\Translatable;';
            $implements = ' implements TranslatableContract';
            $use = 'use Translatable;';
            $translated_attributes = 'protected $hidden = [\'translations\'];

    public $translatedAttributes = ' . json_encode($request['translatable_name']) . ';';
        }
        
        $body = 'protected static function booted(){}';
        foreach ($request['form_field'] as $f => $form_field) {
            $second_database_table = $request->form_field_configs_1[$f];
            $second_page = PostType::where('database_table', $second_database_table)->firstOrFail();
            $model_name = $second_page['model_name'];
            $method_name = str_replace('_id', '', $request->name[$f]);
            if ($form_field == 'select') {
                $body .= 'public function ' . $method_name . '() { return $this->belongsTo' . "('App\\Models\\" .  $model_name . "')" . '; } ';
            } elseif ($form_field == 'select multiple') {
                $pivot_table = Str::singular($request->name[$f]) . '_' . Str::singular($request->database_table);
                $column_name =  $second_database_table == $request->database_table ? 'other_' . Str::singular($second_database_table) . '_id' : Str::singular($second_database_table) . '_id';
                $body .= 'public function ' . $method_name . '() { return $this->belongsToMany' . "('App\\Models\\" .  $model_name . "', '" . $pivot_table . "', '" . Str::singular($request->database_table) . '_id' . "', '" . $column_name . "')->orderBy('" . $pivot_table . ".pos')" . '; } ';
            }
        }

        $custom_functions = '
';
        if (file_exists(app_path('/Models//' . $request['model_name'] . '.php'))) {
            $old_content = file_get_contents(app_path('/Models//' . $request['model_name'] . '.php'));
            $custom_functions = $this->getStringBetween($old_content, '/* Start custom functions */', '/* End custom functions */');
        }

        $this->createModelFile($request['model_name'], [
            $head,
            $request['model_name'],
            $implements,
            $request['database_table'],
            $use,
            $translated_attributes,
            $body,
            $custom_functions,
        ]);

        if ($request['translatable_name']) {
            $replacements['model_name'] = $request['model_name'] . 'Translation';
            $replacements['database_table'] = $request['database_table'] . '_translations';
        
            $this->createModelFile($request['model_name'] . 'Translation',[
                '',
                $request['model_name'] . 'Translation',
                '',
                $request['database_table'] . '_translations',
                '',
                '',
                '',
                $custom_functions,
            ]);
        }
    }

    private function createModelFile($modelName, $replacements) {
        $filePath = app_path('/Models//' . $modelName . '.php');

        $fileContent = str_replace(
            [
                '%%head%%',
                '%%model_name%%',
                '%%implements%%',
                '%%database_table%%',
                '%%use%%',
                '%%translated_attributes%%',
                '%%body%%',
                '%%custom_functions%%',
            ],
            $replacements,
            file_get_contents(__DIR__ . '/constants/model.stub')
        );
    
        file_put_contents($filePath, $fileContent);
    }
    
}

