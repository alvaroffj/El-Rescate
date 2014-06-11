<?php
class session {
    function __construct() {
        session_start();
    }

    function get($n) {
        return $_SESSION["$n"];
    }

    function set($n, $v) {
        $_SESSION["$n"] = $v;
    }

    function delete($n) {
        unset($_SESSION["$n"]);
    }

    function kill() {
        session_destroy();
    }

    function existe($n) {
        if(isset($_SESSION[$n]) and $_SESSION[$n]!= "") {
            return true;
        } else {
            return false;
        }
    }

    function salto($n) {
        header("Location:$n");
        die();
    }
}
?>
