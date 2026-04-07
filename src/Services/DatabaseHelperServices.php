<?php

namespace Darpersodigital\Cms\Services;

use Artisan;
use Schema;
use Auth;
use DB;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class DatabaseHelperServices
{
    public function generateFormField(string $name, string $type, string $input, array $extra = [], bool $isTranslatable = false): array
    {
        $field = array_merge(
            [
                'name' => $name,
                'migration_type' => $type,
                'form_field' => $input,
                'description' => null,
                'additional_validations' => null,
                'can_create' => 1,
                'can_read' => 1,
                'can_update' => 1,
                'hide_table' => 0,
                'nullable' => 0,
                'unique' => 0,
                'form_field_configs_1' => null,
                'form_field_configs_2' => null,
            ],
            $extra,
        );
        if ($isTranslatable) {
            unset($field['unique']);
        }
        return $field;
    }
    public function generateDefaultSingleRecordData(): array
    {
        return [$this->generateFormField('title', 'string', 'text')];
    }
    public function getSeoFields(): array
    {
        return [$this->generateFormField('seo_image', 'text', 'image', [], true), $this->generateFormField('seo_title', 'string', 'text', ['additional_validations' => 'max:60'], true), $this->generateFormField('seo_description', 'string', 'text', ['additional_validations' => 'max:160'], true), $this->generateFormField('seo_keywords', 'string', 'text', [], true)];
    }

    public function createPostType(string $type, array $data): array
    {
        $base = [
            'icon' => null,
            'display_name' => '',
            'display_name_plural' => '',
            'database_table' => null,
            'route' => null,
            'model_name' => null,
            'single_record' => 0,
            'fields' => '[]',
            'translatable_fields' => '[]',
            'add' => 0,
            'edit' => 0,
            'delete' => 0,
            'show' => 0,
            'custom_page' => 0,
            'custom_crud' => 0,
            'hidden' => 0,
            'has_sitemap' => 0,
            'parent_title' => null,
            'parent_icon' => null,
            'server_side_pagination' => 0,
            'is_form' => 0,
            'show_dashboard' => 1,
        ];

        // Inline conditions 👇
        $defaults = match ($type) {
            'custom' => [
                'custom_page' => 1,
                'show_dashboard' => 0,
            ],
            'single' => [
                'single_record' => 1,
                'edit' => 1,
                'show' => 1,
            ],
            'form' => [
                'delete' => 1,
                'show' => 1,
                'server_side_pagination' => 1,
                'is_form' => 1,
            ],

            default => throw new InvalidArgumentException("Invalid post type: {$type}"),
        };

        return array_merge($base, $defaults, $data);
    }
    public function createSeoTranslationTable(string $tableName, string $foreignKey, string $parentTable): void
    {
        Schema::create($tableName, function (Blueprint $table) use ($foreignKey, $parentTable) {
            $table->increments('id');
            $table->unsignedInteger($foreignKey);
            $table->string('locale');
            $table->string('seo_title')->nullable();
            $table->string('seo_description')->nullable();
            $table->string('seo_keywords')->nullable();
            $table->text('seo_image')->nullable();
            $table->foreign($foreignKey)->references('id')->on($parentTable)->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function createStaticPageTable(string $tableName): void
    {
        Schema::create($tableName, function (Blueprint $table) {
            $table->increments('id');
            $table->string('title')->nullable();
            $table->integer('pos')->default(0);
            $table->timestamps();
        });
    }

    public function insertDefaultTranslation(string $tableName, string $foreignKey, int $id, string $locale = 'en'): void
    {
        DB::table($tableName)->insert([
            $foreignKey => $id,
            'locale' => $locale,
        ]);
    }

    public function generatePermissions(int $roleId, int $postTypeId, array $extra = []): array
    {
        return array_merge(
            [
                'admin_role_id' => $roleId,
                'post_type_id' => $postTypeId,
                'browse' => 1,
                'read' => 1,
                'edit' => 1,
                'add' => 1,
                'delete' => 1,
            ],
            $extra,
        );
    }

    public function createStaticPageWithTranslation(string $tableName, string $foreignKey, string $title): void
    {
        $this->createStaticPageTable($tableName);
        $this->createSeoTranslationTable($tableName . '_translations', $foreignKey, $tableName);
        $id = DB::table($tableName)->insertGetId([
            'title' => $title,
            'pos' => 0,
        ]);

        $this->insertDefaultTranslation($tableName . '_translations', $foreignKey, $id);
    }

    public function generateLanguagesTable()
    {
        Schema::create('languages', function ($table) {
            $table->increments('id');
            $table->string('slug');
            $table->string('title');
            $table->string('direction');
            $table->timestamps();
        });

        DB::table('languages')->insert([
            'slug' => 'en',
            'title' => 'English',
            'direction' => 'ltr',
        ]);
    }
}
