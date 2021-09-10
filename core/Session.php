<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Session management
 *
 * @author Daedalus
 */
class Session {
    private $scope;
    private $readOnly;

    /**
     * Opens or creates the given session
     * @param string $scope Session scope
     * @param boolean $readonly Enables or disables writing to session
     */
    public function __construct($scope = null, $readOnly = false)
    {
        $this->scope       = $scope;
        $this->readOnly    = $readOnly;
    }

    public function __set($name, $value) { $this->set($name, $value); }
    public function __get($name) { return $this->get($name); }

    public function set($key, $value)
    {
        if ($this->readOnly) throw new SessionException('Attempted to write to readonly Session');

        if ($this->scope) {
            $key = $this->scope.':'.$key;
        }

        $_SESSION[$key] = $value;
    }

    public function get($key, $default = null)
    {
        if ($this->scope) {
            $key = $this->scope.':'.$key;
        }
        return ifUnset($_SESSION[$key], $default);
    }
}
?>
