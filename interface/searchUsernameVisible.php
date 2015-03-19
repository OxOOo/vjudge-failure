<?php
/**
 * Created by PhpStorm.
 * User: 宇
 * Date: 14-11-13
 * Time: 下午7:15
 */
require_once(dirname(__FILE__) . "/../tools/mysql_tool.php");
require_once(dirname(__FILE__) . "/../tools/php_tool.php");

$username = "";
if (isset($_GET["username"]))
    $username = $_GET["username"];
if (!checkUsername($username)) {
    echo json_encode(array("result" => "ok", "visible" => false));
    exit;
}
if (mysql_getPassword($username) != null) {
    echo json_encode(array("result" => "ok", "visible" => false));
    exit;
}
echo json_encode(array("result" => "ok", "visible" => true));
?>