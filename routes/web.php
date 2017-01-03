<?php use Illuminate\Routing\Router;

/**
 *
 * @var Router $router
 *
 */
/**
 * Admin routes
 */
$adminRoute = config('webed.admin_route');

$moduleRoute = 'webed-ide';

/**
 * Only super admin can fuck this module
 */
$router->group(['prefix' => $adminRoute . '/' . $moduleRoute, 'middleware' => 'has-permission:modify-code-directly'], function (Router $router) use ($adminRoute, $moduleRoute) {
    /**
     *
     * Put some route here
     *
     */
    $router->get('/', 'IDEController@getIndex')
        ->name('admin::webed-ide.index.get');

    $router->get('/editor', 'IDEController@getEditor')
        ->name('admin::webed-ide.editor.get');

    $router->get('/files-tree', 'IDEController@getFileTree')
        ->name('admin::webed-ide.file-tree.get');

    $router->post('/save', 'IDEController@postSave')
        ->name('admin::webed-ide.save.post');
});
