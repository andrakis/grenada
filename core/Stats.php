<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Stats
 *
 * @author Daedalus
 */
class Stats {
    static private $stats = array();

    static public function set($name, $value) {
        self::$stats[$name] = $value;
    }

    static public function getStats() {
        return self::$stats;
    }
}
?>
