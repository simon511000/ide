<?php namespace WebEd\Plugins\IDE\Http\Middleware;

use \Closure;

class BootstrapModuleMiddleware
{
    public function __construct()
    {

    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @param  array|string $params
     * @return mixed
     */
    public function handle($request, Closure $next, ...$params)
    {
        /**
         * Register to dashboard menu
         */
        dashboard_menu()->registerItem([
            'id' => 'webed-ide',
            'priority' => 999.1,
            'parent_id' => null,
            'heading' => null,
            'title' => trans('webed-ide::base.admin_menu.title'),
            'font_icon' => 'fa fa-code',
            'link' => route('admin::webed-ide.index.get'),
            'css_class' => null,
            'permissions' => ['modify-code-directly'],
        ]);

        return $next($request);
    }
}
