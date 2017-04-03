<?php namespace WebEd\Plugins\IDE\Http\Controllers\Traits;

use Exception;

trait FilesTreeTrait
{
    protected function real($path)
    {
        $temp = realpath($path);

        if (!\File::exists($temp) && !\File::isDirectory($temp)) {
            throw new Exception (trans('webed-ide::base.path_does_not_exists') . ': ' . $path);
        }
        return $temp;
    }

    protected function path($id)
    {
        $id = str_replace('/', DIRECTORY_SEPARATOR, $id);
        $id = trim($id, DIRECTORY_SEPARATOR);
        $id = $this->real($this->rootFolder . DIRECTORY_SEPARATOR . $id);
        return $id;
    }

    protected function id($path)
    {
        $path = $this->real($path);
        $path = substr($path, strlen($this->rootFolder));
        $path = str_replace(DIRECTORY_SEPARATOR, '/', $path);
        $path = trim($path, '/');
        return strlen($path) ? $path : '/';
    }

    public function lst($id, $with_root = true)
    {
        $dir = $this->path($id);
        $list = scan_folder($dir);
        if (!$list) {
            return [];
        }
        $result = [];
        foreach ($list as $item) {
            if ($item == '.' || $item == '..' || $item === null) {
                continue;
            }
            $tmp = preg_match('([^ a-zа-я-_0-9.]+)ui', $item);
            if ($tmp === false || $tmp === 1) {
                continue;
            }
            if (is_dir($dir . DIRECTORY_SEPARATOR . $item)) {
                $result[] = [
                    'text' => $item,
                    'children' => true,
                    'id' => $this->id($dir . DIRECTORY_SEPARATOR . $item),
                    'icon' => 'fa fa-folder'
                ];
            } else {
                $result[] = [
                    'text' => $item,
                    'children' => false,
                    'id' => $this->id($dir . DIRECTORY_SEPARATOR . $item),
                    'type' => 'file',
                    'icon' => 'fa fa-file fa-file-' . substr($item, strrpos($item, '.') + 1)
                ];
            }

        }
        if ($with_root && $this->id($dir) === '/') {
            $result = [
                [
                    'text' => basename($this->rootFolder),
                    'children' => $result,
                    'id' => '/',
                    'icon' => 'fa fa-folder',
                    'state' => [
                        'opened' => true,
                        'disabled' => true
                    ]
                ]
            ];

        }
        return $result;
    }

    public function data($id)
    {
        if (strpos($id, ':')) {
            $id = array_map(
                array(
                    $this, 'id'
                ),
                explode(':', $id)
            );
            return [
                'type' => 'multiple',
                'content' => trans('webed-ide::base.multiple_selected') . ': ' . implode(' ', $id)
            ];
        }
        $dir = $this->path($id);
        if (is_dir($dir)) {
            return [
                'type' => 'folder',
                'content' => $id
            ];
        }

        if (is_file($dir)) {
            $ext = strpos($dir, '.') !== FALSE ? substr($dir, strrpos($dir, '.') + 1) : '';
            $data = [
                'type' => $ext,
                'content' => ''
            ];
            if (in_array($ext, config('webed-ide.accepted_types'))) {
                $data['content'] = file_get_contents($dir);
            } else {
                $data['content'] = trans('webed-ide::base.file_not_recognized') . ': ' . $this->id($dir);
            }
            return $data;
        }
        throw new Exception (trans('webed-ide::base.not_a_valid_selection') . ': ' . $dir);
    }

    public function create($id, $name, $mkdir = false)
    {
        $dir = $this->path($id);
        if (preg_match('([^ a-zа-я-_0-9.]+)ui', $name) || !strlen($name)) {
            throw new Exception (trans('webed-ide::base.invalid_name') . ': ' . $name);
        }
        if ($mkdir) {
            mkdir($dir . DIRECTORY_SEPARATOR . $name);
        } else {
            file_put_contents($dir . DIRECTORY_SEPARATOR . $name, '');
        }
        return array(
            'id' => $this->id($dir . DIRECTORY_SEPARATOR . $name)
        );
    }

    public function rename($id, $name)
    {
        $dir = $this->path($id);
        if ($dir === $this->rootFolder) {
            throw new Exception (trans('webed-ide::base.cannot_rename_root'));
        }
        if (preg_match('([^ a-zа-я-_0-9.]+)ui', $name) || !strlen($name)) {
            throw new Exception (trans('webed-ide::base.invalid_name') . ': ' . $name);
        }
        $new = explode(DIRECTORY_SEPARATOR, $dir);
        array_pop($new);
        array_push($new, $name);
        $new = implode(DIRECTORY_SEPARATOR, $new);
        if ($dir !== $new) {
            if (is_file($new) || is_dir($new)) {
                throw new Exception (trans('webed-ide::base.path_already_exists') . ': ' . $new);
            }
            rename($dir, $new);
        }
        return array(
            'id' => $this->id($new)
        );
    }

    public function remove($id)
    {
        $dir = $this->path($id);
        if ($dir === $this->rootFolder) {
            throw new Exception (trans('webed-ide::base.cannot_remove_root'));
        }
        if (is_dir($dir)) {
            foreach (scan_folder($dir) as $f) {
                $this->remove($this->id($dir . DIRECTORY_SEPARATOR . $f));
            }
            rmdir($dir);
        }
        if (is_file($dir)) {
            unlink($dir);
        }
        return [
            'status' => 'OK'
        ];
    }

    public function move($id, $par)
    {
        $dir = $this->path($id);
        $par = $this->path($par);
        $new = explode(DIRECTORY_SEPARATOR, $dir);
        $new = array_pop($new);
        $new = $par . DIRECTORY_SEPARATOR . $new;
        rename($dir, $new);
        return [
            'id' => $this->id($new)
        ];
    }

    public function copy($id, $par)
    {
        $dir = $this->path($id);
        $par = $this->path($par);
        $new = explode(DIRECTORY_SEPARATOR, $dir);
        $new = array_pop($new);
        $new = $par . DIRECTORY_SEPARATOR . $new;
        if (is_file($new) || is_dir($new)) {
            throw new Exception (trans('webed-ide::base.path_already_exists') . ': ' . $new);
        }

        if (is_dir($dir)) {
            mkdir($new);
            foreach (scan_folder($dir) as $f) {
                $this->copy($this->id($dir . DIRECTORY_SEPARATOR . $f), $this->id($new));
            }
        }

        if (is_file($dir)) {
            copy($dir, $new);
        }
        return [
            'id' => $this->id($new)
        ];
    }
}
