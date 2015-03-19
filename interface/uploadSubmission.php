<?php
/**
 * Created by PhpStorm.
 * User: 宇
 * Date: 14-11-17
 * Time: 上午8:12
 */

require_once(dirname(__FILE__) . "/config.php");
require_once(dirname(__FILE__) . "/../tools/mysql_tool.php");

if (!isset($_GET["token"]) || !isset($_GET["key"]) || !checkToken($_GET["key"], $_GET["token"]))
    die("token不符合");
$submission = json_decode($_POST["submission"],true);
if (!checkSubmission($submission))
    die("Submission非法");
mysql_updateSubmission($submission);
?>