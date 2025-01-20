<?php

namespace Teescripts\RptForms;

use Illuminate\Support\ServiceProvider;

class RptFormServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Register package services
    }

    public function boot()
    {
        // Bootstrap any package services
		$this->publishes([
			__DIR__.'/config/forms.php' => config_path('forms.php'),
			//__DIR__.'/resources/views' => resource_path('views/vendor/rpt-forms'),
            __DIR__.'/config/lang' => $this->app->langPath('extra/forms'),
		]);

        //include __DIR__.’/routes.php’;
    }
}

/*

php artisan vendor:publish --provider="Teescripts\RptForms\RptFormServiceProvider"
php artisan make:provider RptFormServiceProvider

git init
git add .
git commit -m "Initial commit"
git remote add origin https://github.com/teescripts/Rapid-Prototyping-Forms-Laravel
git push -u origin master

git push -u origin --all
git push -u origin --tags

,

"packages/teescripts/rpt/dist/base.php", 
"packages/teescripts/rpt/dist/main.php", 
"packages/teescripts/rpt/dist/template.php", 
"packages/teescripts/rpt/dist/layout.php", 
"packages/teescripts/rpt/dist/table.php", 
"packages/teescripts/rpt/dist/form.php", 
"packages/teescripts/rpt/dist/render.php"
*/