<?php

namespace Darpersodigital\Cms\Controllers;

use Illuminate\Http\Request;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\RedirectResponse;
use Darpersodigital\Cms\Models\PostType;
use Darpersodigital\Cms\Models\AdminRolePermission;
use Illuminate\Validation\ValidationException;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class CustomCRUDController extends BaseController
{

   
    private function controllerName($display_name_plural)
    {
        $name = Str::studly(preg_replace('/[^a-zA-Z0-9]+/', ' ', (string) $display_name_plural));
        $name = preg_replace('/[^a-zA-Z0-9]/', '', $name);

        if ($name === '' || is_numeric(substr($name, 0, 1))) {
            $name = 'PostType' . $name;
        }

        return $name;
    }

    public function createCustomCRUDController($post_type)
    {
        $controllerName = $this->controllerName($post_type['display_name_plural']);
        $controllerPath = app_path('Http/Controllers/' . $controllerName . 'Controller.php');
        $crudViewDirectory = resource_path('views/cms/' . $post_type['route']);
        $packageViewDirectory = dirname(__DIR__) . '/resources/views/cms/post-type';

        if (!file_exists($controllerPath)) {
            $replacements = [$controllerName, $post_type['route']];
            $fileContent = str_replace(['%%controller_name%%', '%%route%%'], $replacements, file_get_contents(__DIR__ . '/constants/CustomCRUDController.stub'));
            file_put_contents($controllerPath, $fileContent);
        }

        if (!File::isDirectory($crudViewDirectory)) {
            File::makeDirectory($crudViewDirectory, 0755, true);
        }

        $viewsToCopy = ['index', 'form', 'show'];
        foreach ($viewsToCopy as $view) {
            $sourcePath = $packageViewDirectory . '/' . $view . '.blade.php';
            $destinationPath = $crudViewDirectory . '/' . $view . '.blade.php';

            if (File::exists($sourcePath) && !File::exists($destinationPath)) {
                File::copy($sourcePath, $destinationPath);
            }
        }
    }
}
