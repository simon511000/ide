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

        $this->rootFolder = base_path();
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
        $rslt = null;
        $node = $this->request->get('id', '/');
        if($node === '#') {
            $node = '/';
        }

        switch ($this->request->get('operation')) {
            case 'get_node':
                $withRoot = ($this->request->get('id', '/') === '#');
                $rslt = $this->lst($node, $withRoot);
                break;
            case "get_content":
                $rslt = $this->data($node);
                break;
            case 'create_node':
                $rslt = $this->create($node, isset ($_GET['text']) ? $_GET['text'] : '', (!isset ($_GET['type']) || $_GET['type'] !== 'file'));
                break;
            case 'rename_node':
                $rslt = $this->rename($node, isset ($_GET['text']) ? $_GET['text'] : '');
                break;
            case 'delete_node':
                $rslt = $this->remove($node);
                break;
            case 'move_node':
                $parn = $this->request->get('parent', '/') !== '#' ? $this->request->get('parent', '/') : '/';
                $rslt = $this->move($node, $parn);
                break;
            case 'copy_node':
                $parn = $this->request->get('parent', '/') !== '#' ? $this->request->get('parent', '/') : '/';
                $rslt = $this->copy($node, $parn);
                break;
            default:
                return 'Unsupported operation...';
                break;
        }

        return response()->json($rslt);
    }

    public function postSave()
    {
        $file = $this->path($this->request->get('file'));
        $content = $this->request->get('contents');
        $ext = strpos($file, '.') !== FALSE ? substr($file, strrpos($file, '.') + 1) : '';

        if (!in_array($ext, $this->editableExt)) {
            return response()->json(response_with_messages('File not supported', true, ERROR_CODE));
        }

        $result = save_file_data($file, $content);
        if ($result === true) {
            return response()->json(response_with_messages('File save completed', false, SUCCESS_NO_CONTENT_CODE));
        }
        return response()->json(response_with_messages($result, true, ERROR_CODE));
    }
}
