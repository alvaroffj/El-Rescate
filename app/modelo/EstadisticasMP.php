<?php

/**
 * Description of EstadisticasMP
update estatisticas as e
set estaca_id = u.estaca_id
from barrio as u
where u.barrio_id = e.barrio_id

update estatisticas as e
set mision_id = u.mision_id
from estaca as u
where u.estaca_id = e.estaca_id 
 *
 * @author Alvaro Flores
 */
require_once 'Bd.php';

class EstadisticasMP {
    protected $_dbTable = "estatisticas";
    protected $_id = "when";
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

    function visitsVerifiedByUnidad($unidad, $nivel) {
        $unidad = $this->_bd->limpia($unidad);
        $nivel = $this->_bd->limpia($nivel);
        $nivel_id = array("", "barrio_id", "estaca_id", "mision_id");

        if($nivel != 0) 
            $sql = "select SUM(e.visited) as visited, SUM(e.verified) as verified, SUM(e.mlsupdated) AS mlsupdated, SUM(e.tomlsupdate) AS tomlsupdate, e.when from estatisticas as e where e.".$nivel_id[$nivel]." = ".$unidad." group by e.when order by e.when ASC";
        else 
            $sql = "select SUM(e.visited) as visited, SUM(e.verified) as verified, SUM(e.mlsupdated) AS mlsupdated, SUM(e.tomlsupdate) AS tomlsupdate, e.when from estatisticas as e group by e.when order by e.when ASC";

        $res = $this->_bd->sql($sql);
        $arr = array();
        while($row = pg_fetch_object($res)) {
            $arr[] = $row;
        }
        return $arr;
    }

    function fetchLastModifiedByUnidad($unidad, $nivel, $n) {
        $unidad = $this->_bd->limpia($unidad);
        $nivel = $this->_bd->limpia($nivel);
        $n = $this->_bd->limpia($n);
        $nivel_id = array("", "barrio_id", "estaca_id", "mision_id");

        if($nivel != 0) 
            $sql = "select p.persona_id, p.persona_nom, p.modificado, u.usuario_nom, u.usuario_ape, p.barrio_id 
                from persona as p inner join usuario as u on p.modificado_por = u.usuario_id where p.".$nivel_id[$nivel]." = ".$unidad." order by p.modificado DESC limit $n offset 0";
        else 
            $sql = "select p.persona_id, p.persona_nom, p.modificado, u.usuario_nom, u.usuario_ape, p.barrio_id 
                from persona as p inner join usuario as u on p.modificado_por = u.usuario_id order by p.modificado DESC limit $n offset 0";

        $res = $this->_bd->sql($sql);
        $arr = array();
        while($row = pg_fetch_object($res)) {
            $arr[] = $row;
        }
        return $arr;
    }

    function fetchLastComByUnidad($unidad, $nivel, $n) {
        $unidad = $this->_bd->limpia($unidad);
        $nivel = $this->_bd->limpia($nivel);
        $n = $this->_bd->limpia($n);
        $nivel_id = array("", "barrio_id", "estaca_id", "mision_id");

        if($nivel != 0) {
            $sql = "select c.comentario, c.creado, c.creado_por, p.persona_id, p.persona_nom, u.usuario_nom, u.usuario_ape, p.barrio_id 
                    from comentario as c
                        inner join persona as p
                            on c.persona_id = p.persona_id
                        inner join usuario as u 
                            on c.creado_por = u.usuario_id 
                    where 
                    c.".$nivel_id[$nivel]." = ".$unidad." 
                    order by c.creado DESC 
                    limit $n offset 0";
        } else {
            $sql = "select c.comentario, c.creado, c.creado_por, p.persona_id, p.persona_nom, u.usuario_nom, u.usuario_ape, p.barrio_id 
                from comentario as c
                    inner join persona as p
                        on c.persona_id = p.persona_id
                    inner join usuario as u 
                        on c.creado_por = u.usuario_id 
                order by c.creado DESC 
                limit $n offset 0";
        }

        $res = $this->_bd->sql($sql);
        $arr = array();
        while($row = pg_fetch_object($res)) {
            $arr[] = $row;
        }
        return $arr;
    }

