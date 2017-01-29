<?php namespace WebEd\Plugins\IDE\Http\Controllers;

use WebEd\Base\Core\Http\Controllers\BaseAdminController;
use WebEd\Plugins\IDE\Http\Controllers\Traits\FilesTreeTrait;

class IDEController extends BaseAdminController
{
    use FilesTreeTrait;

    protected $module = 'webed-ide';

    protected $editableExt = [
        'txt', 'text',
        'md',
        'js', 'json', 'jsx',
        'css', 'less', 'sass', 'scss',
        'html', 'htm', 'yml', 'xml',
        'c', 'cpp', 'py', 'h', 'rb', 'php',
        'sql', 'log',
        'htaccess',
    ];

    protected $rootFolder;

    public function __construct()
    {
        parent::__construct();

        $this->getDashboardMenu($this->module);
        $this->setPageTitle('Code editor');

        $this->rootFolder = config('webed-ide.root_folder');
    }

    public function getIndex()
    {
        return $this->viewAdmin('index');
    }

    public function getEditor()
    {
        return $this->viewAdmin('editor');
    }

    public function getFileTree()
    {
        $result = null;
        $node = $this->request->get('id', '/');
        if($node === '#') {
            $node = '/';
        }

        switch ($this->request->get('operation')) {
            case 'get_node':
                $withRoot = ($this->request->get('id', '/') === '#');
                $result = $this->lst($node, $withRoot);
                break;
            case "get_content":
                $result = $this->data($node);
                break;
            case 'create_node':
                $result = $this->create($node, $this->request->get('text', ''), $this->request->get('type') !== 'file');
                break;
            case 'rename_node':
                $result = $this->rename($node, $this->request->get('text', ''));
                break;
            case 'delete_node':
                $result = $this->remove($node);
                break;
            case 'move_node':
                $parent = $this->request->get('parent', '/') !== '#' ? $this->request->get('parent', '/') : '/';
                $result = $this->move($node, $parent);
                break;
            case 'copy_node':
                $parent = $this->request->get('parent', '/') !== '#' ? $this->request->get('parent', '/') : '/';
                $result = $this->copy($node, $parent);
                break;
            default:
                return 'Unsupported operation...';
                break;
        }

        return response()->json($result);
    }

    public function postSave()
    {
        $file = $this->path($this->request->get('file'));
        $content = $this->request->get('contents');
        $ext = strpos($file, '.') !== FALSE ? substr($file, strrpos($file, '.') + 1) : '';

        if (!in_array($ext, $this->editableExt)) {
            return response()->json(response_with_messages('File not supported', true, \Constants::ERROR_CODE));
        }

        $result = save_file_data($file, $content);
        if ($result === true) {
            return response()->json(response_with_messages('File save completed', false, \Constants::SUCCESS_NO_CONTENT_CODE));
        }
        return response()->json(response_with_messages($result, true, \Constants::ERROR_CODE));
    }
}
