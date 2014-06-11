<?php
/**
 * Description of CMiembros
 *
 * @author Alvaro Flores
 */

include 'modelo/PersonaMP.php';
include 'modelo/DireccionMP.php';

class CMiembros {
    protected $sc;

    function __construct($cp) {
        $this->perMP = new PersonaMP();
        $this->dirMP = new DireccionMP();
        $this->cp = $cp;
        $this->setDo();
        $this->setGet();
    }
    
    function setDo() {
        if(isset($_GET["do"])) {
            $this->cp->showLayout = false;
            $val = $_GET["do"];
            switch($val) {
                case 'save':
                    unset($_POST["barrio_nom"]);
                    unset($_POST["estaca_nom"]);
                    unset($_POST["mision_nom"]);
                    unset($_POST["persona_estado"]);
                    unset($_POST["modificado"]);
                    unset($_POST["direccion"]);
                    unset($_POST["logged"]);
                    unset($_POST["persona_edad"]);
                    unset($_POST["persona_fecnac_h"]);
                    if(strlen($_POST["persona_rut_h"])>5) {
                        $_POST["persona_rut_h"] = preg_replace("/\./", "", $_POST["persona_rut_h"]);
                        $_POST["persona_rut_h"] = preg_replace("/\-/", "", $_POST["persona_rut_h"]);
                        $_POST["persona_rut"] = substr($_POST["persona_rut_h"], 0, -1);
                        unset($_POST["persona_rut_h"]);
                    } else {
                        $_POST["persona_rut"] = "null";
                        unset($_POST["persona_rut_h"]);
                    }
                    $_POST["persona_fecfall"] = (strlen($_POST["persona_fecfall"])>0)?$_POST["persona_fecfall"]:"null";
                    $_POST["persona_fecnac"] = (strlen($_POST["persona_fecnac"])>0)?$_POST["persona_fecnac"]:"null";
                    $_POST["persona_email"] = (strlen($_POST["persona_email"])>0)?$_POST["persona_email"]:"null";
                    $_POST["persona_telefono"] = (strlen($_POST["persona_telefono"])>0)?$_POST["persona_telefono"]:"null";
                    $_POST["modificado_por"] = $this->cp->getSession()->get("usuario_id");
                    $upd = $this->perMP->update((object)$_POST);
                    $res->ERROR = ($upd) ? 0 : 1;
                    if($upd) $res->MENSAJE = "Los datos fueron actualizados correctamente";
                    else $res->MENSAJE = "Los datos fueron no actualizados, intentelo nuevamente";
                    $per = $this->perMP->find($_POST["persona_id"]);
                    $res->persona = $per;
                    break;
                case 'verificar':
                    $idDir = $_POST["direccion_id"];
                    unset($_POST["direccion_id"]);
                    $_POST["modificado_por"] = $this->cp->getSession()->get("usuario_id");
                    $ver = $this->perMP->update((object)$_POST);
                    $dir = $this->dirMP->verificar($idDir, $_POST["persona_id"]);
                    $per = $this->perMP->find($_POST["persona_id"]);
                    $res->ERROR = ($ver && $dir) ? 0 : 1;
                    $res->MENSAJE = "Los datos fueron verificados correctamente";
                    $per->direccion = $this->dirMP->fetchByPersona($_POST["persona_id"]);
                    $res->persona = $per;
                    break;
                case 'undoverificar':
                    $_POST["modificado_por"] = $this->cp->getSession()->get("usuario_id");
                    $ver = $this->perMP->update((object)$_POST);
                    $per = $this->perMP->find($_POST["persona_id"]);
                    // $res->ver = $ver;
                    // $res->dir = $dir;
                    $res->ERROR = ($ver) ? 0 : 1;
                    if($ver) $res->MENSAJE = "La verificación fue anulada correctamente";
                    else $res->MENSAJE = "La verificación no pudo ser realizada, intentelo nuevamente";
                    $per->direccion = $this->dirMP->fetchByPersona($_POST["persona_id"]);
                    $res->persona = $per;
                    break;
                case 'mlsactualizar':
                    $mls = $this->perMP->mlsActualizar($_POST['id'], $this->cp->getSession()->get("usuario_id"));
                    // $res = new stdClass();
                    $res->ERROR = ($mls->error) ? 0 : 1;
                    unset($mls->error);
                    $res->MENSAJE = "La actualización de MLS se registró correctamente";
                    $res->res = $mls;
                    break;
                default:
                    break;
            }
            $res->logged = true;
            header('Content-type: application/json');
            echo json_encode($res);
            die();
        }
    }

