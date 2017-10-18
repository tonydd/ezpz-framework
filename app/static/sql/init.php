<?php
/**
 * Instanciate database
 *
 *
 */

define('ROOTPATH', str_replace('/app/static/sql', '', __DIR__));
include_once ROOTPATH . '/app/static/autoload/autoload.php';

$db = PDOHelper::getInstance();

/*
 * Table user
 */
echo "----- Create table User" . PHP_EOL;
$createTableUser = "CREATE TABLE user (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `nom` VARCHAR(255),
  `mail` VARCHAR(255),
	`login` VARCHAR(255),
  `pwd` VARCHAR(255),
  `token` VARCHAR(255),
  `token_expiration` DATETIME,
	`admin` TINYINT(1),

  PRIMARY KEY (`id`),
	INDEX (`mail`),
	INDEX (`login`)
)";
$db->exec($createTableUser);

/*
 * Default root user
 */
echo "----- Create root User" . PHP_EOL;
$rootMail = $db->quote(password_hash("azerty", PASSWORD_DEFAULT));
$rootDefaultUser = "INSERT INTO user (nom, mail, login, pwd, token, token_expiration, admin)
  VALUES ('Admin', 'admin@site.local', 'adm',$rootMail, NULL, NULL, 1)";
$db->exec($rootDefaultUser);

/*
 * Default cache flat table
 */
echo "----- Create table core_cache" . PHP_EOL;
$cacheTable = "CREATE TABLE core_cache (
  `key`   VARCHAR(100),
  `value` LONGTEXT,
  `ttl`   INT(11),

  PRIMARY KEY (`key`)
)";
$db->exec($cacheTable);


echo "----- DONE" . PHP_EOL;
