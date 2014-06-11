<?php

/**
 * Description of CPrincipal
 *
 * @author Alvaro Flores
 */

include_once 'util/session.php';
require_once 'util/Mindrill.class.php';


class CPrincipal {
    public $showLayout = true;
    public $thisLayout = true;
    public $isLoged = false;
    protected $ss;

    function __construct() {
        setlocale(LC_CTYPE, 'es_CL.utf8');
        $this->ss = new session();
        $this->checkLogin();
        $this->layout = "index_js.html";
        if($this->isLoged) {
            $this->setSec();
        } else {
            include_once 'controlador/CLog.php';
            $this->_CSec = new CLog($this);
        }
    }

    function getSession() {
        return $this->ss;
    }

    function checkLogin() {
        if($this->ss->existe("usuario_id")) {
            $this->isLoged = true;
        } else {
            $this->isLoged = false;
        }
    }

    function getCSec() {
        return $this->_CSec;
    }
    
    function setSec() {
        $this->sec = (isset($_GET["sec"])) ? $_GET["sec"] : "";
        switch ($this->sec) {
            case 'buscador':
                include_once 'CBuscador.php';
                $this->_CSec = new CBuscador($this);
                break;
            case 'miembros':
                include_once 'CMiembros.php';
                $this->_CSec = new CMiembros($this);
                break;
            case 'direccion':
                include_once 'CDireccion.php';
                $this->_CSec = new CDireccion($this);
                break;
            case 'reportes':
                include_once 'CReportes.php';
                $this->_CSec = new CReportes($this);
                break;
            case 'comentarios':
                include_once 'CComentarios.php';
                $this->_CSec = new CComentarios($this);
                break;
            case 'dashboard':
                include_once 'CDashboard.php';
                $this->_CSec = new CDashboard($this);
                break;
            case 'log':
                include_once 'CLog.php';
                $this->_CSec = new CLog($this);
                break;
            default:
                // die();
                break;
        }
    }

    function curl($url, array $post = NULL, array $options = array()) { 
        $defaults = array( 
            CURLOPT_POST => 1, 
            CURLOPT_HEADER => 0, 
            CURLOPT_URL => $url, 
            CURLOPT_FRESH_CONNECT => 1, 
            CURLOPT_RETURNTRANSFER => 1, 
            CURLOPT_FORBID_REUSE => 1, 
            CURLOPT_TIMEOUT => 1000, 
            CURLOPT_POSTFIELDS => $post
        ); 

        $ch = curl_init(); 
        curl_setopt_array($ch, ($options + $defaults)); 
        if( ! $result = curl_exec($ch)) 
        { 
            trigger_error(curl_error($ch)); 
        } 
        curl_close($ch); 
        return $result; 
    } 

