<?php

/**
 * Description of UsuarioMP
 *
 * @author Alvaro Flores
 */
require_once 'Bd.php';

class UsuarioMP {
    protected $_dbTable = "usuario";
    protected $_id = "usuario_id";
    protected $_bd;

    function __construct() {
        $this->_bd = new Bd();
    }

    function find($id, $attr = null) {
        $id = $this->_bd->limpia($id);

        if($attr == null) {
            $sAttr = "*";
        } else {
            $sAttr = implode(",", $attr);
        }

        $sql = "SELECT $sAttr FROM $this->_dbTable WHERE $this->_id = $id";
        // error_log($sql);
        $res = $this->_bd->sql($sql);
        if($res) {
            $row = pg_fetch_object($res);
            return $row;
        } else return false;
    }

    function fetchAcceso($id) {
        $id = $this->_bd->limpia($id);

        $sql = "SELECT * FROM usuario_acceso WHERE usuario_id = $id";

        $res = $this->_bd->sql($sql);

        $arr = array();
        while($row = pg_fetch_object($res)) {
            $arr[] = $row;
        }

        return $arr;
    }

    function findByUser($user, $attr = null) {
        $user = $this->_bd->limpia($user);

        if($attr == null) {
            $sAttr = "*";
        } else {
            $sAttr = implode(",", $attr);
        }

        $sql = "SELECT $sAttr FROM $this->_dbTable WHERE usuario_user = '$user'";
        $res = $this->_bd->sql($sql);
        if($res) {
            $row = pg_fetch_object($res);
            return $row;
        } else return false;
    }

    function disponible($us) {
        $us = $this->_bd->limpia($us);

        $sql = "SELECT usuario_id FROM usuario WHERE usuario_user = '$us'";
        // error_log($sql);
        $res = $this->_bd->sql($sql);
        if($res) {
            $row = pg_fetch_object($res);
            
            return !($row->usuario_id > 0);
        } else return true;
    }

    function checkLogin($user, $pass) {
        $user = $this->_bd->limpia($user);
        $pass = $this->_bd->limpia($pass);

        $sql = "SELECT * FROM $this->_dbTable WHERE usuario_user = '$user' AND usuario_pass = '$pass' AND usuario_estado = 1";

        $res = $this->_bd->sql($sql);
        if($res) {
            $row = pg_fetch_object($res);
            if(isset($row->usuario_id) && $row->usuario_id>0) {
                unset($row->usuario_pass);
                $sql = "UPDATE $this->_dbTable SET usuario_lastaccess = NOW() WHERE usuario_id = ".$row->usuario_id;
                $res = $this->_bd->sql($sql);
                return $row;
            } else {
                return false;
            }
        } else return false;
    }
    
    function insert($data) {
        $variables = get_object_vars($data);
        $keys = array_keys($variables);
        
        $i = 0;
        foreach($keys as $k) {
            if($i) {
                $vars .= ", ".$k;
                $vals .= ", '".$this->_bd->limpia($data->$k)."'";
            } else {
                $vars = $k;
                $vals = "'".$data->$k."'";
            }
            $i++;
        }
        
        $sql = "INSERT INTO $this->_dbTable ($vars, modificado, creado) VALUES ($vals, NOW(), NOW()) RETURNING usuario_id";
        error_log($sql);
        
        $res = $this->_bd->sql($sql);
        $row = pg_fetch_object($res);
        return $row->usuario_id;
    }

    function addAcceso($data) {
        $variables = get_object_vars($data);
        $keys = array_keys($variables);
        
        $i = 0;
        foreach($keys as $k) {
            if($i) {
                $vars .= ", ".$k;
                $vals .= ", '".$this->_bd->limpia($data->$k)."'";
            } else {
                $vars = $k;
                $vals = "'".$data->$k."'";
            }
            $i++;
        }
        
        $sql = "INSERT INTO usuario_acceso ($vars, creado) VALUES ($vals, NOW())";
        error_log($sql);
        $res = $this->_bd->sql($sql);
        return $res;
    }

    function genPassword($n=6) {
        $letras = "023456789ABCDEFJHIJKLMNOPQRSTUVWXYZabcdefjhijkmnopqrstuvwxyz";
        $pass = "";
        for($i=0; $i<$n; $i++) {
            $pass .= substr($letras,rand(0,61),1);
        }
        return $pass;
    }

    function update($data) {
        $variables = get_object_vars($data);
        $keys = array_keys($variables);
        
        $i = 0;
        $val = "";
        foreach($keys as $k) {
            if($data->$k != '') {
                if($k != $this->_id) {
                    if($val != "") {
                        $val .= ", ".$k." = '".$data->$k."'";
                    } else {
                        $val = $k." = '".$data->$k."'";
                    }
                }
                $i++;
            } else {
                if($val != "") {
                    $val .= ", ".$k." = null";
                } else {
                    $val = $k." = null";
                }
            }
        }

        $sql = "UPDATE $this->_dbTable SET $val, modificado = NOW() WHERE $this->_id = $data->usuario_id";
        $res = new stdClass();
        $res->sql = $sql;
        $res->error = $this->_bd->sql($sql);
        return $res;
    }
}

?>