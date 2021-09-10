<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Modules
 *
 * @author Daedalus
 */
final class Modules {
    /** Add a module to the end of the array (default) */
    const WH_END = 1;
    /** Add a module to the beginning of the array */
    const WH_START = 2;

    /**
     * The names of the current active modules
     * @var array(string)
     */
    static private $activeModules = array();

    /**
     * Creates and returns an array of modules
     * @return array(Module)
     */
    static public function getActiveModules()
    {
        // Create each activated module
        $modules = array();
        foreach (self::$activeModules as $moduleName) {
            if (!class_exists($moduleName)) throw new ModuleNotFoundException();
            $modules[] = new $moduleName();
        }

        $modules[] = new DefaultModule();

        return $modules;
    }

    /**
     * Adds a module to the 'active' list
     * @param string $module The name of the module class
     * @param WH_START or WH_END $where Where to insert the module
     * 
     * NOTE: Uses the name of the module, not a reference to the module!
     *       The module will be created after the autoloader has run.
     */
    static public function addModule($module, $where = self::WH_END)
    {
        if ($module instanceOf Module) throw new ModuleException('addModule only accepts names of modules');
        
        if (!in_array(self::$activeModules, $module)) {
            switch ($where) {
                case self::WH_END:
                    self::$activeModules[] = $module;
                    break;

                case self::WH_START:
                    array_unshift(self::$activeModules, $module);
                    break;

                default:
                    throw new ModuleException('Unknown $where parameter');
            }
        }
    }
}
?>