    function fetchByFechaUnidad($fecha, $unidad, $nivel) {
        $fecha = $this->_bd->limpia($fecha);
        $unidad = $this->_bd->limpia($unidad);
        $nivel_id = array("", "barrio_id", "estaca_id", "mision_id");
        $groupby = array("mision_id", "barrio_id", "barrio_id", "estaca_id");
        $unidad_nom = array("mision_nom", "barrio_nom", "barrio_nom", "estaca_nom");
        $tabla_join = array("mision", "barrio", "barrio", "estaca");

        if($nivel != 0) {
            $uni = "AND e.".$nivel_id[$nivel]." = ".$unidad;
            // if($nivel > 1) {
                $uni .= " GROUP BY e.".$groupby[$nivel].", u.".$unidad_nom[$nivel];
            // }
            $join = "INNER JOIN ".$tabla_join[$nivel]." AS u ON u.".$groupby[$nivel]." = e.".$groupby[$nivel];
        } else {
            $uni = " GROUP BY e.".$groupby[$nivel].", u.".$unidad_nom[$nivel];
            $join = "INNER JOIN ".$tabla_join[$nivel]." AS u ON u.".$groupby[$nivel]." = e.".$groupby[$nivel];
        }
        $sql = "SELECT e.".$groupby[$nivel]." AS unidad_id, u.".$unidad_nom[$nivel]." AS unidad_nom, 
                    SUM(e.total) AS total,
                    SUM(e.deaddate) AS deaddate, ROUND(SUM(e.deaddate)*100.0/SUM(e.adult), 1) AS deaddate_p,
                    SUM(e.rut) AS rut, ROUND(SUM(e.rut)*100.0/SUM(e.adult), 1) AS rut_p,
                    SUM(e.adult) AS adult, ROUND(SUM(e.adult)*100.0/SUM(e.total), 1) AS adult_p,
                    SUM(e.visited) AS visited, ROUND(SUM(e.visited)*100.0/SUM(e.adult), 1) AS visited_p,
                    SUM(e.verified) AS verified, ROUND(SUM(e.verified)*100.0/SUM(e.adult), 1) AS verified_p,
                    SUM(e.outside) AS outside, ROUND(SUM(e.outside)*100.0/SUM(e.adult), 1) AS outside_p,
                    SUM(e.notfound) AS notfound, ROUND(SUM(e.notfound)*100.0/SUM(e.adult), 1) AS notfound_p,
                    SUM(e.mlsupdated) AS mlsupdated, ROUND(SUM(e.mlsupdated)*100.0/SUM(e.adult), 1) AS mlsupdated_p,
                    SUM(e.tomlsupdate) AS tomlsupdate, ROUND(SUM(e.tomlsupdate)*100.0/SUM(e.adult), 1) AS tomlsupdate_p,
                    SUM(e.active) AS active, ROUND(SUM(e.active)*100.0/SUM(e.adult), 1) AS active_p,
                    SUM(e.lessactivenotreceptive) AS lessactivenotreceptive, ROUND(SUM(e.lessactivenotreceptive)*100.0/SUM(e.adult), 1) AS lessactivenotreceptive_p,
                    SUM(e.returning) AS returning, ROUND(SUM(e.returning)*100.0/SUM(e.adult), 1) AS returning_p,
                    SUM(e.lessactivereceptive) AS lessactivereceptive, ROUND(SUM(e.lessactivereceptive)*100.0/SUM(e.adult), 1) AS lessactivereceptive_p,
                    SUM(e.moved) AS moved, ROUND(SUM(e.moved)*100.0/SUM(e.adult), 1) AS moved_p,
                    SUM(e.dead) AS dead, ROUND(SUM(e.dead)*100.0/SUM(e.adult), 1) AS dead_p,
                    SUM(e.ldsmaps) AS ldsmaps, ROUND(SUM(e.ldsmaps)*100.0/SUM(e.adult), 1) AS ldsmaps_p
                FROM $this->_dbTable AS e 
                ".$join."
                WHERE e.when = '$fecha' ".$uni;
        
        // error_log($sql);
        $res = $this->_bd->sql($sql);
        // $arr = array();
        // while($row = pg_fetch_object($res)) {
        //     $arr[] = $row;
        // }
        $arr = array_values(pg_fetch_all($res));
        return $arr;
    }

    function fetchUserByUnidad($unidad, $nivel) {
        $unidad = $this->_bd->limpia($unidad);
        $nivel_id = array("", "barrio_id", "estaca_id", "mision_id");
        $groupby = array("mision_id", "barrio_id", "barrio_id", "estaca_id");
        $unidad_nom = array("mision_nom", "barrio_nom", "barrio_nom", "estaca_nom");
        $tabla_join = array("mision", "barrio", "barrio", "estaca");

        if($nivel > 0) {
            $wh = "LEFT JOIN usuario_acceso
                        ON usuario_acceso.usuario_id = usuario.usuario_id
                    WHERE usuario.usuario_id > 0
                        AND usuario_acceso.".$nivel_id[$nivel]." = ".$unidad." AND usuario.usuario_id > 0";
        } else {
            $wh = "WHERE usuario.usuario_id > 0";
        }

        $sql = "SELECT usuario.usuario_id, count (A.persona_id) AS num_mod, usuario.usuario_nom, usuario.usuario_ape, usuario.usuario_user, 
                            usuario.usuario_lastaccess::timestamp(0)
                FROM usuario
                     LEFT JOIN
                     (SELECT persona_id, modificado_por, mision_id FROM persona
                      UNION
                      SELECT persona_id, modificado_por, mision_id FROM persona_log) A
                        ON A.modificado_por = usuario.usuario_id
                     $wh
            GROUP BY usuario.usuario_id
            ORDER BY num_mod DESC";

        // error_log($sql);
        $res = $this->_bd->sql($sql);
        $arr = array();
        while($row = pg_fetch_object($res)) {
            $row->num_mod = $row->num_mod*1;
            $arr[] = $row;
        }
        return $arr;
    }

