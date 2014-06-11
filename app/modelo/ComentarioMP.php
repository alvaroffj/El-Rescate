<?php

/**
 * Description of ComentarioMP
 *
 * @author Alvaro Flores
 */
require_once 'Bd.php';

class ComentarioMP {
    protected $_dbTable = "comentario";
    protected $_id = "comentario_id";
    protected $_bd;

    function __construct() {
        $this->_bd = new Bd();
    }

    function find($id) {
        $id = $this->_bd->limpia($id);

        $sql = "SELECT c.*, u.usuario_nom, u.usuario_ape, to_char(c.modificado,'dd-mm-yyyy HH24:MI:SS') AS modificado
    			FROM $this->_dbTable AS c 
    				INNER JOIN usuario AS u ON c.creado_por = u.usuario_id
    			WHERE c.comentario_id = $id
    				AND c.comentario_estado = true
    			ORDER BY c.comentario_id ASC";
        // error_log($sql);
        $res = $this->_bd->sql($sql);
        if($res) {
            $row = pg_fetch_object($res);
            return $row;
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
        
        $sql = "INSERT INTO $this->_dbTable ($vars, modificado, creado) VALUES ($vals, NOW(), NOW()) RETURNING comentario_id";

        
        $res = $this->_bd->sql($sql);
        $row = pg_fetch_object($res);
        return $row->comentario_id;
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

        $sql = "UPDATE $this->_dbTable SET $val, modificado = NOW() WHERE $this->_id = $data->direccion_id";
        $res = new stdClass();
        $res->sql = $sql;
        $res->error = $this->_bd->sql($sql);
        return $res;
    }

    function fetchByPersona($id) {
        $id = $this->_bd->limpia($id);

        $sql = "SELECT c.*, u.usuario_nom, u.usuario_ape, to_char(c.modificado,'dd-mm-yyyy HH24:MI:SS') AS modificado 
    			FROM $this->_dbTable AS c 
    				INNER JOIN usuario AS u ON c.creado_por = u.usuario_id
    			WHERE c.persona_id = $id
    				AND c.comentario_estado = true
    			ORDER BY c.comentario_id ASC";

        $res = $this->_bd->sql($sql);
        $arr = array();
        while($row = pg_fetch_object($res)) {
            $arr[] = $row;
        }
        return $arr;
    }

    
}

?>