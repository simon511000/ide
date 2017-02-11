<?php namespace WebEd\Plugins\IDE\Providers;

use Illuminate\Support\ServiceProvider;

class BootstrapModuleServiceProvider extends ServiceProvider
{
    protected $module = 'WebEd\Plugins\IDE';

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        app()->booted(function () {
            $this->booted();
        });
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {

    }

    private function booted()
    {
        /**
         * Register to dashboard menu
         */
        \DashboardMenu::registerItem([
            'id' => 'webed-ide',
            'priority' => 999.1,
            'parent_id' => null,
            'heading' => null,
            'title' => 'Code editor',
            'font_icon' => 'fa fa-code',
            'link' => route('admin::webed-ide.index.get'),
            'css_class' => null,
            'permissions' => ['modify-code-directly'],
        ]);
    }
}
