<?php
/**
 * Created by PhpStorm.
 * User: 宇
 * Date: 14-11-24
 * Time: 下午8:01
 */
require_once(dirname(__FILE__) . "/config.php");

if (!isset($_GET["token"]) || !isset($_GET["key"]) || !checkToken($_GET["key"], $_GET["token"]))
    die("token不符合");

$upload_path = "../upload";

if (!file_exists($upload_path))
    mkdir($upload_path);
chmod($upload_path, 0755);

if (!isset($_FILES["file"]))
    die("没有文件");
if ($_FILES["file"]["error"] != 0)
    die("错误" . $_FILES["file"]["error"]);

srand(time());
do {
    $fileName = $upload_path . "/" . time() . rand(100, 999) . "." . pathinfo($_FILES["file"]["name"], PATHINFO_EXTENSION);
} while (file_exists($fileName));
move_uploaded_file($_FILES["file"]["tmp_name"], $fileName);
echo "Path:" . substr($fileName, 1);
?>