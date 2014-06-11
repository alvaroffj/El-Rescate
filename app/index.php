<?php
include_once 'controlador/CPrincipal.php';

$cp = new CPrincipal();
?>
<? include $cp->layout; ?>
<script type="text/javascript">
    app.constant('User', <?=json_encode($_SESSION)?>);
</script>