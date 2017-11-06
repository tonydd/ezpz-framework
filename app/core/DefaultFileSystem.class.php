<?php
/**
 * Created by PhpStorm.
 * User: tony
 * Date: 05/11/17
 * Time: 16:50
 */

class DefaultFileSystem implements FS
{
    public function file_exists(string $path)
    {
        return file_exists($path);
    }

    public function scandir(string $dir, bool $keepParents = false)
    {
        $scan = scandir($dir);
        if (!$keepParents) {
            unset($scan[0], $scan[1]); //unset . and ..
        }
        return $scan;
    }

    public function is_dir(string $dir)
    {
        return is_dir($dir);
    }
}