    function secureInput($val, $strip = 1, $xss = 1, $charset = 'UTF-8') {
        if (is_array($val)) {
            $output = array();
            foreach ($val as $key => $data) {
                $output[$key] = secure_input($data, $strip, $charset);
            }
            return $output;
        } else {
            if ($xss > 0) {
                // code by nicolaspar
                $val = preg_replace('/([\x00-\x08][\x0b-\x0c][\x0e-\x20])/', '', $val);
                $search = 'abcdefghijklmnopqrstuvwxyz';
                $search .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
                $search .= '1234567890!@#$%^&*()';
                $search .= '~`";:?+/={}[]-_|\'\\';

                for ($i = 0; $i < strlen($search); $i++) {
                    $val = preg_replace('/(&#[x|X]0{0,8}' . dechex(ord($search[$i])) . ';?)/i', $search[$i], $val); // with a ;
                    $val = preg_replace('/(&#0{0,8}' . ord($search[$i]) . ';?)/', $search[$i], $val); // with a ;
                }

                $ra1 = Array('javascript', 'vbscript', 'expression', 'applet', 'meta', 'xml', 'blink', 'link', 'style', 'script', 'embed', 'object', 'iframe', 'frame', 'frameset', 'ilayer', 'layer', 'bgsound', 'title', 'base');
                $ra2 = Array('onabort', 'onactivate', 'onafterprint', 'onafterupdate', 'onbeforeactivate', 'onbeforecopy', 'onbeforecut', 'onbeforedeactivate', 'onbeforeeditfocus', 'onbeforepaste', 'onbeforeprint', 'onbeforeunload', 'onbeforeupdate', 'onblur', 'onbounce', 'oncellchange', 'onchange', 'onclick', 'oncontextmenu', 'oncontrolselect', 'oncopy', 'oncut', 'ondataavailable', 'ondatasetchanged', 'ondatasetcomplete', 'ondblclick', 'ondeactivate', 'ondrag', 'ondragend', 'ondragenter', 'ondragleave', 'ondragover', 'ondragstart', 'ondrop', 'onerror', 'onerrorupdate', 'onfilterchange', 'onfinish', 'onfocus', 'onfocusin', 'onfocusout', 'onhelp', 'onkeydown', 'onkeypress', 'onkeyup', 'onlayoutcomplete', 'onload', 'onlosecapture', 'onmousedown', 'onmouseenter', 'onmouseleave', 'onmousemove', 'onmouseout', 'onmouseover', 'onmouseup', 'onmousewheel', 'onmove', 'onmoveend', 'onmovestart', 'onpaste', 'onpropertychange', 'onreadystatechange', 'onreset', 'onresize', 'onresizeend', 'onresizestart', 'onrowenter', 'onrowexit', 'onrowsdelete', 'onrowsinserted', 'onscroll', 'onselect', 'onselectionchange', 'onselectstart', 'onstart', 'onstop', 'onsubmit', 'onunload');

                $ra = array_merge($ra1, $ra2);
                $found = true;

                while ($found == true) {
                    $val_before = $val;
                    for ($i = 0; $i < sizeof($ra); $i++) {
                        $pattern = '/';
                        for ($j = 0; $j < strlen($ra[$i]); $j++) {
                            if ($j > 0) {
                                $pattern .= '(';
                                $pattern .= '(&#[x|X]0{0,8}([9][a][b]);?)?';
                                $pattern .= '|(&#0{0,8}([9][10][13]);?)?';
                                $pattern .= ')?';
                            }
                            $pattern .= $ra[$i][$j];
                        }
                        $pattern .= '/i';
                        $replacement = substr($ra[$i], 0, 2) . '<x>' . substr($ra[$i], 2);
                        $val = preg_replace($pattern, $replacement, $val);
                        if ($val_before == $val) {
                            $found = false;
                        }
                    }
                }
            }

            // Strip HTML tags
            if ($strip > 0) {
                $val = strip_tags($val);
            }

            // Encode special chars
            $val = htmlentities($val, ENT_QUOTES, $charset);
            
            if (get_magic_quotes_gpc()) {
                return mysql_escape_string(stripslashes($val));
            } else {
                return mysql_escape_string($val);
            }
        }
    }

    function enviarEmail($nom, $email, $titulo, $txt, $tags=null) {
        $nomFrom = "El Rescate";
        $emaFrom = "no-reply@elrescate.cl";

        $body = "<!DOCTYPE html>
                <html>
                    <head>
                        <meta http-equiv='Content-Type' content='text/html; charset=UTF-8'>
                    </head>
                    <body style='background-color: #2a3753; text-align: center'>
                        <h1 style='margin: 0;text-aling: center; display:block;padding: 20px 10px 10px 10px;font-weight: normal;color:white;'><a href='http://elrescate.cl' style='color:white;text-decoration: none;'>El Rescate</a></h1>
                        <div style='width:80%; padding: 8px; background-color: white;margin: 0 auto;border-radius: 4px;'>
                            <div style='margin: 0;background: #f9f6ed;padding: 10px;border-radius: 4px;'>
                                $txt
                            </div>
                        </div>
                    </body>
                </html>";
        
        $mandrill = new Mindrill('QaK2xYWw9GP5Zd6M8bKI4A');
        $msg = new stdClass();
        $msg->message = new stdClass();
        $msg->message->subject = $titulo." - ".$nomFrom;
        $msg->message->from_email = $emaFrom;
        $msg->message->from_name = $nomFrom;
        $msg->message->to = array();
        $to = new stdClass();
        $to->email = $email;
        $to->name = $nom;
        $msg->message->to[] = $to;
        $msg->message->async = true;
        $msg->message->track_opens = true;
        $msg->message->track_clicks = true;
        if(!is_null($tags)) $msg->message->tags = $tags;

        $msg->message->html = $body;
        $res = $mandrill->call("/messages/send", $msg);

        return ($res[0]->status == "sent");
    }

}

?>