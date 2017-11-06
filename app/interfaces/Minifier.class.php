<?php
/**
 * Created by PhpStorm.
 * User: tony
 * Date: 05/11/17
 * Time: 16:51
 */

interface Minifier
{
    /**
     * @param string $cssSource
     * @return string
     */
    function minifyCSS(string $cssSource);

    /**
     * @param string $jsSource
     * @return string
     */
    function minifyJS(string $jsSource);
}