<?php
/* 
 * Controls initialization and runs a given page
 */

// Include a set of base files that will be required in other files
foreach ((array(
    CORE.'Processable.php',
    CORE.'Singleton.php',
    CORE.'Module.php',
    CORE.'Modules.php',
    CORE.'AutoLoader.php',
)) as $include) {
    require_once($include);
}

$count = AutoLoader::Run(BASE);
$autoLoaderTime = microtime(true) - $execStartTime;
Stats::set('AutoLoaderTime', $autoLoaderTime);

// Load config
$configFile = BASE.'config/'.preg_replace('/^www\\./', '', $_SERVER['SERVER_NAME']).'.php';
@include $configFile;

if (!defined('SITE')) {
    // Attempt to guess SITE
    define('SITE', $_SERVER['SERVER_NAME']);
}

define('URL_ROOT_HTTP', 'http://'.SITE.'/');
define('URL_ROOT_HTTPS', 'https://'.SITE.'/');
define('URL_ROOT', URL_ROOT_HTTP);

/**
 * Find matching page request.
 * 
 * Tell each module to attempt to handle the page request.
 * The Module::handlePageRequest function will check it's $pages array for
 * something that will handle the request. If it does handle it, the loop exits.
 * 
 * If it does not handle it, we continue to the next module and try again.
 * 
 * Using this method, modules can override different page URLs.
 * For example, the DefaultModule handles any URL to give a generic error page.
 * When you set up a module, you should set up an Index page ('/') to handle the
 * site index.
 */

$request = $_SERVER['REQUEST_URI'];
try {
    $success = false;
    foreach (Modules::getActiveModules() as $module) {
        $success = $module->handlePageRequest($request);
        if ($success) break;
    }

    if (!$success) {
        // No handler was found
        throw new PageNotFoundException($request);
    }
} catch (PageNotFoundException $pnfe) {
    Page::executePage(new FourOhFourPage($request));
} catch (HandlerNotFoundException $hnfe) {
    Page::executePage(new HandlerNotFoundPage($hnfe->getHandlerName()));
} catch (Exception $ex) {
    // Unexpected exception
    echo "
        <h1>Uncaught Exception</h1>
        <p>{$ex->getMessage()}</p>
        <p>{$ex->getFile()}({$ex->getLine()}) {$ex->getPrevious()}</p>
        <pre>{$ex->getTraceAsString()}</p>
        ";
}
exit;

/**
 * Returns the first value if set, or the second vlaue
 * @param mixed $a
 * @param mixed $b Fallback value
 * @return mixed $a or $b
 */
function ifUnset($a, $b = null)
{
    return isset($a) ? $a : $b;
}
?>
