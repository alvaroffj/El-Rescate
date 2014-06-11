<?php
/**
 * Description of CDireccion
 *
 * @author Alvaro Flores
 */

// include 'modelo/PersonaMP.php';
include 'modelo/DireccionMP.php';

class CDireccion {
    protected $sc;

    function __construct($cp) {
        // $this->perMP = new PersonaMP();
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
                case 'update':
                    $_POST["modificado_por"] = $this->cp->getSession()->get("usuario_id");
                    $res = $this->dirMP->update((object)$_POST);
                    break;
                case 'insert':
                    $_POST["creado_por"] = $this->cp->getSession()->get("usuario_id");
                    $_POST["modificado_por"] = $this->cp->getSession()->get("usuario_id");
                    $res_id = $this->dirMP->insert((object)$_POST);
                    $res = $this->dirMP->find($res_id);
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
    
    function setGet() {
        if(isset($_GET["get"])) {
            $val = $_GET["get"];
            switch($val) {
                case 'estructura':
                    $res = new stdClass();
                    $res->regiones = $this->dirMP->fetchRegiones();
                    $res->comunas = $this->dirMP->fetchComunas();
                    $res->estados = $this->dirMP->fetchEstados();
                    $res->fuentes = $this->dirMP->fetchFuentes();
                    break;
                case 'regiones';
                    $res = $this->dirMP->fetchRegiones();
                    break;
                case 'comunas':
                    $res = $this->dirMP->fetchComunas();
                    break;
                case 'estados':
                    $res = $this->dirMP->fetchEstados();
                    break;
                case 'fuentes':
                    $res = $this->dirMP->fetchFuentes();
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
