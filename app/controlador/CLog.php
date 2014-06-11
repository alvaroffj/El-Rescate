<?php
/**
 * Description of CLog
 *
 * @author Alvaro Flores
 */

include_once 'modelo/UsuarioMP.php';

class CLog {
    function __construct($cp, $run = true) {
        $this->usMP = new UsuarioMP();
        $this->cp = $cp;
        $this->cp->layout = 'login.html';
        if($run) {
            $this->setGet();
            $this->setDo();
        }
    }
    
    function setDo() {
        if(isset($_GET["do"])) {
            $this->cp->showLayout = false;
            $val = $_GET["do"];
            switch($val) {
                case 'in':
                    $res = $this->usMP->checkLogin($_POST["user"], $_POST["pass"]);
                    if(isset($res->usuario_id)) {
                        $this->cp->getSession()->set("usuario_id", $res->usuario_id);
                        $this->cp->getSession()->set("usuario_user", $res->usuario_user);
                        $this->cp->getSession()->set("usuario_nom", $res->usuario_nom);
                        $this->cp->getSession()->set("usuario_ape", $res->usuario_ape);
                        $this->cp->getSession()->set("usuario_permisos_usuarios", $res->usuario_permisos_usuarios);
                        include_once 'modelo/DireccionMP.php';
                        $this->dirMP = new DireccionMP();

                        $acc = $this->usMP->fetchAcceso($this->cp->getSession()->get("usuario_id"));
                        
                        $nAcc = count($acc);
                        if($nAcc > 0) {
                            // $res = new stdClass();
                            $res->mision = array();
                            $res->estaca = array();
                            $res->barrio = array();

                            for($i=0; $i<$nAcc; $i++) {
                                switch($acc[$i]->usuario_acceso_nivel) {
                                    case 0:
                                        $res->mision = $this->dirMP->fetchUnidades(3);
                                        $res->estaca = $this->dirMP->fetchUnidades(2);
                                        $res->barrio = $this->dirMP->fetchUnidades(1);
                                        $res->area = array();
                                        $chile = new stdClass();
                                        $chile->area_id = "1";
                                        $chile->area_nom = "Chile";
                                        $res->area[] = $chile;
                                        break;
                                    case 1:
                                        $aux = $this->dirMP->findUnidad($acc[$i]->barrio_id, $acc[$i]->usuario_acceso_nivel);
                                        $res->barrio[] = $aux;
                                        break;
                                    case 2:
                                        $aux = $this->dirMP->findUnidad($acc[$i]->estaca_id, $acc[$i]->usuario_acceso_nivel);
                                        $res->estaca[] = $aux;
                                        $auxUn = $this->dirMP->fetchByUnidad($acc[$i]->estaca_id, $acc[$i]->usuario_acceso_nivel, 0);
                                        $res->barrio = array_merge($res->barrio, $auxUn);
                                        break;
                                    case 3:
                                        $aux = $this->dirMP->findUnidad($acc[$i]->mision_id, $acc[$i]->usuario_acceso_nivel);
                                        $res->mision[] = $aux;
                                        $auxUn = $this->dirMP->fetchByUnidad($acc[$i]->mision_id, $acc[$i]->usuario_acceso_nivel, 1);
                                        $res->estaca = array_merge($res->estaca, $auxUn);
                                        $auxUn = $this->dirMP->fetchByUnidad($acc[$i]->mision_id, $acc[$i]->usuario_acceso_nivel, 0);
                                        $res->barrio = array_merge($res->barrio, $auxUn);
                                        break;
                                }
                            }
                        } else {
                            // $res = false;
                        }
                        $carga = new stdClass();
                        $this->cp->getSession()->set("carga", $carga);
                        $this->cp->getSession()->set("acceso", $res);
                        $res->logged = true;
                    } else {
                        $res->logged = false;
                    }
                    break;
                case 'out':
                    $this->cp->getSession()->kill();
                    $this->cp->getSession()->salto("/");
                    $res->logged = false;
                    break;
                case 'rec':
                    if(isset($_POST["usuario_user"]) && $_POST["usuario_user"]!='') {
                        $us = $this->usMP->findByUser($_POST["usuario_user"]);
                        if($us && $us->usuario_id > 0) {
                            $aux = new stdClass();
                            $aux->usuario_id = $us->usuario_id;
                            $aux->usuario_pass = $this->usMP->genPassword(12);

                            $res = $this->usMP->update($aux);
                            if($res) {
                                $nom = $us->usuario_nom . " " . $us->usuario_ape;
                                $txt = "<h2>Estimado(a) $nom, su nueva clave de acceso es:</h2>
                                        <span style='font-weight: bold; padding: 10px 20px; border: 1px solid #ccc; font-size: 18px;'>".$aux->usuario_pass."</span>";
                                $this->cp->enviarEmail($nom, $us->usuario_user, "El Rescate - Recuperar contraseña", $txt);
                                $res->ERROR = 0;
                                $res->MENSAJE = "La contraseña fue enviada correctamente, revise su email";
                            } else {
                                $res->ERROR = 1;
                                $res->MENSAJE = "La contraseña no pudo ser enviada";
                            }
                        } else {
                            $res->ERROR = 1;
                            $res->MENSAJE = "La cuenta no existe";
                        }
                    }
                    break;
                case 'addFull':
                    $res = new stdClass();
                    if(isset($_POST["usuario_user"]) && $_POST["usuario_user"] != '' 
                        && isset($_POST["permisos_usuarios"]) && is_numeric($_POST["permisos_usuarios"]*1)
                        && isset($_POST["unidad"]) && is_numeric($_POST["unidad"]*1)
                        && isset($_POST["nivel"]) && is_numeric($_POST["nivel"]*1)) {
                        $aux = explode("@", $_POST["usuario_user"]);
                        if(count($aux) == 2) {
                            if($this->usMP->disponible($_POST["usuario_user"])) {
                                $user = new stdClass();
                                $user->usuario_user = $_POST["usuario_user"];
                                $user->usuario_permisos_usuarios = $_POST["permisos_usuarios"];
                                $user->usuario_estado = 1;
                                $user->usuario_pass = $this->usMP->genPassword(12);
                                $user->usuario_nom = $_POST["usuario_nom"];
                                $user->usuario_ape = $_POST["usuario_ape"];
                                $user->usuario_id = $this->usMP->insert((object)$user);
                                if($user->usuario_id) {
                                    $user_acceso = new stdClass();
                                    $user_acceso->usuario_id = $user->usuario_id;
                                    if($_POST["nivel"]*1 == 1) {
                                        $user_acceso->barrio_id = $_POST["unidad"];
                                    } else if($_POST["nivel"]*1 == 2) {
                                        $user_acceso->estaca_id = $_POST["unidad"];
                                    } else if($_POST["nivel"]*1 == 3) {
                                        $user_acceso->mision_id = $_POST["unidad"];
                                    }
                                    $user_acceso->usuario_acceso_nivel = $_POST["nivel"];
                                    $user_acceso = $this->usMP->addAcceso($user_acceso);

                                    $bienvenida = "<p>Bienvenidos a la obra de salvación de rescatar los miembros perdidos y encontrar muchos nuevos investigadores. Algunos de estos miembros van a responder a las tres cosas que cada nuevo converso y menos activo necesitan:</p>
                                                    <ol>
                                                    <li>Un amigo</li>
                                                    <li>Una asignación</li>
                                                    <li>Ser nutridos por la buena palabra de Dios</li>
                                                    </ol>
                                                    <p>Que el Señor les bendiga en esta obra de salvación</p>

                                                    <h3>Instrucciones básicas:</h3>

                                                    <p>Al abrir su unidad va a encontrar que aproximadamente 80% de los adultos tiene RUT y las nuevas direcciones del Servicio Servel y Bienes Raíces que corresponden al RUT. Los 20% que no tienen RUT y resultados de la base de datos es por causa del error ortográfico del nombre.  Puede empezar inmediatamente con estos pasos sencillos:</p>
                                                    <ol>
                                                    <li>Encontrar los errores ortográficos de los nombres para encontrar el RUT y las direcciones que corresponden de los 20% de los adultos sin resultados. Se puede empezar inmediatamente con los 80% con resultados.</li>
                                                    <li>En la sección Reportes se puede crear una lista de miembros con las direcciones filtradas. Hay que empezar con una lista de un sector chico.</li>
                                                    <li>Consulta con los lideres y los miembros sobre los nombres y las direcciones en la lista antes de hacer la búsqueda o caminar a encontrarlos. </li>
                                                    <li>Trabaja con los miembros bajo la dirección del obispado y el consejo del barrio. Los misioneros y los miembros tienen que trabajar juntos para verificar todos y rescatarlos.</li>
                                                    <li>Hable con todos al buscar los miembros en la lista. Es fácil tener una conversación con todos cuando está buscando un nombre. Hay que introducir el evangelio en estas conversaciones.</li>
                                                    <li>Actualiza la información del miembro cada semana en elrescate.cl. Si hay muchos cambios puede imprimir una nueva lista del sector.</li>
                                                    <li>Trabaja con los secretarios y los lideres para asegurar que MLS se actualiza con las nuevas direcciones y datos de contacto.</li>
                                                    <li>Si un miembro ha cambiado su domicilio fuera de su barrio es importante que obtenga la nueva dirección. No se puede enviar un registro de un miembro afuera hasta que tenga la nueva dirección.</li>
                                                    <li>Trabaja con los secretarios y los lideres para asegurar que los miembros están ubicado en el mapa del barrio en lds.org.</li>
                                                    <li>La meta es de encontrar todos los miembros, rescatar los menos activos, encontrar muchos nuevos investigadores, actualizar MLS con las direcciones correctas y ubicar todos los miembros en el mapa de su barrio en lds.org.</li>
                                                    </ol>";
                                    $nom = $_POST["usuario_nom"].' '.$_POST["usuario_ape"];
                                    $txt = "<h2>Bienvenido(a) $nom, su clave de acceso es:</h2>
                                            <span style='font-weight: bold; padding: 10px 20px; border: 1px solid #ccc; font-size: 18px;'>".$user->usuario_pass."</span>
                                            <div style='margin-top: 30px; text-align:left;'>$bienvenida</div>";
                                    $this->cp->enviarEmail($nom, $_POST["usuario_user"], "Bienvenido a El Rescate", $txt);

                                    $res->ERROR = 0;
                                    $res->MENSAJE = "La cuenta fue creada correctamente";
                                } else {
                                    $res->ERROR = 1;
                                    $res->MENSAJE = "La cuenta NO pudo ser creada";
                                }
                            } else {
                                $res->ERROR = 1;
                                $res->MENSAJE = "El usuario ya existe";
                            }
                        } else {
                            $res->ERROR = 1;
                            $res->MENSAJE = "El usuario no es valido";
                        }
                    } else {

                    }
                    break;
                case 'add':
                    $res = new stdClass();
                    if(isset($_POST["usuario_user"]) && $_POST["usuario_user"]!='') {
                        $aux = explode("@", $_POST["usuario_user"]);
                        if(count($aux) == 2) {
                            $_POST["usuario_pass"] = $this->usMP->genPassword(12);
                            if($aux[1] == 'myldsmail.net') { //misionero
                                $mision = $_POST["mision_id"];
                            } else {
                                $mision = false;   
                            }
                            if($this->usMP->disponible($_POST["usuario_user"])) {
                                unset($_POST["mision_id"]);
                                $_POST["usuario_estado"] = 1;
                                $add = $this->usMP->insert((object)$_POST);
                                if($mision) {
                                    $acc = new stdClass();
                                    $acc->usuario_id = $add;
                                    $acc->mision_id = $mision;
                                    $acc->usuario_acceso_nivel = 3;
                                    $acc = $this->usMP->addAcceso($acc);
                                }
                                if($add) {
                                    $bienvenida = "<p>Bienvenidos a la obra de salvación de rescatar los miembros perdidos y encontrar muchos nuevos investigadores. Algunos de estos miembros van a responder a las tres cosas que cada nuevo converso y menos activo necesitan:</p>
                                                    <ol>
                                                    <li>Un amigo</li>
                                                    <li>Una asignación</li>
                                                    <li>Ser nutridos por la buena palabra de Dios</li>
                                                    </ol>
                                                    <p>Que el Señor les bendiga en esta obra de salvación</p>

                                                    <h3>Instrucciones básicas:</h3>

                                                    <p>Al abrir su unidad va a encontrar que aproximadamente 80% de los adultos tiene RUT y las nuevas direcciones del Servicio Servel y Bienes Raíces que corresponden al RUT. Los 20% que no tienen RUT y resultados de la base de datos es por causa del error ortográfico del nombre.  Puede empezar inmediatamente con estos pasos sencillos:</p>
                                                    <ol>
                                                    <li>Encontrar los errores ortográficos de los nombres para encontrar el RUT y las direcciones que corresponden de los 20% de los adultos sin resultados. Se puede empezar inmediatamente con los 80% con resultados.</li>
                                                    <li>En la sección Reportes se puede crear una lista de miembros con las direcciones filtradas. Hay que empezar con una lista de un sector chico.</li>
                                                    <li>Consulta con los lideres y los miembros sobre los nombres y las direcciones en la lista antes de hacer la búsqueda o caminar a encontrarlos. </li>
                                                    <li>Trabaja con los miembros bajo la dirección del obispado y el consejo del barrio. Los misioneros y los miembros tienen que trabajar juntos para verificar todos y rescatarlos.</li>
                                                    <li>Hable con todos al buscar los miembros en la lista. Es fácil tener una conversación con todos cuando está buscando un nombre. Hay que introducir el evangelio en estas conversaciones.</li>
                                                    <li>Actualiza la información del miembro cada semana en elrescate.cl. Si hay muchos cambios puede imprimir una nueva lista del sector.</li>
                                                    <li>Trabaja con los secretarios y los lideres para asegurar que MLS se actualiza con las nuevas direcciones y datos de contacto.</li>
                                                    <li>Si un miembro ha cambiado su domicilio fuera de su barrio es importante que obtenga la nueva dirección. No se puede enviar un registro de un miembro afuera hasta que tenga la nueva dirección.</li>
                                                    <li>Trabaja con los secretarios y los lideres para asegurar que los miembros están ubicado en el mapa del barrio en lds.org.</li>
                                                    <li>La meta es de encontrar todos los miembros, rescatar los menos activos, encontrar muchos nuevos investigadores, actualizar MLS con las direcciones correctas y ubicar todos los miembros en el mapa de su barrio en lds.org.</li>
                                                    </ol>";
                                    $nom = $_POST["usuario_nom"].' '.$_POST["usuario_ape"];
                                    $txt = "<h2>Bienvenido(a) $nom, su clave de acceso es:</h2>
                                            <span style='font-weight: bold; padding: 10px 20px; border: 1px solid #ccc; font-size: 18px;'>".$_POST["usuario_pass"]."</span>
                                            <div style='margin-top: 30px; text-align:left;'>$bienvenida</div>";
                                    $this->cp->enviarEmail($nom, $_POST["usuario_user"], "Bienvenido a El Rescate", $txt);
                                    $res->ERROR = 0;
                                    $res->MENSAJE = "La cuenta fue creada correctamente, revise su email";
                                } else {
                                    $res->ERROR = 1;
                                    $res->MENSAJE = "La cuenta no pudo ser creada, por favor intente nuevamente";
                                }
                            } else {
                                $res->ERROR = 1;
                                $res->MENSAJE = "Ya existe un usuario con ese email";
                            }
                        } else {
                            $res->ERROR = 1;
                            $res->MENSAJE = "Email no valido";
                        }
                    } else {
                        $res->ERROR = 1;
                        $res->MENSAJE = "Email no valido";
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
    
    function setGet() {
        if(isset($_GET["get"])) {
            $val = $_GET["get"];
            switch($val) {
                case 'user':
                    $res = new stdClass();
                    if($this->cp->getSession()->existe("usuario_id")) {
                        $res->usuario_id = $this->cp->getSession()->get("usuario_id");
                        $res->usuario_user = $this->cp->getSession()->get("usuario_user");
                        $res->usuario_nom = $this->cp->getSession()->get("usuario_nom");
                        $res->usuario_ape = $this->cp->getSession()->get("usuario_ape");
                    }
                    break;
                case 'acceso':
                    if(!$this->cp->getSession()->existe("acceso")) {
                        include_once 'modelo/DireccionMP.php';
                        $this->dirMP = new DireccionMP();

                        $acc = $this->usMP->fetchAcceso($this->cp->getSession()->get("usuario_id"));
                        
                        $nAcc = count($acc);
                        if($nAcc > 0) {
                            $res = new stdClass();
                            $res->mision = array();
                            $res->estaca = array();
                            $res->barrio = array();

                            for($i=0; $i<$nAcc; $i++) {
                                switch($acc[$i]->usuario_acceso_nivel) {
                                    case 0:
                                        $res->mision = $this->dirMP->fetchUnidades(3);
                                        $res->estaca = $this->dirMP->fetchUnidades(2);
                                        $res->barrio = $this->dirMP->fetchUnidades(1);
                                        break;
                                    case 1:
                                        $aux = $this->dirMP->findUnidad($acc[$i]->barrio_id, $acc[$i]->usuario_acceso_nivel);
                                        $res->barrio[] = $aux;
                                        break;
                                    case 2:
                                        $aux = $this->dirMP->findUnidad($acc[$i]->estaca_id, $acc[$i]->usuario_acceso_nivel);
                                        $res->estaca[] = $aux;
                                        $auxUn = $this->dirMP->fetchByUnidad($acc[$i]->estaca_id, $acc[$i]->usuario_acceso_nivel, 0);
                                        $res->barrio = array_merge($res->barrio, $auxUn);
                                        break;
                                    case 3:
                                        $aux = $this->dirMP->findUnidad($acc[$i]->mision_id, $acc[$i]->usuario_acceso_nivel);
                                        $res->mision[] = $aux;
                                        $auxUn = $this->dirMP->fetchByUnidad($acc[$i]->mision_id, $acc[$i]->usuario_acceso_nivel, 1);
                                        $res->estaca = array_merge($res->estaca, $auxUn);
                                        $auxUn = $this->dirMP->fetchByUnidad($acc[$i]->mision_id, $acc[$i]->usuario_acceso_nivel, 0);
                                        $res->barrio = array_merge($res->barrio, $auxUn);
                                        break;
                                }
                            }
                        } else {
                            $res = false;
                        }
                        $this->cp->getSession()->set("acceso", $res);
                    } else {
                        $res = $this->cp->getSession()->get("acceso");
                    }
                    break;
                case 'misiones':
                    include_once 'modelo/DireccionMP.php';
                    $this->dirMP = new DireccionMP();
                    $res = $this->dirMP->fetchMisiones();
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
