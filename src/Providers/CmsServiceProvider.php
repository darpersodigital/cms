<?php

namespace Darpersodigital\Cms\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\File;

use Artisan;
use Schema;
use Auth;
use DB;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CmsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
       
     
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'darpersocms');
    

        // Controllers
      
        $this->app['router']->aliasMiddleware('admin', \Darpersodigital\Cms\Middlewares\AdminMiddleware::class);

      

    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
     
        Schema::defaultStringLength(191);
     
        $this->publishes([__DIR__ . '/../publishable/config' => config_path('/')], 'cms_config');
        $this->publishes([__DIR__ . '/../publishable/test-env' => config_path('/')], 'test-env');
        
        if (!is_array($this->app['config']->get('cms_config'))) $this->runInitialSetup();
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');


    }

    protected function runInitialSetup() {

        $this->generateDatabase();
        // Publish cms assets


        $files = [
            "CfMessage.php",
            "ContactPage.php",
            "ContactPageTranslation.php",
            "HomePage.php",
            "HomePageTranslation.php",
            "SiteSetting.php",
            "SiteSettingTranslation.php",
        ];
        
        foreach ($files as $file) {
            File::copy(__DIR__ . "/../static/models/{$file}", base_path("app/Models/{$file}"));
        }
        File::copyDirectory(__DIR__ . "/../static/fontawesome", base_path("public/assets/fontawesome-cms"));

   
     
        Artisan::call('vendor:publish --tag=cms_config --force');
        
    }

    protected function generateDatabase() {
        try {
            Schema::dropIfExists('sessions');
            Schema::create('sessions', function (Blueprint $table) {
                $table->string('id')->primary();
                $table->foreignId('user_id')->nullable()->index();
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->longText('payload');
                $table->integer('last_activity')->index();
            });
        }catch (Exception $e){

        }
        Schema::dropIfExists('admin_role_permissions');
        Schema::dropIfExists('admins');
        Schema::dropIfExists('admin_roles');
        Schema::dropIfExists('post_types');
        Schema::dropIfExists('cf_messages');
        Schema::dropIfExists('home_pages');
        Schema::dropIfExists('home_pages_translations');
        Schema::dropIfExists('contact_pages');
        Schema::dropIfExists('contact_pages_translations');
        Schema::dropIfExists('site_settings');
        Schema::dropIfExists('site_settings_translations');
        Schema::dropIfExists('languages');
        
        

        Schema::create('admin_roles', function ($table) {
            $table->increments('id');
            $table->string('title');
            $table->timestamps();
        });
        DB::table('admin_roles')->insert([
    		'title' => 'Administrator',
    	]);
        DB::table('admin_roles')->insert([
    		'title' => 'Customer',
    	]);

        Schema::create('admins', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('image')->nullable();
            $table->string('email')->unique();
            $table->string('password');
            $table->integer('admin_role_id')->nullable()->unsigned();
            $table->timestamp('reset_password_date')->nullable();
            $table->rememberToken();
            $table->timestamps();

            $table->foreign('admin_role_id')->references('id')->on('admin_roles')->onDelete('cascade');
        });

        DB::table('admins')->insert([
    		'name' => 'admin',
    		'email' => 'admin@admin.com',
    		'password' => bcrypt('123'),
            'admin_role_id'=>1,
            "created_at" =>  date('Y-m-d H:i:s'),
            "updated_at" => date('Y-m-d H:i:s'),
    	]);

        Schema::create('post_types', function ($table) {
            $table->increments('id');
            $table->string('icon')->nullable();
            $table->string('display_name')->nullable();
            $table->string('display_name_plural')->nullable();
            $table->string('database_table')->unique()->nullable();
            $table->string('route')->unique()->nullable();
            $table->string('model_name')->unique()->nullable();
            $table->string('sort_by')->nullable();
            $table->string('sort_by_direction')->nullable();
            $table->string('order_display')->nullable();
            $table->longtext('fields')->nullable();
            $table->longtext('translatable_fields')->nullable();
            $table->tinyInteger('add')->nullable();
            $table->tinyInteger('edit')->nullable();
            $table->tinyInteger('delete')->nullable();
            $table->tinyInteger('show')->nullable();
            $table->tinyInteger('single_record')->nullable();
            $table->tinyInteger('server_side_pagination')->nullable();
            $table->tinyInteger('with_export')->nullable();
            $table->tinyInteger('is_form')->default(0);
            $table->tinyInteger('hidden')->default(0);
            $table->tinyInteger('custom_page')->default(0);
            $table->string('parent_title')->nullable();
            $table->string('parent_icon')->nullable();
            $table->integer('pos')->nullable();
            $table->timestamps();
        });

        DB::table('post_types')->insert([
            [
                'icon' => 'fa-solid fa-table-columns',
                'display_name' => "",
                'display_name_plural' => 'Post Types',
                'database_table' => null,
                'route' => 'post-types',
                'model_name' => null,
                'custom_page' => 1,
                'fields' => null,
                'translatable_fields' => null,
                'add' => null,
                'edit' => null,
                'delete' => null,
                'show' => null,
                'single_record' => null,
                'hidden' => 1,
                'parent_title' => null,
                'parent_icon' => null,
                'server_side_pagination'=>0,
                'is_form'=>0,
            ],
            [
                'icon' => 'fa-language',
                'display_name' => "",
                'display_name_plural' => 'Languages',
                'database_table' => null,
                'route' => 'languages',
                'model_name' => null,
                'custom_page' => 1,
                'fields' => null,
                'translatable_fields' => null,
                'add' => null,
                'edit' => null,
                'delete' => null,
                'show' => null,
                'single_record' => null,
                'hidden' => 0,
                'parent_title' => null,
                'parent_icon' => null,
                'server_side_pagination'=>0,
                'is_form'=>0,
            ],
            [
                'icon' => 'fa-lock',
                'display_name' => "",
                'display_name_plural' => 'Admin Roles',
                'database_table' => null,
                'route' => 'admin-roles',
                'model_name' => null,
                'custom_page' => 1,
                'fields' => null,
                'translatable_fields' => null,
                'add' => null,
                'edit' => null,
                'delete' => null,
                'show' => null,
                'single_record' => null,
                'hidden' => 0,
                'parent_title' => 'Admins',
                'parent_icon' => 'fa-user-secret',
                'server_side_pagination'=>0,
                'is_form'=>0,
            ],
            [
                'icon' => ' fa-user-secret',
                'display_name' => "",
                'display_name_plural' => 'Admins',
                'database_table' => null,
                'route' => 'admins',
                'model_name' => null,
                'custom_page' => 1,
                'fields' => null,
                'translatable_fields' => null,
                'add' => null,
                'edit' => null,
                'delete' => null,
                'show' => null,
                'single_record' => null,
                'hidden' => 0,
                'parent_title' => 'Admins',
                'parent_icon' => 'fa-user-secret',
                'server_side_pagination'=>0,
                'is_form'=>0,
            ],
            [
                'icon' => 'fa-solid fa-house-user',
                'display_name' => "Home Page",
                'display_name_plural' => 'Home Pages',
                'database_table' => "home_pages",
                'route' => 'home-pages',
                'model_name' => "HomePage",
                'custom_page' => 0,
                'fields' => '[{"name":"title","migration_type":"string","form_field":"text","form_field_configs_1":null,"additional_validations":null,"form_field_configs_2":null,"description":null,"hide_table":"0","can_create":"1","can_read":"1","can_update":"1","nullable":"0","unique":"0"}]',
                'translatable_fields' => '[{"name":"seo_image","migration_type":"text","form_field":"image","description":null,"additional_validations":"max:1000","can_create":"1","hide_table":"0","can_read":"1","can_update":"1","nullable":"1"},{"name":"seo_title","migration_type":"string","form_field":"text","description":null,"additional_validations":"max:60","can_create":"1","hide_table":"0","can_read":"1","can_update":"1","nullable":"1"},{"name":"seo_description","migration_type":"string","form_field":"text","description":null,"additional_validations":"max:160","can_create":"1","hide_table":"0","can_read":"1","can_update":"1","nullable":"1"},{"name":"seo_keywords","migration_type":"string","form_field":"text","description":null,"additional_validations":null,"can_create":"1","hide_table":"0","can_read":"1","can_update":"1","nullable":"1"}]',
                'add' => 0,
                'edit' => 1,
                'delete' => 0,
                'show' => 1,
                'single_record' => 1,
                'hidden' => 0,
                'parent_title' => null,
                'parent_icon' => null,
                'server_side_pagination'=>0,
                'is_form'=>0,
            ],
            [
                'icon' => 'fa-solid fa-message',
                'display_name' => "Contact Page",
                'display_name_plural' => 'Contact Pages',
                'database_table' => "contact_pages",
                'route' => 'contact-pages',
                'model_name' => "ContactPage",
                'custom_page' => 0,
                'fields' => '[{"name":"send_form_messages_to","migration_type":"string","form_field":"email","form_field_configs_1":null,"additional_validations":"email","form_field_configs_2":null,"description":null,"hide_table":"0","can_create":"1","can_read":"1","can_update":"1","nullable":"0","unique":"0"}]',
                'translatable_fields' => '[{"name":"seo_image","migration_type":"text","form_field":"image","description":null,"additional_validations":"max:1000","can_create":"1","hide_table":"0","can_read":"1","can_update":"1","nullable":"1"},{"name":"seo_title","migration_type":"string","form_field":"text","description":null,"additional_validations":"max:60","can_create":"1","hide_table":"0","can_read":"1","can_update":"1","nullable":"1"},{"name":"seo_description","migration_type":"string","form_field":"text","description":null,"additional_validations":"max:160","can_create":"1","hide_table":"0","can_read":"1","can_update":"1","nullable":"1"},{"name":"seo_keywords","migration_type":"string","form_field":"text","description":null,"additional_validations":null,"can_create":"1","hide_table":"0","can_read":"1","can_update":"1","nullable":"1"}]',
                'add' => 0,
                'edit' => 1,
                'delete' => 0,
                'show' => 1,
                'single_record' => 1,
                'hidden' => 0,
                'parent_title' => null,
                'parent_icon' => null,
                'server_side_pagination'=>0,
                'is_form'=>0,
            ],
            [
                'icon' => 'fa-solid fa-gear',
                'display_name' => "Site Setting",
                'display_name_plural' => 'Site Settings',
                'database_table' => "site_settings",
                'route' => 'site-settings',
                'model_name' => "SiteSetting",
                'custom_page' => 0,
                'translatable_fields' => '[{"name":"seo_image","migration_type":"text","form_field":"image","description":null,"additional_validations":"max:1000","can_create":"1","hide_table":"0","can_read":"1","can_update":"1","nullable":"1"},{"name":"seo_title","migration_type":"string","form_field":"text","description":null,"additional_validations":"max:60","can_create":"1","hide_table":"0","can_read":"1","can_update":"1","nullable":"1"},{"name":"seo_description","migration_type":"string","form_field":"text","description":null,"additional_validations":"max:160","can_create":"1","hide_table":"0","can_read":"1","can_update":"1","nullable":"1"},{"name":"seo_keywords","migration_type":"string","form_field":"text","description":null,"additional_validations":null,"can_create":"1","hide_table":"0","can_read":"1","can_update":"1","nullable":"1"}]',
                'fields' => '[{"name":"instagram_url","migration_type":"text","form_field":"text","form_field_configs_1":null,"additional_validations":"","form_field_configs_2":null,"description":null,"hide_table":"0","can_create":"1","can_read":"1","can_update":"1","nullable":"1","unique":"0"},{"name":"facebook_url","migration_type":"text","form_field":"text","form_field_configs_1":null,"additional_validations":"","form_field_configs_2":null,"description":null,"hide_table":"0","can_create":"1","can_read":"1","can_update":"1","nullable":"1","unique":"0"},{"name":"twitter_url","migration_type":"text","form_field":"text","form_field_configs_1":null,"additional_validations":"","form_field_configs_2":null,"description":null,"hide_table":"0","can_create":"1","can_read":"1","can_update":"1","nullable":"1","unique":"0"},{"name":"linkedin_url","migration_type":"text","form_field":"text","form_field_configs_1":null,"additional_validations":"","form_field_configs_2":null,"description":null,"hide_table":"0","can_create":"1","can_read":"1","can_update":"1","nullable":"1","unique":"0"},{"name":"phone_number","migration_type":"string","form_field":"text","form_field_configs_1":null,"additional_validations":null,"form_field_configs_2":null,"description":null,"hide_table":"0","can_create":"1","can_read":"1","can_update":"1","nullable":"1","unique":"0"}]',
                'add' => 0,
                'edit' => 1,
                'delete' => 0,
                'show' => 1,
                'single_record' => 1,
                'hidden' => 0,
                'parent_title' => null,
                'parent_icon' => null,
                'server_side_pagination'=>0,
                'is_form'=>0,
            ],
            [
                'icon' => 'fa-solid fa-envelopes-bulk',
                'display_name' => "Contact Form Message",
                'display_name_plural' => 'Contact Form Messages',
                'database_table' => "cf_messages",
                'route' => 'cf-message',
                'model_name' => "CfMessage",
                'custom_page' => 0,
                'translatable_fields' => '[]',
                'fields' => '[{"name":"full_name","migration_type":"string","form_field":"text","form_field_configs_1":null,"additional_validations":null,"form_field_configs_2":null,"description":null,"hide_table":"0","can_create":"1","can_read":"1","can_update":"1","nullable":"1","unique":"0"},{"name":"email","migration_type":"string","form_field":"text","form_field_configs_1":null,"additional_validations":null,"form_field_configs_2":null,"description":null,"hide_table":"0","can_create":"1","can_read":"1","can_update":"1","nullable":"1","unique":"0"},{"name":"subject","migration_type":"string","form_field":"text","form_field_configs_1":null,"additional_validations":null,"form_field_configs_2":null,"description":null,"hide_table":"0","can_create":"1","can_read":"1","can_update":"1","nullable":"1","unique":"0"},{"name":"message","migration_type":"text","form_field":"text","form_field_configs_1":null,"additional_validations":null,"form_field_configs_2":null,"description":null,"hide_table":"0","can_create":"1","can_read":"1","can_update":"1","nullable":"1","unique":"0"},{"name":"read","migration_type":"integer","form_field":"checkbox","form_field_configs_1":null,"additional_validations":null,"form_field_configs_2":null,"description":null,"hide_table":"0","can_create":"1","can_read":"1","can_update":"1","nullable":"1","unique":"0"},{"name":"star","migration_type":"integer","form_field":"checkbox","form_field_configs_1":null,"additional_validations":null,"form_field_configs_2":null,"description":null,"hide_table":"0","can_create":"1","can_read":"1","can_update":"1","nullable":"1","unique":"0"}]',
                'add' => 0,
                'edit' => 0,
                'delete' => 1,
                'show' => 1,
                'single_record' => 0,
                'hidden' => 0,
                'parent_title' => null,
                'parent_icon' => null,
                'server_side_pagination'=>1,
                'is_form'=>1,
            ],
           
        ]);

        
        Schema::create('cf_messages', function ($table) {
            $table->increments('id');
            $table->string('full_name')->nullable();
            $table->string('email')->nullable();
            $table->text('message')->nullable();
            $table->string('subject')->nullable();
            $table->tinyInteger('published')->nullable();
            $table->tinyInteger('star')->nullable();
            $table->tinyInteger('read')->nullable();
            $table->integer('pos')->nullable();
            $table->timestamps();
        });

        DB::table('cf_messages')->insert([
            [
                'full_name'=>'John Doe',
                'email'=>'hello@cms.com',
                'subject'=>"Subject ...",
                'message'=>'Hello world'
            ]

        ]);
        
        Schema::create('home_pages', function ($table) {
            $table->increments('id');
            $table->string('title');
            $table->integer('pos');
            $table->timestamps();
        });

        // Create seo pages table
        Schema::create('home_pages_translations', function ($table) {
            $table->increments('id');
            $table->integer('home_page_id');
            $table->string('locale');
            $table->string('seo_title')->nullable();
            $table->string('seo_description')->nullable();
            $table->string('seo_keywords')->nullable();
            $table->text('seo_image')->nullable();
            $table->timestamps();
        });

        Schema::create('contact_pages', function ($table) {
            $table->increments('id');
            $table->string('send_form_messages_to');
            $table->integer('pos');
            $table->timestamps();
        });
      
        // Create seo pages table
        Schema::create('contact_pages_translations', function ($table) {
            $table->increments('id');
            $table->integer('contact_page_id');
            $table->string('locale');
            $table->string('seo_title')->nullable();
            $table->string('seo_description')->nullable();
            $table->string('seo_keywords')->nullable();
            $table->text('seo_image')->nullable();
            $table->timestamps();
        });


        Schema::create('site_settings', function ($table) {
            $table->increments('id');
            $table->text('instagram_url')->nullable();
            $table->text('phone_number')->nullable();
            $table->text('facebook_url')->nullable();
            $table->text('linkedin_url')->nullable();
            $table->text('twitter_url')->nullable();
            $table->integer('pos');
            $table->timestamps();
        });

        // Create seo pages table
        Schema::create('site_settings_translations', function ($table) {
            $table->increments('id');
            $table->integer('site_setting_id');
            $table->string('locale');
            $table->string('seo_title')->nullable();
            $table->string('seo_description')->nullable();
            $table->string('seo_keywords')->nullable();
            $table->text('seo_image')->nullable();
            $table->timestamps();
        });

        // Create languages table
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

        Schema::create('admin_role_permissions', function ($table) {
            $table->increments('id');
            $table->integer('admin_role_id')->unsigned();
            $table->integer('post_type_id')->unsigned();
            $table->integer('browse')->unsigned();
            $table->integer('read')->unsigned();
            $table->integer('edit')->unsigned();
            $table->integer('add')->unsigned();
            $table->integer('delete')->unsigned();
            $table->timestamps();

            $table->foreign('admin_role_id')->references('id')->on('admin_roles')->onDelete('cascade');
            $table->foreign('post_type_id')->references('id')->on('post_types')->onDelete('cascade');
        });


        DB::table('admin_role_permissions')->insert([
            [
                'admin_role_id'=>1,
                'post_type_id'=>2,
                'browse'=>1,
                'read'=>1,
                'edit'=>1,
                'add'=>1,
                'delete'=>1
            ],
            [
                'admin_role_id'=>1,
                'post_type_id'=>3,
                'browse'=>1,
                'read'=>1,
                'edit'=>1,
                'add'=>1,
                'delete'=>1
            ],
            [
                'admin_role_id'=>1,
                'post_type_id'=>4,
                'browse'=>1,
                'read'=>1,
                'edit'=>1,
                'add'=>1,
                'delete'=>1
            ],
            [
                'admin_role_id'=>1,
                'post_type_id'=>5,
                'browse'=>1,
                'read'=>1,
                'edit'=>1,
                'add'=>1,
                'delete'=>1
            ],
            [
                'admin_role_id'=>1,
                'post_type_id'=>5,
                'browse'=>1,
                'read'=>1,
                'edit'=>1,
                'add'=>1,
                'delete'=>1
            ],
            [
                'admin_role_id'=>1,
                'post_type_id'=>6,
                'browse'=>1,
                'read'=>1,
                'edit'=>1,
                'add'=>1,
                'delete'=>1
            ],
            [
                'admin_role_id'=>1,
                'post_type_id'=>7,
                'browse'=>1,
                'read'=>1,
                'edit'=>1,
                'add'=>1,
                'delete'=>1
            ],
            [
                'admin_role_id'=>1,
                'post_type_id'=>8,
                'browse'=>1,
                'read'=>1,
                'edit'=>0,
                'add'=>0,
                'delete'=>1
            ]
        ]);


    }
}
