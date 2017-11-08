<?php
/**
 * Created by PhpStorm.
 * User: tony
 * Date: 05/11/17
 * Time: 16:50
 */

class DefaultFileSystem implements FS
{
    /**
     * @param string $path
     * @return bool
     */
    public function file_exists(string $path)
    {
        return file_exists($path);
    }

    /**
     * @param string $dir
     * @param bool $keepParents
     * @return array
     */
    public function scandir(string $dir, bool $keepParents = false)
    {
        $scan = scandir($dir);
        if (!$keepParents) {
            unset($scan[0], $scan[1]); //unset . and ..
        }
        return $scan;
    }

    /**
     * @param string $dir
     * @return bool
     */
    public function is_dir(string $dir)
    {
        return is_dir($dir);
    }
}