<?php

/**
 * Grenada
 *
 * A very lightweight CMS
 */

ob_start();

$execStartTime = microtime(true);

define('BASE', realpath(__DIR__).'/');
define('CORE', BASE.'core/');
define('MODULES', BASE.'modules/');

require_once(CORE.'core.php');

?>
