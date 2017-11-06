<?php
/**
 * Created by PhpStorm.
 * User: tony
 * Date: 05/11/17
 * Time: 16:44
 */

/**
 * Interface FS (File System Interface)
 */
interface FS
{
    /** @var string : Directory separator */
    const SEP = DIRECTORY_SEPARATOR;

    /**
     * @param string $dir
     * @param bool $keepParents
     * @return array
     */
    public function scandir(string $dir, bool $keepParents = false);

    /**
     * @param string $dir
     * @return bool
     */
    public function is_dir(string $dir);

    /**
     * @param string $path
     * @return bool
     */
    public function file_exists(string $path);
}