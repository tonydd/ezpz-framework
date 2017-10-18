<?php
/**
 * Created by PhpStorm.
 * User: tony
 * Date: 14/10/17
 * Time: 12:20
 */

define('ROOTPATH', __DIR__);
include 'autoload/autoload.php';

// Creer le cache
Recette::load(1);
Ingredient::load(1);