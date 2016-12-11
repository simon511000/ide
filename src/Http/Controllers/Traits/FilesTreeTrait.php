<?php namespace WebEd\Plugins\IDE\Http\Controllers\Traits;

use Exception;

trait FilesTreeTrait
{
    protected function real($path)
    {
        $temp = realpath($path);

        if (!\File::exists($temp) && !\File::isDirectory($temp)) {
            throw new Exception ('Path does not exist: ' . $path);
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
        $lst = scan_folder($dir);
        if (!$lst) {
            throw new Exception ('Could not list path: ' . $dir);
        }
        $res = array();
        foreach ($lst as $item) {
            if ($item == '.' || $item == '..' || $item === null) {
                continue;
            }
            $tmp = preg_match('([^ a-zа-я-_0-9.]+)ui', $item);
            if ($tmp === false || $tmp === 1) {
                continue;
            }
            if (is_dir($dir . DIRECTORY_SEPARATOR . $item)) {
                $res[] = [
                    'text' => $item,
                    'children' => true,
                    'id' => $this->id($dir . DIRECTORY_SEPARATOR . $item),
                    'icon' => 'fa fa-folder'
                ];
            } else {
                $res[] = [
                    'text' => $item,
                    'children' => false,
                    'id' => $this->id($dir . DIRECTORY_SEPARATOR . $item),
                    'type' => 'file',
                    'icon' => 'fa fa-file fa-file-' . substr($item, strrpos($item, '.') + 1)
                ];
            }

        }
        if ($with_root && $this->id($dir) === '/') {
            $res = [
                [
                    'text' => basename($this->rootFolder),
                    'children' => $res,
                    'id' => '/',
                    'icon' => 'fa fa-folder',
                    'state' => [
                        'opened' => true,
                        'disabled' => true
                    ]
                ]
            ];

        }
        return $res;
    }

    public function data($id)
    {
        if (strpos($id, ":")) {
            $id = array_map(
                array(
                    $this, 'id'
                ),
                explode(':', $id)
            );
            return [
                'type' => 'multiple',
                'content' => 'Multiple selected: ' . implode(' ', $id)
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
            $dat = [
                'type' => $ext,
                'content' => ''
            ];
            switch ($ext) {
                case 'txt':
                case 'text':
                case 'md':
                case 'js':
                case 'json':
                case 'css':
                case 'html':
                case 'htm':
                case 'yml':
                case 'xml':
                case 'c':
                case 'cpp':
                case 'h':
                case 'sql':
                case 'log':
                case 'py':
                case 'rb':
                case 'htaccess':
                case 'php':
                    $dat['content'] = file_get_contents($dir);
                    break;
                default:
                    $dat['content'] = 'File not recognized: ' . $this->id($dir);
                    break;
            }
            return $dat;
        }
        throw new Exception ('Not a valid selection: ' . $dir);
    }

    public function create($id, $name, $mkdir = false)
    {
        $dir = $this->path($id);
        if (preg_match('([^ a-zа-я-_0-9.]+)ui', $name) || !strlen($name)) {
            throw new Exception ('Invalid name: ' . $name);
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
            throw new Exception ('Cannot rename root');
        }
        if (preg_match('([^ a-zа-я-_0-9.]+)ui', $name) || !strlen($name)) {
            throw new Exception ('Invalid name: ' . $name);
        }
        $new = explode(DIRECTORY_SEPARATOR, $dir);
        array_pop($new);
        array_push($new, $name);
        $new = implode(DIRECTORY_SEPARATOR, $new);
        if ($dir !== $new) {
            if (is_file($new) || is_dir($new)) {
                throw new Exception ('Path already exists: ' . $new);
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
            throw new Exception ('Cannot remove root');
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
            throw new Exception ('Path already exists: ' . $new);
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
