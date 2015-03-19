<?php
/**
 * Created by PhpStorm.
 * User: 宇
 * Date: 14-11-13
 * Time: 下午4:17
 */
?>
<?php

require_once(dirname(__FILE__) . "/../tools/mysql_tool.php");
require_once(dirname(__FILE__) . "/../tools/php_tool.php");

$oj = null;
if (isset($_GET["oj"]))
    $oj = $_GET["oj"];
$id = null;
if (isset($_GET["id"]))
    $id = $_GET["id"];
if (!checkNumber($oj, $id)) {
    echo json_encode(array('result' => 'none'));
    exit;
}
$result = mysql_getSearchProblem($oj, $id);
if ($result == null) {
    echo json_encode(array('result' => 'none'));
    exit;
}
echo json_encode(array('result' => 'ok', 'id' => $result["id"], 'title' => $result["title"]));
?>