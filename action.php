<?php
/**
 * Created by PhpStorm.
 * User: 宇
 * Date: 14-11-23
 * Time: 下午5:49
 */
session_start();

require_once(dirname(__FILE__) . "/tools/php_tool.php");
require_once(dirname(__FILE__) . "/tools/mysql_tool.php");

function getIP()
{
    if (getenv('HTTP_CLIENT_IP')) {
        $ip = getenv('HTTP_CLIENT_IP');
    } elseif (getenv('HTTP_X_FORWARDED_FOR')) {
        $ip = getenv('HTTP_X_FORWARDED_FOR');
    } elseif (getenv('HTTP_X_FORWARDED')) {
        $ip = getenv('HTTP_X_FORWARDED');
    } elseif (getenv('HTTP_FORWARDED_FOR')) {
        $ip = getenv('HTTP_FORWARDED_FOR');

    } elseif (getenv('HTTP_FORWARDED')) {
        $ip = getenv('HTTP_FORWARDED');
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return $ip;
}

if (isset($_GET["action"]) && $_GET["action"] == "afresh" && isset($_GET["id"]) && checkID($_GET["id"])) {
    mysql_afreshSubmission($_GET["id"]);
}
if (isset($_GET["action"]) && $_GET["action"] == "admin" && isset($_SESSION["user"]) && $_SESSION["user"]["isAdmin"] == 1) {
    if (isset($_SESSION["admin"]))
        $_SESSION["admin"] = 1 - $_SESSION["admin"];
    else $_SESSION["admin"] = 1;
}
if (isset($_GET["action"]) && $_GET["action"] == "test") {
    $ip = getIP();
    if (!mysql_testFinish($ip))
        die("暂时不能提交，还有未完成的评测");
    $language = $_POST["language"];
    $code = $_POST["code"];
    $input = $_POST["input"];
    echo mysql_addTest($ip, $language, $code, $input);
    exit;
}
if (isset($_GET["action"]) && $_GET["action"] == "getTest" && isset($_GET["id"]) && checkID($_GET["id"])) {
    $id = $_GET["id"];
    echo json_encode(mysql_getSearchTest($id));
    exit;
}
?>
<script>
    history.back(-1);
</script>