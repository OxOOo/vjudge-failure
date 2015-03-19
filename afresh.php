<?php
/**
 * Created by PhpStorm.
 * User: 宇
 * Date: 14-11-23
 * Time: 下午5:49
 */
require_once(dirname(__FILE__) . "/tools/php_tool.php");
require_once(dirname(__FILE__) . "/tools/mysql_tool.php");

if(isset($_GET["action"]) && $_GET["action"]=="afresh" && isset($_GET["id"]) && checkID($_GET["id"]))
{
    mysql_afreshSubmission($_GET["id"]);
    ?>
    <script>
        history.back(-1);
    </script>
    <?php
    exit;
}
die("参数不合法");
?>