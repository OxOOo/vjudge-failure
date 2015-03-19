<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 14-10-29
 * Time: 下午2:46
 */
session_start();
if (isset($_SESSION["user"]))
    unset($_SESSION["user"]);
if (isset($_SESSION["admin"]))
	unset($_SESSION["admin"]);
?>
<script>
    history.go(-1);
</script>