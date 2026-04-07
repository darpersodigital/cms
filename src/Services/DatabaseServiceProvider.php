<?php

namespace Darpersodigital\Cms\Services;

use Artisan;
use Schema;
use Auth;
use DB;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Darpersodigital\Cms\Services\DatabaseHelperServices;

class DatabaseServiceProvider
{
    protected DatabaseHelperServices $db_helpers;

    public function __construct(DatabaseHelperServices $db_helpers)
    {
        $this->db_helpers = $db_helpers;
    }

    public function shouldRunInitialSetup($cmsConfig): bool
    {
        return !is_array($cmsConfig);
    }

    private function generateAdminDatabases()
    {
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
            $table->string('full_name')->nullable();
            $table->string('user_name')->unique();
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
            'full_name' => 'Admin Admin',
            'user_name' => 'admin',
            'email' => 'admin@admin.com',
            'password' => bcrypt('123456'),
            'admin_role_id' => 1,
        ]);

        DB::table('admins')->insert([
            'full_name' => 'Customer Customer',
            'user_name' => 'customer',
            'email' => 'customer@customer.com',
            'password' => bcrypt('123456'),
            'admin_role_id' => 2,
        ]);
    }

    protected function generatePostTypeDatabases()
    {
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
            $table->tinyInteger('show_dashboard')->default(0);
            $table->tinyInteger('hidden')->default(0);
            $table->tinyInteger('has_sitemap')->default(0);
            $table->tinyInteger('custom_page')->default(0);
            $table->tinyInteger('custom_crud')->default(0);
            $table->string('parent_title')->nullable();
            $table->string('parent_icon')->nullable();
            $table->integer('pos')->nullable();
            $table->timestamps();
        });

        DB::table('post_types')->insert([
            $this->db_helpers->createPostType('custom', [
                'icon' => 'fa-solid fa-table-columns',
                'display_name_plural' => 'Post Types',
                'route' => 'post-types',
                'hidden' => 1,
            ]),
            $this->db_helpers->createPostType('custom', [
                'icon' => 'fa-language',
                'display_name_plural' => 'Languages',
                'route' => 'languages',
                'hidden' => 1,
            ]),
            $this->db_helpers->createPostType('custom', [
                'icon' => 'fa-solid fa-sitemap',
                'display_name_plural' => 'Sitemaps',
                'route' => 'sitemaps',
                'hidden' => 1,
                'parent_icon' => 'fa-brands fa-searchengin',
                'parent_title' => 'SEO',
            ]),
            $this->db_helpers->createPostType('custom', [
                'icon' => 'fa-brands fa-bots',
                'display_name_plural' => 'Robots TXTs',
                'route' => 'robots-txts',
                'hidden' => 1,
                'parent_icon' => 'fa-brands fa-searchengin',
                'parent_title' => 'SEO',
            ]),
               $this->db_helpers->createPostType('custom', [
                'icon' => 'fa-solid fa-chart-simple',
                'display_name_plural' => 'Google Analytics',
                'route' => 'google-analytics',
                'hidden' => 1,
                'parent_icon' => 'fa-brands fa-searchengin',
                'parent_title' => 'SEO',
            ]),
            $this->db_helpers->createPostType('custom', [
                'icon' => 'fa-lock',
                'display_name_plural' => 'Admin Roles',
                'route' => 'admin-roles',
                'hidden' => 1,
                'parent_title' => 'Admins',
                'parent_icon' => 'fa-user-secret',
            ]),
            $this->db_helpers->createPostType('custom', [
                'icon' => 'fa-user-secret',
                'display_name_plural' => 'Admins',
                'route' => 'admins',
                'hidden' => 1,
                'parent_title' => 'Admins',
                'parent_icon' => 'fa-user-secret',
            ]),
            $this->db_helpers->createPostType('single', [
                'icon' => 'fa-solid fa-house-user',
                'display_name' => 'Home Page',
                'display_name_plural' => 'Home Pages',
                'database_table' => 'home_pages',
                'route' => 'home-pages',
                'model_name' => 'HomePage',
                'fields' => json_encode($this->db_helpers->generateDefaultSingleRecordData()),
                'translatable_fields' => json_encode($this->db_helpers->getSeoFields()),
            ]),
            $this->db_helpers->createPostType('single', [
                'icon' => 'fa-solid fa-message',
                'display_name' => 'Contact Page',
                'display_name_plural' => 'Contact Pages',
                'database_table' => 'contact_pages',
                'route' => 'contact-pages',
                'model_name' => 'ContactPage',
                'fields' => json_encode($this->db_helpers->generateDefaultSingleRecordData()),
                'translatable_fields' => json_encode($this->db_helpers->getSeoFields()),
            ]),
            $this->db_helpers->createPostType('single', [
                'icon' => 'fa-solid fa-gear',
                'display_name' => 'Site Setting',
                'display_name_plural' => 'Site Settings',
                'database_table' => 'site_settings',
                'route' => 'site-settings',
                'model_name' => 'SiteSetting',
                'fields' => json_encode([
                    $this->db_helpers->generateFormField('email', 'text', 'email', [
                        'nullable' => 1,
                    ]),
                    $this->db_helpers->generateFormField('instagram_url', 'text', 'text', ['nullable' => 1]),
                    $this->db_helpers->generateFormField('facebook_url', 'text', 'text', ['nullable' => 1]),
                    $this->db_helpers->generateFormField('twitter_url', 'text', 'text', ['nullable' => 1]),
                    $this->db_helpers->generateFormField('linkedin_url', 'text', 'text', ['nullable' => 1]),
                    $this->db_helpers->generateFormField('phone_number', 'string', 'text', ['nullable' => 1]),
                    $this->db_helpers->generateFormField('copyright_text', 'text', 'text', ['nullable' => 1]),
                    $this->db_helpers->generateFormField('send_form_messages_to', 'string', 'email', ['nullable' => 1]),
                ]),
                'translatable_fields' => json_encode($this->db_helpers->getSeoFields()),
            ]),
            $this->db_helpers->createPostType('form', [
                'icon' => 'fa-solid fa-envelopes-bulk',
                'display_name' => 'Contact Form Message',
                'display_name_plural' => 'Contact Form Messages',
                'database_table' => 'cf_messages',
                'route' => 'cf-message',
                'model_name' => 'CfMessage',
                'fields' => json_encode([
                    $this->db_helpers->generateFormField('full_name', 'string', 'text', [
                        'nullable' => 1,
                    ]),
                    $this->db_helpers->generateFormField('email', 'string', 'text', ['nullable' => 1]),
                    $this->db_helpers->generateFormField('subject', 'string', 'text', ['nullable' => 1]),
                    $this->db_helpers->generateFormField('message', 'text', 'text', ['nullable' => 1]),
                    $this->db_helpers->generateFormField('read', 'integer', 'checkbox', ['nullable' => 1]),
                    $this->db_helpers->generateFormField('star', 'integer', 'checkbox', ['nullable' => 1]),
                ]),
            ]),
        ]);
    }

    protected function generateDefaultWebsitePages()
    {
        // ------------------ START CONTACT FORM MESSAGES ------------------- //
        Schema::create('cf_messages', function ($table) {
            $table->increments('id');
            $table->string('full_name')->nullable();
            $table->string('email')->nullable();
            $table->text('message')->nullable();
            $table->string('subject')->nullable();
            $table->tinyInteger('star')->nullable();
            $table->tinyInteger('read')->nullable();
            $table->integer('pos')->nullable();
            $table->timestamps();
        });
        $base_cf_messages = [
            'full_name' => 'John Doe',
            'email' => 'hello@cms.com',
            'subject' => 'Subject ...',
            'message' => 'Hello world',
        ];
        DB::table('cf_messages')->insert([$base_cf_messages, $base_cf_messages, $base_cf_messages]);
        // ------------------ END CONTACT FORM MESSAGES ------------------- //

        // ------------------ START STATIC PAGES MESSAGES ------------------- //
        $this->db_helpers->createStaticPageWithTranslation('home_pages', 'home_page_id', 'Home Page');

        $this->db_helpers->createStaticPageWithTranslation('contact_pages', 'contact_page_id', 'Contact Page');
        // ------------------ END STATIC PAGES MESSAGES ------------------- //

        // ------------------ START SITE SETTINGS ------------------- //
        Schema::create('site_settings', function ($table) {
            $table->increments('id');
            $table->text('email')->nullable();
            $table->string('send_form_messages_to')->nullable();
            $table->text('instagram_url')->nullable();
            $table->text('phone_number')->nullable();
            $table->text('facebook_url')->nullable();
            $table->text('linkedin_url')->nullable();
            $table->text('twitter_url')->nullable();
            $table->text('copyright_text')->nullable();
            $table->integer('pos')->default(0);
            $table->timestamps();
        });
        $this->db_helpers->createSeoTranslationTable('site_settings_translations', 'site_setting_id', 'site_settings');
        $siteSettingId = DB::table('site_settings')->insertGetId([]);
        $this->db_helpers->insertDefaultTranslation('site_settings_translations', 'site_setting_id', $siteSettingId);
        // ------------------ END SITE SETTINGS ------------------- //
    }

    public function generateSEODatabase()
    {
        Schema::create('sitemaps', function (Blueprint $table) {
            $table->increments('id');
            $table->string('url')->nullable();
            $table->string('locale')->nullable();
            $table->dateTime('last_modified')->nullable();
            $table->string('change_frequency')->nullable();
            $table->decimal('priority')->nullable();
            $table->boolean('published')->default(true);
            $table->integer('post_type_id')->unsigned()->nullable();
            $table->integer('post_type_children_id')->unsigned()->nullable();
            $table->timestamps();
            $table->foreign('post_type_id')->references('id')->on('post_types')->onDelete('cascade');
            $table->foreign('post_type_children_id')->references('id')->on('post_types')->onDelete('cascade');
        });

        Schema::create('robots_txts', function (Blueprint $table) {
            $table->increments('id');
            $table->longtext('content')->nullable();
            $table->timestamps();
        });
        DB::table('robots_txts')->insert([
            'content' => <<<TXT
            User-agent: *
            Disallow: /admin/
            Sitemap: https://example.com/sitemap.xml
            TXT,
        ]);

        Schema::create('google_analytics', function (Blueprint $table) {
            $table->increments('id');
            $table->string('property_id')->nullable();
            $table->integer('cache_lifetime_in_minutes')->nullable();
            $table->text('service_account_credentials_json')->nullable();
            $table->timestamps();
        });
    }

    public function generateAdminRolePermissionsDatabase()
    {
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
            $this->db_helpers->generatePermissions(1, 2),
            $this->db_helpers->generatePermissions(1, 3),
            $this->db_helpers->generatePermissions(1, 4),
            $this->db_helpers->generatePermissions(1, 5),
            $this->db_helpers->generatePermissions(1, 6),
            $this->db_helpers->generatePermissions(1, 7),
            $this->db_helpers->generatePermissions(1, 8),
            $this->db_helpers->generatePermissions(1, 9),
            $this->db_helpers->generatePermissions(1, 10, [
                'edit' => 0,
                'add' => 0,
            ]),
        ]);
    }

    public function generateDatabase()
    {
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
        } catch (Exception $e) {
        }

        Schema::dropIfExists('admin_role_permissions');
        Schema::dropIfExists('admins');
        Schema::dropIfExists('admin_roles');
        Schema::dropIfExists('sitemaps');
        Schema::dropIfExists('google_analytics');
        Schema::dropIfExists('robots_txts');
        Schema::dropIfExists('post_types');
        Schema::dropIfExists('cf_messages');
        Schema::dropIfExists('home_pages_translations');
        Schema::dropIfExists('home_pages');
        Schema::dropIfExists('contact_pages_translations');
        Schema::dropIfExists('contact_pages');
        Schema::dropIfExists('site_settings_translations');
        Schema::dropIfExists('site_settings');
        Schema::dropIfExists('languages');

        $this->generateAdminDatabases();
        $this->generatePostTypeDatabases();
        $this->generateSEODatabase();
        $this->generateDefaultWebsitePages();

        $this->db_helpers->generateLanguagesTable();

        $this->generateAdminRolePermissionsDatabase();
    }
}
