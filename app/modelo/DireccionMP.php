<?php

/**
 * Description of DireccionMP
 *
 * @author Alvaro Flores
 */
require_once 'Bd.php';

class DireccionMP {
    protected $_dbTable = "direccion";
    protected $_id = "direccion_id";
    protected $_bd;

    function __construct() {
        $this->_bd = new Bd();
    }

    function findUnidad($id, $nivel) {
        $id = $this->_bd->limpia($id);
        $nivel = $this->_bd->limpia($nivel);

        $col = array("", "barrio_id", "estaca_id", "mision_id");
        $tab = array("", "barrio", "estaca", "mision");
        $sql = "SELECT * FROM " . $tab[$nivel] . " WHERE " . $col[$nivel] . " = " . $id;

        $res = $this->_bd->sql($sql);
        $row = pg_fetch_object($res);

        return $row;
    }

    function fetchByUnidad($id, $nivel, $objetivo) {
        $id = $this->_bd->limpia($id);
        $nivel = $this->_bd->limpia($nivel);

        $col = array("", "barrio_id", "estaca_id", "mision_id");
        $tab = array("barrio", "estaca");
        $sql = "SELECT * FROM " . $tab[$objetivo] . " WHERE " . $col[$nivel] . " = " . $id;

        $res = $this->_bd->sql($sql);
        $arr = array();
        while($row = pg_fetch_object($res)) {
            $arr[] = $row;
        }
        return $arr;
    }

    function fetchByPersona($id, $attr = null) {
        $id = $this->_bd->limpia($id);

        if($attr == null) {
            $sAttr = "*";
        } else {
            $sAttr = implode(",", $attr);
        }

        $sql = "SELECT d.*, de.direccion_estado, df.direccion_fuente, r.region_nom, c.comuna_nom FROM $this->_dbTable AS d 
                INNER JOIN direccion_estado AS de ON d.direccion_estado_id = de.direccion_estado_id
                INNER JOIN direccion_fuente AS df ON d.direccion_fuente_id = df.direccion_fuente_id
                INNER JOIN region AS r ON d.region_id = r.region_id
                INNER JOIN comuna AS c ON d.comuna_id = c.comuna_id
                WHERE d.persona_id = $id
                ORDER BY d.direccion_estado_id, d.direccion_id ASC";
        $res = $this->_bd->sql($sql);
        $arr = array();
        while($row = pg_fetch_object($res)) {
            $row->direccion_visitado = ($row->direccion_visitado=="t")?true:false;
            $row->direccion = trim($row->direccion);
            if($row->direccion != '' && $row->direccion != 'null')
                $arr[] = $row;
        }
        return $arr;
    }

    function find($id, $attr = null) {
        $id = $this->_bd->limpia($id);

        if($attr == null) {
            $sAttr = "*";
        } else {
            $sAttr = implode(",", $attr);
        }

        $sql = "SELECT d.*, de.direccion_estado, df.direccion_fuente, r.region_nom, c.comuna_nom FROM $this->_dbTable AS d 
                INNER JOIN direccion_estado AS de ON d.direccion_estado_id = de.direccion_estado_id
                INNER JOIN direccion_fuente AS df ON d.direccion_fuente_id = df.direccion_fuente_id
                INNER JOIN region AS r ON d.region_id = r.region_id
                INNER JOIN comuna AS c ON d.comuna_id = c.comuna_id
                WHERE d.direccion_id = $id";
        // error_log($sql);
        $res = $this->_bd->sql($sql);
        if($res) {
            $row = pg_fetch_object($res);
            $row->direccion_visitado = ($row->direccion_visitado=="t")?true:false;
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
        
        $sql = "INSERT INTO $this->_dbTable ($vars, modificado, creado) VALUES ($vals, NOW(), NOW()) RETURNING direccion_id";

        
        $res = $this->_bd->sql($sql);
        $row = pg_fetch_object($res);
        return $row->direccion_id;
    }

    function fetchEstados() {
        $sql = "SELECT * FROM direccion_estado WHERE direccion_estado_estado = true ORDER BY direccion_estado_id ASC";
        $res = $this->_bd->sql($sql);
        $arr = array();
        while($row = pg_fetch_object($res)) {
            $arr[] = $row;
        }
        return $arr;
    }

    function fetchFuentes() {
        $sql = "SELECT * FROM direccion_fuente WHERE direccion_fuente_estado = true ORDER BY direccion_fuente_id ASC";
        $res = $this->_bd->sql($sql);
        $arr = array();
        while($row = pg_fetch_object($res)) {
            $arr[] = $row;
        }
        return $arr;
    }

    function fetchRegiones() {
        $sql = "SELECT * FROM region ORDER BY region_id ASC";
        $res = $this->_bd->sql($sql);
        $arr = array();
        while($row = pg_fetch_object($res)) {
            $arr[] = $row;
        }
        return $arr;
    }

    function fetchComunas() {
        $sql = "SELECT * FROM comuna ORDER BY comuna_id ASC";
        $res = $this->_bd->sql($sql);
        $arr = array();
        while($row = pg_fetch_object($res)) {
            $arr[] = $row;
        }
        return $arr;
    }

    function fetchMisiones() {
        $sql = "SELECT * FROM mision ORDER BY mision_nom ASC";
        $res = $this->_bd->sql($sql);
        $arr = array();
        while($row = pg_fetch_object($res)) {
            $arr[] = $row;
        }
        return $arr;
    }

    function fetchUnidades($nivel) {
        $tabla = array('', 'barrio', 'estaca', 'mision');
        $key = array('', 'barrio_id', 'estaca_id', 'mision_id');
        $nom = array('', 'barrio_nom', 'estaca_nom', 'mision_nom');
        $sql = "SELECT * FROM ".$tabla[$nivel]." ORDER BY ".$nom[$nivel]." ASC";
        $res = $this->_bd->sql($sql);
        $arr = array();
        while($row = pg_fetch_object($res)) {
            $arr[] = $row;
        }
        return $arr;
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

    function verificar($id, $idPer) {
        $id = $this->_bd->limpia($id);
        $idPer = $this->_bd->limpia($idPer);

        $sql1 = "UPDATE $this->_dbTable SET direccion_estado_id = 3 WHERE persona_id = $idPer";
        $res1 = $this->_bd->sql($sql1);

        $sql2 = "UPDATE $this->_dbTable SET direccion_estado_id = 2 WHERE $this->_id = $id";
        $res2 = $this->_bd->sql($sql2);

        $res = new stdClass();
        $res->sql1 = $sql1;
        $res->sql2 = $sql2;
        $res->error = $this->_bd->sql($sql);

        return $res;
    }

}

?>