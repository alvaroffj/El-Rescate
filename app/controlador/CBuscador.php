<?php
/**
 * Description of CBuscador
 *
 * @author Alvaro Flores
 */
// include_once 'modelo/DireccionMP.php';

class CBuscador {
    protected $sc;

    function __construct($cp) {
        $this->cp = $cp;
        // $this->dirMP = new DireccionMP();
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
                case 'searchPersonas':
                    $_POST["rut"] = $this->cp->secureInput($_POST["rut"]);
                    $_POST["dir"] = $this->cp->secureInput($_POST["dir"]);
                    $_POST["nom"] = $this->cp->secureInput($_POST["nom"]);
                    $_POST["m"] = $this->cp->secureInput($_POST["m"]);
                    $_POST["c"] = $this->cp->secureInput( (isset($_POST["c"]) && $_POST["c"]!='')?$_POST["c"]:'0' );
                    $_POST["g"] = $this->cp->secureInput((isset($_POST["g"]) && $_POST["g"]!='')?$_POST["g"]:'0');
                    $_POST["od"] = $this->cp->secureInput((isset($_POST["od"]) && $_POST["od"]!='')?'true':'false');
                    $_POST["fec"] = (isset($_POST["fec"]) && $_POST["fec"]!='')?$_POST["fec"]:'0-0-0';
                    $fec = explode("-", $_POST["fec"]);
                    $url = "http://search.curi.co.uk/search-r.php?r=".urlencode($_POST["rut"])."&a=".urlencode($_POST["dir"])."&n=".urlencode($_POST["nom"])."&m=".urlencode($_POST["m"])."&c=".urlencode($_POST["c"])."&g=".urlencode($_POST["g"])."&dd=".urlencode($fec[2])."&dm=".urlencode($fec[1])."&dy=".urlencode($fec[0])."&od=".urlencode($_POST["od"]);
                    $res = json_decode($this->cp->curl($url));
                    $nRes = count($res);
                    for($i=0; $i<$nRes; $i++) {
                        $res[$i]->rut = $res[$i]->rut."-".$this->getDV($res[$i]->rut);
                    }
                    break;
                case 'searchBR':
                    $_POST["rut"] = $this->cp->secureInput($_POST["rut"]);
                    $_POST["dir"] = $this->cp->secureInput($_POST["dir"]);
                    $_POST["nom"] = $this->cp->secureInput($_POST["nom"]);
                    $_POST["m"] = $this->cp->secureInput($_POST["m"]);
                    $_POST["c"] = $this->cp->secureInput( (isset($_POST["c"]) && $_POST["c"]!='')?$_POST["c"]:'0' );
                    $_POST["g"] = $this->cp->secureInput((isset($_POST["g"]) && $_POST["g"]!='')?$_POST["g"]:'0');
                    $_POST["od"] = $this->cp->secureInput((isset($_POST["od"]) && $_POST["od"]!='')?'true':'false');
                    $_POST["fec"] = (isset($_POST["fec"]) && $_POST["fec"]!='')?$_POST["fec"]:'0-0-0';
                    $fec = explode("-", $_POST["fec"]);
                    $url = "http://search.curi.co.uk/search-br.php?r=".urlencode($_POST["rut"])."&a=".urlencode($_POST["dir"])."&n=".urlencode($_POST["nom"])."&m=".urlencode($_POST["m"])."&c=".urlencode($_POST["c"])."&g=".urlencode($_POST["g"])."&dd=".urlencode($fec[2])."&dm=".urlencode($fec[1])."&dy=".urlencode($fec[0])."&od=".urlencode($_POST["od"]);
                    $res = json_decode($this->cp->curl($url));
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
                default:
                    break;
            }
            header('Content-type: application/json');
            $res->logged = true;
            echo json_encode($res);
            die();
        }
    }

}

?>
