<h1>Installation:</h1>
<ul>
	<li>Create the database and update your .env file</li>
	<li>
		Add the below to config/auth.php:
		<pre>
            'guards' => [
                'admin' => [
                    'driver' => 'session',
                    'provider' => 'admins',
                ],
            ],
            'providers' => [
                'admins' => [
                    'driver' => 'eloquent',
                    'model' => Darpersodigital\Cms\Models\Admin::class,
                ],
            ],
		</pre>
	</li>
    <li>
		Change in filesystem disks
		<pre>
          'local' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => env('APP_URL').'/storage',
            'visibility' => 'public',
            'throw' => false,
        ],
		</pre>
	</li>
	<li>
		Run:
		<pre>composer require darpersodigital/cms</pre>
	</li>
</ul>