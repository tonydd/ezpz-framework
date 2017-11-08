<?php
/**
 * Created by PhpStorm.
 * User: tony
 * Date: 08/10/17
 * Time: 18:42
 */

spl_autoload_register('ez_autoload');

function ez_autoload($className) {

    $fileName = "$className.class.php";


    if (load_classphp(ROOTPATH . '/code', $fileName)) {
        // -- Look first in code directory
        return;
    }
    else if (load_classphp(ROOTPATH . '/app', $fileName)) {
        // -- Look then in app
        return;
    }
    else {
        trigger_error("$className was not found in project", E_USER_WARNING);
    }

}

function load_classphp($directory, $target) {
    $excludedDirs = array('views', 'static');

    if(is_dir($directory)) {
        $scan = scandir($directory);
        unset($scan[0], $scan[1]); //unset . and ..
        foreach($scan as $file) {
            if(is_dir($directory."/".$file) && !in_array($file, $excludedDirs)) {
                if(load_classphp($directory."/".$file, $target)) {
                    return true;
                }
            } else {
                if($file === $target) {
                    include_once($directory."/".$file);
                    return true;
                }
            }
        }
    }

    return false;
}


// -- Always loaded files
//require_once '../func/func.php';