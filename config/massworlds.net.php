<?php

/**
 * Mass Worlds configuration file
 */

define('DEBUG', true);
define('STATS', true);

define('SITE', 'www.massworlds.net');

Template::addConstructorHook('massworldsTemplateConstructorHook');
Template::addPageSearchPath(MODULES.'massworlds/templates/');

Template::$strictMode = true;

Modules::addModule('MassWorldsModule', Modules::WH_START);

function massworldsTemplateConstructorHook(Template $t)
{
    $t->DEBUG = DEBUG;
    $t->STATS = STATS;
    $t->CSS   = URL_ROOT.'content/css/';
    $t->title = 'Mass Worlds';
    $t->test  = 5;
}

?>