    function validaAccesoUnidad($acc, $uni, $nivel) {
        $nivelVar = array('', 'barrio', 'estaca', 'mision');
        $attrVal = array('', 'barrio_id', 'estaca_id', 'mision_id');
        $nivelVal = $nivelVar[$nivel];
        $arr = $acc->$nivelVal;
        $attr = $attrVal[$nivel];
        $nVal = count($arr);
        for($i=0; $i<$nVal; $i++) {
            if($arr[$i]->$attr == $uni) {
                return true;
            }
        }
        return false;
    }

    function getDiff($ant, $cur, $attr, $attrNom, $diffOnly = true) {
        $nAttr = count($attr);
        $aux = new stdClass();
        $aux->usuario = $cur->usuario_nom . ' ' . $cur->usuario_ape;
        $aux->fecha = $cur->modificado;
        $aux->log = array();
        for($j=0; $j<$nAttr; $j++) {
            $at = $attr[$j];
            if(is_bool($ant->$at) && $ant->$at == true) {
                $ant->$at = 'Si';
            } else if(is_bool($ant->$at) && $ant->$at == false) {
                $ant->$at = 'No';
            }
            if(is_bool($cur->$at) && $cur->$at == true) {
                $cur->$at = 'Si';
            } else if(is_bool($cur->$at) && $cur->$at == false) {
                $cur->$at = 'No';
            }
            if(strcmp($ant->$at, $cur->$at)!=0) {
                $dif = new stdClass();
                $dif->attr = $at;
                $dif->attrNom = $attrNom[$j];
                $dif->ant = $ant->$at;
                $dif->cur = $cur->$at;
                if($cur->$at == '') {
                    $dif->tipo = 'del';
                } else $dif->tipo = 'add';
                $aux->log[] = $dif;
            } else if(!$diffOnly) {
                $dif = new stdClass();
                $dif->attr = $at;
                $dif->attrNom = $attrNom[$j];
                $dif->ant = $ant->$at;
                $dif->cur = $cur->$at;
                $dir->tipo = '';
                $aux->log[] = $dif;
            }
        }
        return $aux;
    }
    
    function setGet() {
        if(isset($_GET["get"])) {
            $val = $_GET["get"];
            $res = new stdClass();
            switch($val) {
                case 'all':
                    $acc = $this->cp->getSession()->get("acceso");
                    $nivel = -1;
                    if(isset($_POST["barrio_id"]) && $_POST["barrio_id"] != '') {
                        $unidad = $_POST["barrio_id"];
                        $nivel = 1;
                    } else if(isset($_POST["estaca_id"]) && $_POST["estaca_id"] != '') {
                        $unidad = $_POST["estaca_id"];
                        $nivel = 2;
                    } else if(isset($_POST["mision_id"]) && $_POST["mision_id"] != '') {
                        $unidad = $_POST["mision_id"];
                        $nivel = 3;
                    }

                    if($nivel>0) {
                        if($this->validaAccesoUnidad($acc, $unidad, $nivel)) {
                            $res = $this->perMP->fetchAll($unidad, $nivel, true);
                        } else {
                            $res = false;
                        }
                    } else {
                        $res = false;
                    }
                    break;
                case 'det':
                    $res = $this->perMP->find($_POST["id"]);
                    $acc = $this->cp->getSession()->get("acceso");
                    if($this->validaAccesoUnidad($acc, $res->barrio_id, 1)) {
                        $res->direccion = $this->dirMP->fetchByPersona($_POST["id"]);
                    } else 
                        $res = false;
                    break;
                case 'estados':
                    $res = $this->perMP->fetchEstados();
                    break;
                case 'log':
                    $log = $this->perMP->fetchLog($_POST["id"]);
                    $act = $this->perMP->find($_POST["id"]);
                    $log[] = $act;
                    $attr = array('persona_nom', 'persona_rut', 'persona_telefono', 'persona_email', 'persona_fecnac_h', 'persona_fecfall_h', 'persona_estado', 'persona_no_rut','persona_visitado', 'persona_verificado', 'actualizado_mls');
                    $attrNom = array('Nombre', 'Rut', 'Teléfono', 'Email', 'Nacimiento', 'Defunción', 'Estado', 'Rut no encontrado', 'Visitado', 'Verificado', 'Actualizado MLS');
                    
                    $hist = array();
                    $nLog = count($log);
                    for($i=1; $i<$nLog; $i++) {
                        $ant = $log[$i-1];
                        $cur = $log[$i];
                        $hist[] = $this->getDiff($ant, $cur, $attr, $attrNom);
                    }
                    $res = new stdClass();
                    $res->log = $log;
                    $res->hist = $hist;
                    $res->resumen = $this->getDiff($log[0], $act, $attr, $attrNom, false);
                    break;
                case 'familia':
                    $res = $this->perMP->fetchFamilia($_POST["id"]);
                    break;
                default:
                    break;
            }
            header('Content-type: application/json');
            // $res->logged = true;
            echo json_encode($res);
            die();
        }
    }

}

?>
