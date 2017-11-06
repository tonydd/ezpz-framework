<?php
/**
 * Created by PhpStorm.
 * User: tony
 * Date: 05/11/17
 * Time: 16:43
 */

/**
 * Class FS (FileSystem)
 */
class CachedFileSystem implements FS
{
    const FILE  = 'file';
    const DIR   = 'dir';

    /** @var array  */
    private $_fsReflection = [];

    /** @var  DefaultFileSystem */
    private $_dfs;

    public function __construct()
    {
        $this->_dfs = new DefaultFileSystem();
        $this->_buildFileSystem();
    }

    private function _buildFileSystem(string $directory, &$current = [])
    {
        $files = [];

        if ($directory === null) {
            $directory = ROOTPATH;
        }

        if (empty($current)) {
            $current = [
                'type'      => self::DIR,
                'name'      => 'root',
                'content'   => []
            ];
        }

        if ($this->_dfs->is_dir($directory)) {
            foreach ($this->_dfs->scandir($directory) as $fileOrDir) {
                $path = $directory . FS::SEP . $fileOrDir;

                if ($this->_dfs->is_dir($path)) {

                    $data = [
                        'type'      => self::DIR,
                        'name'      => $fileOrDir,
                        'content'   => []
                    ];

                    $this->_buildFileSystem($path, $data);
                    $current['content'] = $data;

                }
                else {
                    $data = [
                        'type'  => self::FILE,
                        'name'  => $fileOrDir
                    ];
                }
            }
        }

        return $current;
    }

    private function _getDirContent()
    {

    }

    public function file_exists(string $path)
    {
        // TODO: Implement file_exists() method.
    }

    public function scandir(string $dir)
    {
        // TODO: Implement scandir() method.
    }

    public function is_dir(string $dir)
    {
        // TODO: Implement is_dir() method.
    }
}