<?php
class cookie {
    function get($n) {
        return $_COOKIE["$n"];
    }

    function set($n, $v, $t) {
        setcookie($n, $v, $t);
    }

    function delete($n) {
        setcookie($n, false, time()-360);
    }

    function existe($n) {
        if(isset($_COOKIE[$n]) and $_COOKIE[$n]!= "") {
            return true;
        } else {
            return false;
        }
    }
}
?>