    function getComByUser($id, $n) {
        $id = $this->_bd->limpia($id);
        $n = $this->_bd->limpia($n);

        $sql = "select c.comentario, c.creado, c.creado_por, p.persona_id, p.persona_nom, u.usuario_nom, u.usuario_ape, p.barrio_id 
                from comentario as c
                    inner join persona as p
                        on c.persona_id = p.persona_id
                    inner join usuario as u 
                        on c.creado_por = u.usuario_id 
                where c.creado_por = $id
                order by c.creado DESC 
                limit $n offset 0";

        $res = $this->_bd->sql($sql);
        $com = array();
        while($row = pg_fetch_object($res)) {
            $com[] = $row;
        }

        $final = new stdClass();
        $final->last = $com;

        $sql = "select count(creado_por) as num, comentario.barrio_id, barrio.barrio_nom as unidad 
                from comentario 
                inner join barrio on barrio.barrio_id = comentario.barrio_id
                where creado_por = $id 
                group by creado_por, comentario.barrio_id, unidad";
        $res = $this->_bd->sql($sql);
        $barrio = array();
        $n = 0;
        while($row = pg_fetch_object($res)) {
            $row->num = $row->num*1;
            $n += $row->num;
            $barrio[] = $row;
        }
        $final->num = $n;
        $final->por_barrio = $barrio;
        return $final;
    }

    function getModByUser($id, $n) {
        $id = $this->_bd->limpia($id);

        $sql = "select p.persona_id, p.persona_nom, p.modificado, u.usuario_nom, u.usuario_ape, p.barrio_id 
                from persona as p 
                inner join usuario as u on u.usuario_id = p.modificado_por
                where p.modificado_por = $id
                order by p.modificado DESC
                limit $n offset 0";

        $res = $this->_bd->sql($sql);
        $mod = array();
        while($row = pg_fetch_object($res)) {
            $mod[] = $row;
        }

        $sql = "SELECT count (A.persona_id) AS num, A.barrio_id, barrio.barrio_nom AS unidad
                FROM usuario
                     LEFT JOIN
                     (SELECT persona_id, modificado_por, barrio_id FROM persona
                      UNION
                      SELECT persona_id, modificado_por, barrio_id FROM persona_log) A
                        ON A.modificado_por = usuario.usuario_id
                    INNER JOIN barrio ON A.barrio_id = barrio.barrio_id
                     WHERE usuario.usuario_id = $id
            GROUP BY usuario.usuario_id, A.barrio_id, barrio.barrio_nom
            ORDER BY num DESC";

        $res = $this->_bd->sql($sql);
        $barrio = array();
        $n = 0;
        while($row = pg_fetch_object($res)) {
            $row->num = $row->num*1;
            $n += $row->num;
            $barrio[] = $row;
        }

        $final = new stdClass();
        $final->last = $mod;
        $final->num = $n;
        $final->por_barrio = $barrio;
        return $final;
    }

    function getModByUserFecha($id) {
        $id = $this->_bd->limpia($id);
        $sql = "SELECT COUNT(*) AS num_mod, modificado
                    FROM (SELECT persona_id, modificado_por, to_char(modificado, 'YYYY-MM-DD') AS modificado
                            FROM persona
                           WHERE persona.modificado_por = $id
                          UNION DISTINCT
                          SELECT persona_id, modificado_por, to_char(modificado, 'YYYY-MM-DD') AS modificado
                            FROM persona_log
                           WHERE persona_log.modificado_por = $id) A
                GROUP BY modificado
                ORDER BY modificado ASC";

        $res = $this->_bd->sql($sql);
        $arr = array();
        while($row = pg_fetch_object($res)) {
            $row->num_mod = $row->num_mod*1;
            $arr[] = $row;
        }
        return $arr;
    }

    function getComByUserFecha($id) {
        $id = $this->_bd->limpia($id);

        $sql = "select count(*) as num_com, to_char(creado,'yyyy-mm-dd') as dia 
                from comentario where creado_por = $id group by dia order by dia asc";

        $res = $this->_bd->sql($sql);
        $arr = array();
        while($row = pg_fetch_object($res)) {
            $row->num_com = $row->num_com*1;
            $arr[] = $row;
        }
        return $arr;
    }
}

?>