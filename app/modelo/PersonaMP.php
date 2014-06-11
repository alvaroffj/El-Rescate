<?php

/**
 * Description of PersonaMP
 *
 * @author Alvaro Flores
 */
require_once 'Bd.php';

class PersonaMP {
    protected $_dbTable = "persona";
    protected $_id = "persona_id";
    protected $_bd;

    function __construct() {
        $this->_bd = new Bd();
    }

    function getDV($rut) {
        $tur = strrev($rut);
        $mult = 2;
        $suma = 0;
     
        for ($i = 0; $i <= strlen($tur); $i++) { 
           if ($mult > 7) $mult = 2; 
     
           $suma = $mult * substr($tur, $i, 1) + $suma;
           $mult = $mult + 1;
        }
     
        $valor = 11 - ($suma % 11);
     
        if ($valor == 11) { 
            $codigo_veri = "0";
          } elseif ($valor == 10) {
            $codigo_veri = "k";
          } else { 
            $codigo_veri = $valor;
        }

        return $codigo_veri;
    }

    function fetchDireccion($id, $attr = null) {
        $id = $this->_bd->limpia($id);

        if($attr == null) {
            $sAttr = "*";
        } else {
            $sAttr = implode(",", $attr);
        }
        
        $sql = "SELECT d.*, de.direccion_estado, df.direccion_fuente, r.region_nom, c.comuna_nom FROM direccion AS d 
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
            if($row->direccion_estado_id == 2) {
                array_unshift($arr, $row);
            } else {
                array_push($arr, $row);
            }
        }
        return $arr;
    }

    function fetchAll($unidad, $nivel, $dir = false) {
        $nivelVal = array('', 'barrio_id', 'estaca_id', 'mision_id');

        $ini = microtime(true);
        if(!$dir) {
            $sql = "SELECT p.*, b.barrio_nom, e.estaca_nom, m.mision_nom, pe.persona_estado, date_part('year',age(persona_fecnac)) AS persona_edad, to_char(persona_fecnac,'dd-mm-yyyy') AS persona_fecnac_h 
                    FROM $this->_dbTable AS p 
                    INNER JOIN barrio as b ON p.barrio_id = b.barrio_id 
                    INNER JOIN estaca AS e ON p.estaca_id = e.estaca_id
                    INNER JOIN mision AS m ON p.mision_id = m.mision_id
                    INNER JOIN persona_estado AS pe ON p.persona_estado_id = pe.persona_estado_id
                    WHERE p.".$nivelVal[$nivel]." = ".$unidad." 
                    ORDER BY persona_id ASC";
        } else {
            $sql = "SELECT p.*, b.barrio_nom, e.estaca_nom, m.mision_nom, pe.persona_estado, date_part('year',age(persona_fecnac)) AS persona_edad, to_char(persona_fecnac,'dd-mm-yyyy') AS persona_fecnac_h,  
                    d.direccion_id, d.comuna_id, d.region_id, d.barrio_id, d.estaca_id, d.mision_id,
                    d.direccion_estado_id, d.direccion_fuente_id, d.direccion, 
                    de.direccion_estado, df.direccion_fuente, r.region_nom, c.comuna_nom
                    FROM $this->_dbTable AS p 
                    INNER JOIN barrio as b ON p.barrio_id = b.barrio_id 
                    INNER JOIN estaca AS e ON p.estaca_id = e.estaca_id
                    INNER JOIN mision AS m ON p.mision_id = m.mision_id
                    INNER JOIN persona_estado AS pe ON p.persona_estado_id = pe.persona_estado_id
                    LEFT JOIN direccion AS d ON p.persona_id = d.persona_id
                    LEFT JOIN direccion_estado AS de ON d.direccion_estado_id = de.direccion_estado_id
                    LEFT JOIN direccion_fuente AS df ON d.direccion_fuente_id = df.direccion_fuente_id
                    LEFT JOIN region AS r ON d.region_id = r.region_id
                    LEFT JOIN comuna AS c ON d.comuna_id = c.comuna_id
                    WHERE p.".$nivelVal[$nivel]." = ".$unidad." 
                    ORDER BY p.persona_id, d.direccion_estado_id, d.direccion_id ASC";
        }
        
        $res = $this->_bd->sql($sql);
        $arr = array();
        $fin = microtime(true);
        error_log($sql);
        error_log("query personas: " . ($fin - $ini));
        $ini = microtime(true);
        $pAnt = 0;
        $i = 0;
        while($row = pg_fetch_object($res)) {
            $row->persona_cabezahogar = ($row->persona_id==$row->persona_cabeza_id)?true:false;
            $row->persona_exmisionero = ($row->persona_exmisionero=="t")?true:false;
            $row->persona_sellado = ($row->persona_sellado=="t")?true:false;
            $row->persona_viudo = ($row->persona_viudo=="t")?true:false;
            $row->persona_fallecido = ($row->persona_fallecido=="t")?true:false;
            $row->persona_no_rut = ($row->persona_no_rut=="t")?true:false;
            $row->persona_investido = ($row->persona_investido=="t")?true:false;
            $row->persona_verificado = ($row->persona_verificado=="t")?true:false;
            $row->persona_visitado = ($row->persona_visitado=="t")?true:false;
            $row->persona_fecfall = ($row->persona_fecfall==null)?"":$row->persona_fecfall;
            $row->persona_fuera_unidad = ($row->persona_fuera_unidad=="t")?true:false;
            $row->persona_edad = ($row->persona_edad==null)?-1:$row->persona_edad;
            $row->persona_rut_h = ($row->persona_rut)?$row->persona_rut."-".$this->getDV($row->persona_rut):"";
            $row->persona_fecnac = ($row->persona_fecnac==null)?"":$row->persona_fecnac;
            $row->persona_ofisac =  strtoupper($row->persona_ofisac);
            if($row->persona_fecfall!='') {
                $nac = new DateTime($row->persona_fecnac);
                $fall = new DateTime($row->persona_fecfall);
                $diff = $nac->diff($fall);
                $row->persona_edad = $diff->format('%y%');
            }
            $row->persona_adulto = ($row->persona_edad*1 > 17)?true:false;
            $row->direccion_mls = false;
            if($dir) {
                $dir = new stdClass();
                $dir->direccion_id = $row->direccion_id;
                $dir->comuna_id = $row->comuna_id;
                $dir->region_id = $row->region_id;
                $dir->persona_id = $row->persona_id;
                $dir->barrio_id = $row->barrio_id;
                $dir->estaca_id = $row->estaca_id;
                $dir->mision_id = $row->mision_id;
                $dir->direccion_fuente = $row->direccion_fuente;
                $dir->direccion_fuente_id = $row->direccion_fuente_id;
                $dir->direccion_estado = $row->direccion_estado;
                $dir->direccion_estado_id = $row->direccion_estado_id;
                $dir->region_nom = $row->region_nom;
                $dir->comuna_nom = $row->comuna_nom;
                $dir->direccion = trim($row->direccion);

                unset($row->direccion_id);
                unset($row->comuna_id);
                unset($row->region_id);
                unset($row->barrio_id);
                unset($row->estaca_id);
                unset($row->mision_id);
                unset($row->direccion_fuente);
                unset($row->direccion_fuente_id);
                unset($row->direccion_estado);
                unset($row->direccion_estado_id);
                unset($row->region_nom);
                unset($row->comuna_nom);
                unset($row->direccion);


                if($pAnt > 0) {
                    if($pAnt == $row->persona_id) {
                        if($dir->direccion != '' && $dir->direccion != 'null') {
                            $arr[$i-1]->direccion[] = $dir;
                            if(!$arr[$i-1]->direccion_mls)
                                $arr[$i-1]->direccion_mls = ($dir->direccion_fuente_id == "1")?true:false;
                        }
                    } else {
                        $row->direccion = array();
                        $arr[$i] = $row;
                        $i++;
                        if($dir->direccion != '' && $dir->direccion != 'null') {
                            $arr[$i-1]->direccion[] = $dir;
                            if(!$arr[$i-1]->direccion_mls)
                                $arr[$i-1]->direccion_mls = ($dir->direccion_fuente_id == "1")?true:false;
                        }
                    }
                } else {
                    $row->direccion = array();
                    if($dir->direccion != '' && $dir->direccion != 'null')
                        $row->direccion[] = $dir;
                    $arr[$i] = $row;
                    $i++;
                    if(!$arr[$i-1]->direccion_mls)
                        $arr[$i-1]->direccion_mls = ($dir->direccion_fuente_id == "1")?true:false;

                }
                $pAnt = $row->persona_id;
            } else {
                $arr[] = $row;
            }
        }
        $fin = microtime(true);
        error_log("procesamiento personas: " . ($fin - $ini));
        return $arr;

        // Sin direccion
        // 574 row(s)
        // Total runtime: 93.694 ms

        // Con direccion
        // 696 row(s)
        // Total runtime: 372.894 ms
    }

    function fetchLog($id, $attr = null) {
        $id = $this->_bd->limpia($id);

        if($attr == null) {
            $sAttr = "*";
        } else {
            $sAttr = implode(",", $attr);
        }

        $sql = "SELECT p.*, 
                b.barrio_nom, e.estaca_nom, m.mision_nom, pe.persona_estado, u.usuario_nom, u.usuario_ape, 
                date_part('year',age(persona_fecnac)) AS persona_edad, 
                to_char(persona_fecnac,'dd-mm-yyyy') AS persona_fecnac_h, 
                to_char(persona_fecfall,'dd-mm-yyyy') AS persona_fecfall_h
                FROM persona_log AS p 
                INNER JOIN barrio as b ON p.barrio_id = b.barrio_id 
                INNER JOIN estaca AS e ON p.estaca_id = e.estaca_id
                INNER JOIN mision AS m ON p.mision_id = m.mision_id
                INNER JOIN persona_estado AS pe ON p.persona_estado_id = pe.persona_estado_id
                INNER JOIN usuario AS u ON p.modificado_por = u.usuario_id
                WHERE p.persona_id = $id
                ORDER BY p.persona_log_id ASC";

        // error_log($sql);
        $res = $this->_bd->sql($sql);
        $arr = array();
        while($row = pg_fetch_object($res)) {
            $row->persona_cabezahogar = ($row->persona_id==$row->persona_cabeza_id)?true:false;
            $row->persona_exmisionero = ($row->persona_exmisionero=="t")?true:false;
            $row->persona_sellado = ($row->persona_sellado=="t")?true:false;
            $row->persona_viudo = ($row->persona_viudo=="t")?true:false;
            $row->persona_fallecido = ($row->persona_fallecido=="t")?true:false;
            $row->persona_no_rut = ($row->persona_no_rut=="t")?true:false;
            $row->persona_investido = ($row->persona_investido=="t")?true:false;
            $row->persona_verificado = ($row->persona_verificado=="t")?true:false;
            $row->persona_visitado = ($row->persona_visitado=="t")?true:false;
            $row->persona_fuera_unidad = ($row->persona_fuera_unidad=="t")?true:false;
            $row->persona_edad = ($row->persona_edad==null)?-1:$row->persona_edad;
            $row->persona_rut_h = ($row->persona_rut)?$row->persona_rut."-".$this->getDV($row->persona_rut):"";
            $row->persona_fecnac = ($row->persona_fecnac==null)?"":$row->persona_fecnac;
            $row->persona_fecfall = ($row->persona_fecfall==null)?"":$row->persona_fecfall;
            $row->persona_ofisac =  strtoupper($row->persona_ofisac);
            if($row->persona_fecfall!='') {
                $nac = new DateTime($row->persona_fecnac);
                $fall = new DateTime($row->persona_fecfall);
                $diff = $nac->diff($fall);
                $row->persona_edad = $diff->format('%y%');
            }
            $arr[] = $row;
        }
        return $arr;
    }

    function fetchFamilia($id) {
        $id = $this->_bd->limpia($id);

        $sql = "SELECT p.*, date_part('year',age(persona_fecnac)) AS persona_edad, to_char(persona_fecnac,'dd-mm-yyyy') AS persona_fecnac_h, 
                d.direccion_id, d.comuna_id, d.region_id, d.barrio_id, d.estaca_id, d.mision_id,
                d.direccion_estado_id, d.direccion_fuente_id, d.direccion, 
                de.direccion_estado, df.direccion_fuente, r.region_nom, c.comuna_nom
                FROM persona AS p 
                INNER JOIN barrio as b ON p.barrio_id = b.barrio_id 
                INNER JOIN estaca AS e ON p.estaca_id = e.estaca_id
                INNER JOIN mision AS m ON p.mision_id = m.mision_id
                INNER JOIN persona_estado AS pe ON p.persona_estado_id = pe.persona_estado_id
                LEFT JOIN direccion AS d ON p.persona_id = d.persona_id
                LEFT JOIN direccion_estado AS de ON d.direccion_estado_id = de.direccion_estado_id
                LEFT JOIN direccion_fuente AS df ON d.direccion_fuente_id = df.direccion_fuente_id
                LEFT JOIN region AS r ON d.region_id = r.region_id
                LEFT JOIN comuna AS c ON d.comuna_id = c.comuna_id
                WHERE p.persona_cabeza_id = $id
                ORDER BY persona_edad DESC";
        $res = $this->_bd->sql($sql);
        $arr = array();
        $pAnt = 0;
        $i = 0;
        while($row = pg_fetch_object($res)) {
            $row->persona_cabezahogar = ($row->persona_id==$row->persona_cabeza_id)?true:false;
            $row->persona_exmisionero = ($row->persona_exmisionero=="t")?true:false;
            $row->persona_sellado = ($row->persona_sellado=="t")?true:false;
            $row->persona_viudo = ($row->persona_viudo=="t")?true:false;
            $row->persona_fallecido = ($row->persona_fallecido=="t")?true:false;
            $row->persona_no_rut = ($row->persona_no_rut=="t")?true:false;
            $row->persona_investido = ($row->persona_investido=="t")?true:false;
            $row->persona_verificado = ($row->persona_verificado=="t")?true:false;
            $row->persona_visitado = ($row->persona_visitado=="t")?true:false;
            $row->persona_fuera_unidad = ($row->persona_fuera_unidad=="t")?true:false;
            $row->persona_edad = ($row->persona_edad==null)?-1:$row->persona_edad;
            $row->persona_rut_h = ($row->persona_rut)?$row->persona_rut."-".$this->getDV($row->persona_rut):"";
            $row->persona_fecnac = ($row->persona_fecnac==null)?"":$row->persona_fecnac;
            $row->persona_fecfall = ($row->persona_fecfall==null)?"":$row->persona_fecfall;
            $row->persona_ofisac =  strtoupper($row->persona_ofisac);
            if($row->persona_fecfall!='') {
                $nac = new DateTime($row->persona_fecnac);
                $fall = new DateTime($row->persona_fecfall);
                $diff = $nac->diff($fall);
                $row->persona_edad = $diff->format('%y%');
            }

            $dir = new stdClass();
            $dir->direccion_id = $row->direccion_id;
            $dir->comuna_id = $row->comuna_id;
            $dir->region_id = $row->region_id;
            $dir->persona_id = $row->persona_id;
            $dir->barrio_id = $row->barrio_id;
            $dir->estaca_id = $row->estaca_id;
            $dir->mision_id = $row->mision_id;
            $dir->direccion_fuente = $row->direccion_fuente;
            $dir->direccion_fuente_id = $row->direccion_fuente_id;
            $dir->direccion_estado = $row->direccion_estado;
            $dir->direccion_estado_id = $row->direccion_estado_id;
            $dir->region_nom = $row->region_nom;
            $dir->comuna_nom = $row->comuna_nom;
            $dir->direccion = $row->direccion;

            unset($row->direccion_id);
            unset($row->comuna_id);
            unset($row->region_id);
            // unset($row->barrio_id);
            unset($row->estaca_id);
            unset($row->mision_id);
            unset($row->direccion_fuente);
            unset($row->direccion_fuente_id);
            unset($row->direccion_estado);
            unset($row->direccion_estado_id);
            unset($row->region_nom);
            unset($row->comuna_nom);
            unset($row->direccion);

            if($pAnt > 0) {
                if($pAnt == $row->persona_id) {
                    if($dir->direccion != '' && $dir->direccion != 'null')
                        $arr[$i-1]->direccion[] = $dir;
                } else {
                    $row->direccion = array();
                    if($dir->direccion != '' && $dir->direccion != 'null')
                        $row->direccion[] = $dir;
                    $arr[$i] = $row;
                    $i++;
                }
            } else {
                $row->direccion = array();
                if($dir->direccion != '' && $dir->direccion != 'null')
                    $row->direccion[] = $dir;
                $arr[$i] = $row;
                $i++;
            }
            $pAnt = $row->persona_id;
        }
        return $arr;
    }

    function find($id, $full = true, $attr = null) {
        $id = $this->_bd->limpia($id);

        if($attr == null) {
            $sAttr = "*";
        } else {
            $sAttr = implode(",", $attr);
        }
        if($full) {
            // $sql = "SELECT p.*, 
            //         b.barrio_nom, e.estaca_nom, m.mision_nom, pe.persona_estado, u.usuario_nom, u.usuario_ape, 
            //         date_part('year',age(persona_fecnac)) AS persona_edad, 
            //         to_char(persona_fecnac,'dd-mm-yyyy') AS persona_fecnac_h, 
            //         to_char(persona_fecfall,'dd-mm-yyyy') AS persona_fecfall_h, 
            //         to_char(p.modificado,'dd-mm-yyyy HH24:MI:SS') AS modificado,
            //         to_char(p.actualizado_mls,'dd-mm-yyyy HH24:MI:SS') AS actualizado_mls  
            //         FROM $this->_dbTable AS p 
            //         INNER JOIN barrio as b ON p.barrio_id = b.barrio_id 
            //         INNER JOIN estaca AS e ON p.estaca_id = e.estaca_id
            //         INNER JOIN mision AS m ON p.mision_id = m.mision_id
            //         INNER JOIN persona_estado AS pe ON p.persona_estado_id = pe.persona_estado_id
            //         INNER JOIN usuario AS u ON p.modificado_por = u.usuario_id
            //         WHERE p.persona_id = $id";
            $sql = "SELECT p.*, 
                    b.barrio_nom, e.estaca_nom, m.mision_nom, pe.persona_estado, u.usuario_nom, u.usuario_ape, 
                    date_part('year',age(persona_fecnac)) AS persona_edad, 
                    to_char(persona_fecnac,'dd-mm-yyyy') AS persona_fecnac_h, 
                    to_char(persona_fecfall,'dd-mm-yyyy') AS persona_fecfall_h
                    FROM $this->_dbTable AS p 
                    INNER JOIN barrio as b ON p.barrio_id = b.barrio_id 
                    INNER JOIN estaca AS e ON p.estaca_id = e.estaca_id
                    INNER JOIN mision AS m ON p.mision_id = m.mision_id
                    INNER JOIN persona_estado AS pe ON p.persona_estado_id = pe.persona_estado_id
                    INNER JOIN usuario AS u ON p.modificado_por = u.usuario_id
                    WHERE p.persona_id = $id";
        } else {
            $sql = "SELECT * FROM $this->_dbTable WHERE persona_id = $id";
        }
        // error_log($sql);
        $res = $this->_bd->sql($sql);
        if($res) {
            $row = pg_fetch_object($res);
            $row->persona_cabezahogar = ($row->persona_id==$row->persona_cabeza_id)?true:false;
            $row->persona_exmisionero = ($row->persona_exmisionero=="t")?true:false;
            $row->persona_sellado = ($row->persona_sellado=="t")?true:false;
            $row->persona_viudo = ($row->persona_viudo=="t")?true:false;
            $row->persona_fallecido = ($row->persona_fallecido=="t")?true:false;
            $row->persona_no_rut = ($row->persona_no_rut=="t")?true:false;
            $row->persona_investido = ($row->persona_investido=="t")?true:false;
            $row->persona_verificado = ($row->persona_verificado=="t")?true:false;
            $row->persona_visitado = ($row->persona_visitado=="t")?true:false;
            $row->persona_fuera_unidad = ($row->persona_fuera_unidad=="t")?true:false;
            $row->persona_edad = ($row->persona_edad==null)?-1:$row->persona_edad;
            $row->persona_fecnac = ($row->persona_fecnac==null)?"":$row->persona_fecnac;
            $row->persona_fecfall = ($row->persona_fecfall==null)?"":$row->persona_fecfall;
            if($row->persona_fecfall!='') {
                $nac = new DateTime($row->persona_fecnac);
                $fall = new DateTime($row->persona_fecfall);
                $diff = $nac->diff($fall);
                $row->persona_edad = $diff->format('%y%');
            }
            if($full) {
                $row->persona_ofisac =  strtoupper($row->persona_ofisac);
                $row->persona_rut_h = ($row->persona_rut)?$row->persona_rut."-".$this->getDV($row->persona_rut):"";
            }
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
        
        $sql = "INSERT INTO $this->_dbTable ($vars) VALUES ($vals)";

        return $this->_bd->sql($sql);
        // return mysql_insert_id();
    }

    function insertLog($data) {
        unset($data->persona_edad);
        $variables = get_object_vars($data);
        $keys = array_keys($variables);
        
        $i = 0;
        foreach($keys as $k) {
            if($data->$k != '') {
                if($i) {
                    $vars .= ", ".$k;
                    $vals .= ", '".$this->_bd->limpia($data->$k)."'";
                } else {
                    $vars = $k;
                    $vals = "'".$data->$k."'";
                }
                $i++;
            }
        }
        
        $sql = "INSERT INTO persona_log ($vars) VALUES ($vals)";
        
        $res->sql = $sql;
        $res->error = $this->_bd->sql($sql);
        return $res;
    }

    function update($data) {
        if(strlen($data->persona_fecfall) == 10) {
            $data->persona_fallecido = "true";
        } else {
            $data->persona_fallecido = "false";
        }
        $variables = get_object_vars($data);
        $keys = array_keys($variables);
        
        $i = 0;
        $val = "";
        foreach($keys as $k) {
            if($data->$k != '') {
                if($k != $this->_id) {
                    if($data->$k == 'null') {
                        if($val != "") {
                            $val .= ", ".$k." = ".$data->$k;
                        } else {
                            $val = $k." = ".$data->$k;
                        }
                    } else {
                        if($val != "") {
                            $val .= ", ".$k." = '".$data->$k."'";
                        } else {
                            $val = $k." = '".$data->$k."'";
                        }
                    }
                }
                $i++;
            }
        }

        $ant = $this->find($data->persona_id, false);
        $res = new stdClass();
        $res->log = $this->insertLog($ant);

        $sql = "UPDATE $this->_dbTable SET $val, modificado = NOW() WHERE $this->_id = $data->persona_id";
        // echo $sql."<br>";
        // return $this->_bd->sql($sql);
        // return $sql;
        // error_log($sql);
        $res->sql = $sql;
        $res->error = $this->_bd->sql($sql);
        return $res;
    }

    function mlsActualizar($id, $id_user) {
        $id = $this->_bd->limpia($id);
        $id_user = $this->_bd->limpia($id_user);

        $ant = $this->find($id, false);
        $res = new stdClass();
        $res->log = $this->insertLog($ant);

        $sql = "UPDATE $this->_dbTable SET actualizado_mls = NOW(), modificado = NOW(), modificado_por = ".$id_user." WHERE $this->_id = $id";
        // $res->sql = $sql;
        $error = $this->_bd->sql($sql);
        $sql = "SELECT 
                    p.actualizado_mls,
                    p.modificado,
                    p.modificado_por,
                    u.usuario_nom, u.usuario_ape
                FROM $this->_dbTable AS p 
                INNER JOIN usuario AS u ON p.modificado_por = u.usuario_id
                WHERE $this->_id = $id";

        $new = $this->_bd->sql($sql);
        $row = pg_fetch_object($new);
        $row->error = $error;
        return $row;
    }

    function paraVisitar($id) {
        $id = $this->_bd->limpia($id);
        $sql = "SELECT p.persona_id, p.barrio_id, p.persona_cabeza_id, p.persona_exmisionero, p.persona_investido, p.persona_no_rut, p.persona_nom, 
                    p.persona_rut, p.persona_sellado, p.persona_ofisac, 
                    d.direccion, d.direccion_fuente_id, df.direccion_fuente, r.region_nom, c.comuna_nom, 
                    date_part('year',age(persona_fecnac)) AS persona_edad
                FROM persona AS p
                RIGHT JOIN direccion AS d ON p.persona_id=d.persona_id
                LEFT JOIN direccion_fuente AS df ON d.direccion_fuente_id = df.direccion_fuente_id
                LEFT JOIN region AS r ON d.region_id = r.region_id
                LEFT JOIN comuna AS c ON d.comuna_id = c.comuna_id
                WHERE 
                    p.barrio_id= $id AND 
                    p.persona_verificado=FALSE AND 
                    p.persona_visitado=FALSE AND 
                    p.persona_fallecido=FALSE AND
                    p.persona_fuera_unidad = FALSE AND
                    d.direccion_estado_id=1 
                ORDER BY p.persona_cabeza_id,p.persona_cabezahogar DESC, p.persona_fecnac";
        $res = $this->_bd->sql($sql);
        $arr = array();
        $fin = microtime(true);
        error_log($sql);
        error_log("query personas: " . ($fin - $ini));
        $ini = microtime(true);
        $pAnt = 0;
        $i = 0;
        while($row = pg_fetch_object($res)) {
            $row->persona_cabezahogar = ($row->persona_id==$row->persona_cabeza_id)?true:false;
            $row->persona_exmisionero = ($row->persona_exmisionero=="t")?true:false;
            $row->persona_sellado = ($row->persona_sellado=="t")?true:false;
            $row->persona_fallecido = ($row->persona_fallecido=="t")?true:false;
            $row->persona_no_rut = ($row->persona_no_rut=="t")?true:false;
            $row->persona_investido = ($row->persona_investido=="t")?true:false;
            $row->persona_edad = ($row->persona_edad==null)?-1:$row->persona_edad;
            $row->persona_rut_h = ($row->persona_rut)?$row->persona_rut."-".$this->getDV($row->persona_rut):"";
            $row->persona_ofisac =  strtoupper($row->persona_ofisac);
            $row->persona_adulto = ($row->persona_edad*1 > 17)?true:false;
            $row->direccion_mls = ($row->direccion_fuente_id*1 == 1)?true:false;
            $arr[] = $row;
        }
        $fin = microtime(true);
        error_log("procesamiento personas: " . ($fin - $ini));
        return $arr;
    }

    function paraActualizar($id) {
        $id = $this->_bd->limpia($id);

        $sql = "SELECT 
                    L.persona_nommls AS namemls, L.persona_nom AS name, COALESCE(L.persona_nom,'') AS oldname, COALESCE(R.persona_nom, '') AS newname,
                    CASE WHEN L.persona_nom IS NOT DISTINCT FROM R.persona_nom THEN False ELSE True END as nameChange,
                    L.persona_fecnac AS olddob, R.persona_fecnac AS newdob,
                    CASE WHEN L.persona_fecnac IS NOT DISTINCT FROM R.persona_fecnac THEN False ELSE True END as dobChange,
                    COALESCE(L.persona_email,'') AS oldemail, COALESCE(R.persona_email,'') As newemail,
                    CASE WHEN (COALESCE(L.persona_email,'')=COALESCE(R.persona_email,''))  THEN False ELSE True END as emailChange,
                    L.persona_telefono AS oldphone,R.persona_telefono AS newphone,
                    CASE WHEN L.persona_telefono IS NOT DISTINCT FROM R.persona_telefono OR ((L.persona_telefono IS NULL) AND (R.persona_telefono='')) THEN False ELSE True END as phoneChange,
                    L.persona_fecfall as olddeath,R.persona_fecfall as newdeath,
                    CASE WHEN L.persona_fecfall IS NOT DISTINCT FROM R.persona_fecfall THEN False ELSE True END as deathChange,
                    CASE WHEN d.direccion_fuente_id <> 1 AND R.persona_verificado THEN True ELSE False END as addressChange,
                    R.modificado, R.actualizado_mls, R.modificado_por, R.persona_id, R.persona_verificado, d.direccion, c.comuna_nom, re.region_nom
                FROM persona_log L
                LEFT JOIN persona R ON L.persona_id=R.persona_id
                LEFT JOIN direccion AS d ON R.persona_id = d.persona_id AND d.direccion_estado_id = 2 AND d.direccion_fuente_id <> 1
                LEFT JOIN region AS re ON d.region_id = re.region_id
                LEFT JOIN comuna AS c ON d.comuna_id = c.comuna_id
                WHERE 
                    L.barrio_id = $id 
                    AND (R.actualizado_mls < R.modificado OR R.actualizado_mls is NULL)
                    AND CASE WHEN R.actualizado_mls IS NULL 
                    THEN 
                        (persona_log_id IN 
                            (SELECT DISTINCT ON (persona_id) persona_log_id FROM persona_log ORDER BY persona_id, persona_log_id ASC))
                    ELSE
                        (persona_log_id IN 
                            (SELECT DISTINCT ON (persona_id) persona_log_id FROM persona_log WHERE modificado < R.actualizado_mls or R.actualizado_mls is NULL ORDER BY persona_id, persona_log_id DESC)) END";
        error_log($sql);
        $res = $this->_bd->sql($sql);
        $arr = array();
        while($row = pg_fetch_object($res)) {
            $row->namechange = ($row->namechange=="t")?true:false;
            $row->dobchange = ($row->dobchange=="t")?true:false;
            $row->emailchange = ($row->emailchange=="t")?true:false;
            $row->phonechange = ($row->phonechange=="t")?true:false;
            $row->deathchange = ($row->deathchange=="t")?true:false;
            $row->persona_verificado = ($row->persona_verificado=="t")?true:false;
            $row->addresschange = ($row->addresschange=="t")?true:false;
            $row->actualizado = false;
            if($row->namechange || $row->dobchange || $row->emailchange || $row->phonechange || $row->deathchange || $row->addresschange) {
                $arr[] = $row;
            }
        }

        return $arr;
    }

    function fetchEstados() {
        $sql = "SELECT * FROM persona_estado";
        $res = $this->_bd->sql($sql);
        $arr = array();
        while($row = pg_fetch_object($res)) {
            $arr[] = $row;
        }
        return $arr;
    }

}

?>