<?php

namespace Darpersodigital\Cms\Controllers;

use Illuminate\Http\Request;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\RedirectResponse;
use Darpersodigital\Cms\Models\PostType;
use Darpersodigital\Cms\Models\AdminRolePermission;
use Illuminate\Validation\ValidationException;

use Illuminate\Support\Facades\File;

class CustomCRUDController extends BaseController
{

    public function createCustomCRUDController($post_type)
    {
        $controllerPath = app_path('/Http/Controllers//' . $post_type['display_name_plural'] . 'Controller.php');
        $crudViewDirectory = resource_path('views/cms/' . $post_type['route']);
        $packageViewDirectory = dirname(__DIR__) . '/resources/views/cms/post-type';

        if (!file_exists($controllerPath)) {
            $replacements = [$post_type['display_name_plural'], $post_type['route']];
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
