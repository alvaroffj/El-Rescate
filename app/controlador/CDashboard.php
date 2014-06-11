<?php
/**
 * Description of CDashboard
 *
 * @author Alvaro Flores
 */
include_once 'modelo/EstadisticasMP.php';
include_once 'modelo/UsuarioMP.php';

class CDashboard {
    protected $sc;

    function __construct($cp) {
        $this->cp = $cp;
        $this->estMP = new EstadisticasMP();
        $this->usMP = new UsuarioMP();
        $this->setDo();
        $this->setGet();
    }

    function setDo() {
        if(isset($_GET["do"])) {
            $this->cp->showLayout = false;
            $val = $_GET["do"];
            switch($val) {
                default:
                    break;
            }
            $res->logged = true;
            header('Content-type: application/json');
            echo json_encode($res);
            die();
        }
    }
    
    function setGet() {
        if(isset($_GET["get"])) {
            $val = $_GET["get"];
            switch($val) {
                case 'usuario':
                    $res = new stdClass();
                    if($this->cp->getSession()->get("usuario_permisos_usuarios") > 0 
                        || $this->cp->getSession()->get("usuario_id") == $_POST["id"]) {
                        $res->perfil = $this->usMP->find($_POST["id"]);
                        $res->mod_datos = $this->estMP->getModByUser($_POST["id"], 20);
                        $res->mod_datos->tiempo = $this->estMP->getModByUserFecha($_POST["id"]);
                        $res->com_datos = $this->estMP->getComByUser($_POST["id"], 20);
                        $res->com_datos->tiempo = $this->estMP->getComByUserFecha($_POST["id"]);
                    }
                    break;
                case 'usuarios':
                    $nivel = -1;
                    $unidad = -1;
                    if(isset($_POST["barrio_id"]) && $_POST["barrio_id"]) {
                        $nivel = 1;
                        $unidad = $_POST["barrio_id"];
                    } else if(isset($_POST["estaca_id"]) && $_POST["estaca_id"]) {
                        $nivel = 2;
                        $unidad = $_POST["estaca_id"];
                    } else if(isset($_POST["mision_id"]) && $_POST["mision_id"]) {
                        $nivel = 3;
                        $unidad = $_POST["mision_id"];
                    } else if(isset($_POST["area_id"]) && $_POST["area_id"]) {
                        $nivel = 0;
                        $unidad = $_POST["area_id"];
                    }
                    $res = new stdClass();
                    if($nivel >= 0 && $unidad*1 > 0) {
                        $res->usuarios = $this->estMP->fetchUserByUnidad($unidad, $nivel);
                    }
                    break;
                case 'all':
                    $nivel = -1;
                    $unidad = -1;
                    if(isset($_POST["barrio_id"]) && $_POST["barrio_id"]) {
                        $nivel = 1;
                        $unidad = $_POST["barrio_id"];
                    } else if(isset($_POST["estaca_id"]) && $_POST["estaca_id"]) {
                        $nivel = 2;
                        $unidad = $_POST["estaca_id"];
                    } else if(isset($_POST["mision_id"]) && $_POST["mision_id"]) {
                        $nivel = 3;
                        $unidad = $_POST["mision_id"];
                    } else if(isset($_POST["area_id"]) && $_POST["area_id"]) {
                        $nivel = 0;
                        $unidad = $_POST["area_id"];
                    }
                    $res = new stdClass();
                    if($nivel >= 0 && $unidad*1 > 0) {
                        $res->unidades = $this->estMP->fetchByFechaUnidad($_POST["fecha"], $unidad, $nivel);
                        $res->visVer = $this->estMP->visitsVerifiedByUnidad($unidad, $nivel);
                        $res->lastMod = $this->estMP->fetchLastModifiedByUnidad($unidad, $nivel, 20);
                        $res->lastCom = $this->estMP->fetchLastComByUnidad($unidad, $nivel, 20);
                    }
                    break;
                default:
                    break;
            }
            header('Content-type: application/json');
            echo json_encode($res);
            die();
        }
    }

}

?>
