<?php

namespace Teescripts\RptForms;

use Illuminate\Support\ServiceProvider;

class RptFormServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Register package services
        // Register any application services or bindings.
        $this->mergeConfigFrom(
            __DIR__.'/../install/config.php', 'forms'
        );
    }

    public function boot()
    {
        // Bootstrap any package services
		$this->publishes([
            __DIR__.'/../install/lang' => lang_path('extra/forms'),
			__DIR__.'/../install/lists.php' => app_path('Models/rptLists.php'),
			__DIR__.'/../install/controllers' => app_path('Http'),
			__DIR__.'/../install/views' => resource_path('views/rpt-forms'),
			__DIR__.'/../install/assets' => public_path('rpt-forms/assets'),
			__DIR__.'/../install/config/forms.php' => config_path('forms.php')
		]);

        // Load routes, migrations, and views if applicable
        $this->loadRoutesFrom(__DIR__.'/../install/routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../install/views', 'rpt-forms');
        $this->loadMigrationsFrom(__DIR__.'/../install/migrations');

    }

}

/*
php artisan vendor:publish --provider="Teescripts\RptForms\RptFormServiceProvider"
php artisan make:provider RptFormServiceProvider

php artisan vendor:publish -tag=rpt-forms-config

git init
git add .
git commit -m "Initial commit"
git remote add origin https://github.com/teescripts/Rapid-Prototyping-Forms-Laravel
git push -u origin master

git push -u origin --all
git push -u origin --tags

git tag 1.0.0
git push origin 1.0.0

"packages/teescripts/rpt/src/base.php", 
"packages/teescripts/rpt/src/main.php", 
"packages/teescripts/rpt/src/template.php", 
"packages/teescripts/rpt/src/layout.php", 
"packages/teescripts/rpt/src/table.php", 
"packages/teescripts/rpt/src/form.php", 
"packages/teescripts/rpt/src/render.php"
*/