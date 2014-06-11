<?php
/**
 * Description of CComentario
 *
 * @author Alvaro Flores
 */

include 'modelo/ComentarioMP.php';

class CComentarios {
    protected $sc;

    function __construct($cp) {
        // $this->perMP = new PersonaMP();
        $this->comMP = new ComentarioMP();
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
                    $res = $this->comMP->update((object)$_POST);
                    break;
                case 'insert':
                    $_POST["creado_por"] = $this->cp->getSession()->get("usuario_id");
                    $_POST["modificado_por"] = $this->cp->getSession()->get("usuario_id");
                    $res_id = $this->comMP->insert((object)$_POST);
                    $res = $this->comMP->find($res_id);
                    break;
                default:
                    break;
            }
            // $res->logged = true;
            header('Content-type: application/json');
            echo json_encode($res);
            die();
        }
    }
    
    function setGet() {
        if(isset($_GET["get"])) {
            $val = $_GET["get"];
            switch($val) {
                case 'all':
                    $res = $this->comMP->fetchByPersona($_POST['id']);
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
