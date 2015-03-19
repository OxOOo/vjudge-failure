<?php
/**
 * Created by PhpStorm.
 * User: 宇
 * Date: 14-12-3
 * Time: 上午10:37
 */
require_once(dirname(__FILE__) . "/config.php");
require_once(dirname(__FILE__) . "/../tools/mysql_tool.php");

if (!isset($_GET["token"]) || !isset($_GET["key"]) || !checkToken($_GET["key"], $_GET["token"]))
    die("token不符合");
$test = json_decode($_POST["test"],true);
if (!checkTest($test))
    die("Task非法");
mysql_updateTest($test);
?>