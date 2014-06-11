<?php
/**
 * Description of CReportes
 *
 * @author Alvaro Flores
 */
include_once 'modelo/PersonaMP.php';

class CReportes {
    protected $sc;

    function __construct($cp) {
        $this->cp = $cp;
        $this->perMP = new PersonaMP();
        $this->setDo();
        $this->setGet();
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
                case 'reporte':
                    switch($_POST["reporte"]) {
                        case 'avisitar':
                            // $_POST["barrio_id"] = $this->cp->secureInput($_POST["barrio_id"]);
                            // $url = "http://search.curi.co.uk/elrescate/ToVisitJson.php?filter=&wid=".$_POST["barrio_id"];
                            // $res = json_decode($this->cp->curl($url));
                            $res = $this->perMP->paraVisitar($_POST["barrio_id"]);
                            break;
                        case 'actualizarmls':
                            $res = $this->perMP->paraActualizar($_POST["barrio_id"]);
                            break;
                    }
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
