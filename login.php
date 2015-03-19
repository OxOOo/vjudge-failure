<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 14-10-28
 * Time: 下午3:35
 */
session_start();

require_once(dirname(__FILE__) . "/tools/php_tool.php");
require_once(dirname(__FILE__) . "/tools/mysql_tool.php");

if (isset($_GET["post"]) && $_GET["post"] == "login") {
    $username = "";
    if (isset($_POST["username"]))
        $username = $_POST["username"];
    $password = "";
    if (isset($_POST["password"]))
        $password = $_POST["password"];
    if (!checkUsername($username))
        die("用户名非法");
    if (!checkPassword($password))
        die("密码非法");
    $mysql_password = mysql_getPassword($username);
    if ($mysql_password == null)
        die("该用户不存在");
    if ($mysql_password != $password)
        die("用户名或密码错误");
    mysql_lastLogin($username);
    $_SESSION["user"] = mysql_getUserInfo($username);
    ?>
    <script>
        history.go(-2);
    </script>
    <?php
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8"/>
    <link rel="shortcut icon" href="./img/logo.png" >
    <title>虚拟在线评测系统</title>
    <link rel="stylesheet" href="./main.css" type="text/css"/>
</head>
<body>
<div class="container">
    <?php include "./header.php"; ?>
    <div class="box">
        <h2 style="text-align: center">登录</h2>

        <div class="form">
            <form action="login.php?post=login" method="post" style="float: inherit" onsubmit="return checkForm()">
                <table>
                    <tbody>
                    <tr>
                        <th style="width: 10em;text-align: right">用户名&nbsp;&nbsp;&nbsp;&nbsp;</th>
                        <td><input type="text" name="username" id="username" placeholder="输入用户名"/></td>
                        <td id="usernameError" class="error">&nbsp;</td>
                    </tr>
                    <tr>
                        <th style="text-align: right">密码&nbsp;&nbsp;&nbsp;&nbsp;</th>
                        <td><input type="password" name="password" id="password" placeholder="输入密码"/></td>
                        <td id="passwordError" class="error">&nbsp;</td>
                    </tr>
                    <tr>
                        <th>&nbsp;</th>
                        <td><input type="submit" value="  提交  "/></td>
                    </tr>
                    </tbody>
                </table>
                <script src="./js/md5.js" type="text/javascript" charset="utf-8"></script>
                <script src="./js/tools.js" type="text/javascript" charset="utf-8"></script>
                <script>
                    function checkForm() {
                        document.getElementById('usernameError').innerHTML = '&nbsp;';
                        document.getElementById('passwordError').innerHTML = '&nbsp;';

                        var error = null;
                        var hasError = false;

                        error = checkUsername(document.getElementById('username').value);
                        if (error != null) {
                            hasError = true;
                            document.getElementById('usernameError').innerHTML = error;
                        }

                        error = checkPassword(document.getElementById('password').value);
                        if (error != null) {
                            hasError = true;
                            document.getElementById('passwordError').innerHTML = error;
                        }

                        if (hasError == false) {
                            document.getElementById('password').value = md5(document.getElementById('password').value);
                            return true;
                        }

                        return false;
                    }
                </script>
            </form>
        </div>
    </div>
    <?php include "./footer.php"; ?>
</div>
</body>
</html>