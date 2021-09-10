<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Non-Recursive Auto Loader
 *
 * @author Daedalus
 */
class AutoLoader {
    /**
     * Runs AutoLoader
     * @param string $baseDir
     * @return The number of files loaded
     */
    static public function Run($baseDir)
    {
        // Get all directories
        $dir = $baseDir;
        while($dirs = glob($dir . '*', GLOB_ONLYDIR)) {
            $dir .= '/*';
            if(!$d) {
                $d=$dirs;
            } else {
                $d=array_merge($d,$dirs);
            }
        }

        // Load appropriate files
        $count = 0;
        foreach ($d as $dirs) {
            foreach (glob($dirs.'/*') as $file) {
                // Skip index.php and config files
                if (preg_match('/config\/|index(|\d).php(|\d$)/', $file)) continue;

                if (preg_match('/.php(|\d)$/', $file)) {
                    require_once($file);
                    $count++;
                }
            }
        }

        Stats::set('Autoloader', "$count files loaded");
        return $count;
    }

    static private function recurseLoader($dir)
    {
        echo "Index: $dir<br/>";
        $count = 0;

        $files = glob($dir.'*');
        foreach ($files as $file) {
            // Skip index.php files
            if (preg_match('/index.php(|\d$)/', $file)) continue;

            if (preg_match('/.php(|\d)$/', $file)) {
                require_once($file);
                $count++;
            }
        }

        return $count;
    }
}
?>
