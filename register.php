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

if (isset($_GET["post"]) && $_GET["post"] == "register") {
    if (isset($_POST["username"]))
        $username = $_POST["username"];
    else $username = "";
    if (isset($_POST["password"]))
        $password = $_POST["password"];
    else $password = "";
    if (isset($_POST["realname"]))
        $realname = $_POST["realname"];
    else $realname = "";
    if (isset($_POST["nickname"]))
        $nickname = $_POST["nickname"];
    else $nickname = "";
    if (!checkUsername($username))
        die("用户名非法");
    if (!checkPassword($password))
        die("密码非法");
    if (!checkRealname($realname))
        die("真实姓名非法");
    if (!checkNickname($nickname))
        die("昵称非法");
    if (mysql_getPassword($username) != null)
        die("用户名已存在");

    mysql_registerUser($username, $password, $realname, $nickname);
    mysql_lastLogin($username);
    $_SESSION["user"] = mysql_getUserInfo($username);

    header("location: ./");
    exit();
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
        <h2 style="text-align: center">注册</h2>

        <div class="form">
            <form action="./register.php?post=register" method="post" style="float: inherit"
                  onsubmit="return checkForm()">
                <table>
                    <tbody>
                    <tr>
                        <th style="width: 10em;text-align: right">用户名&nbsp;&nbsp;&nbsp;&nbsp;</th>
                        <td><input type="text" name="username" id="username" placeholder="输入用户名"
                                   onblur="checkUsernameOnline()"/></td>
                        <td id="usernameError" class="error">&nbsp;</td>
                    </tr>
                    <tr>
                        <th style="text-align: right">密码&nbsp;&nbsp;&nbsp;&nbsp;</th>
                        <td><input type="password" name="password" id="password" placeholder="输入密码"/></td>
                        <td id="passwordError" class="error">&nbsp;</td>
                    </tr>
                    <tr>
                        <th style="text-align: right">&nbsp;</th>
                        <td><input type="password" id="repeat" placeholder="再次输入密码"/></td>
                        <td id="repeatError" class="error">&nbsp;</td>
                    </tr>
                    <tr>
                        <td colspan="3">&nbsp;</td>
                    </tr>
                    <tr>
                        <th style="text-align: right">真实姓名&nbsp;&nbsp;&nbsp;&nbsp;</th>
                        <td><input type="text" name="realname" id="realname" placeholder="输入真实姓名"/></td>
                        <td id="realnameError" class="error">&nbsp;</td>
                    </tr>
                    <tr>
                        <th style="text-align: right">昵称&nbsp;&nbsp;&nbsp;&nbsp;</th>
                        <td><input type="text" name="nickname" id="nickname" placeholder="输入昵称"/></td>
                        <td id="nicknameError" class="error">&nbsp;</td>
                    </tr>
                    <tr>
                        <th>&nbsp;</th>
                        <td><input type="submit" value="  提交  "/></td>
                    </tr>
                    </tbody>
                </table>
                <script src="./js/tools.js" type="text/javascript" charset="utf-8"></script>
                <script src="./js/md5.js" type="text/javascript" charset="utf-8"></script>
                <script>
                    function checkUsernameOnline() {
                        var username = document.getElementById('username');
                        var hint = document.getElementById('usernameError');
                        hint.className = 'error';
                        var error = checkUsername(username.value);
                        if (error != null) {
                            hint.innerHTML = error;
                            return;
                        }

                        var xmlHttpRequest = null;
                        if (window.ActiveXObject) { //如果是IE浏览器
                            xmlHttpRequest = new ActiveXObject("Microsoft.XMLHTTP");
                        } else if (window.XMLHttpRequest) { //非IE浏览器
                            xmlHttpRequest = new XMLHttpRequest();
                        }
                        if (xmlHttpRequest == null)return;

                        xmlHttpRequest.onreadystatechange = function () {
                            if (xmlHttpRequest.readyState == 4 && xmlHttpRequest.status == 200) {
                                var html = xmlHttpRequest.responseText;
                                var json = JSON.parse(html);
                                if (json['result'] == 'ok') {
                                    if (json['visible'] == true) {
                                        hint.className = 'accepted';
                                        hint.innerHTML = '该用户名可用';
                                    } else {
                                        hint.innerHTML = '该用户名不可用，请换一个试试';
                                    }
                                } else {
                                    hint.innerHTML = '查询用户名出现错误';
                                }
                                return;
                            }
                            hint.innerHTML = '正在查询中，请稍等...';
                        };
                        xmlHttpRequest.open('GET', './interface/searchUsernameVisible.php?username=' + username.value, true);
                        xmlHttpRequest.send(null);
                    }

                    function checkForm() {
                        document.getElementById('usernameError').innerHTML = '&nbsp;';
                        document.getElementById('usernameError').className = 'error';
                        document.getElementById('passwordError').innerHTML = '&nbsp;';
                        document.getElementById('repeatError').innerHTML = '&nbsp;';
                        document.getElementById('realnameError').innerHTML = '&nbsp;';
                        document.getElementById('nicknameError').innerHTML = '&nbsp;';

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

                        if (document.getElementById('password').value != document.getElementById('repeat').value)
                            error = '两次输入的密码不一致';
                        else error = null;
                        if (error != null) {
                            hasError = true;
                            document.getElementById('repeatError').innerHTML = error;
                        }

                        error = checkRealname(document.getElementById('realname').value);
                        if (error != null) {
                            hasError = true;
                            document.getElementById('realnameError').innerHTML = error;
                        }

                        error = checkNickname(document.getElementById('nickname').value);
                        if (error != null) {
                            hasError = true;
                            document.getElementById('nicknameError').innerHTML = error;
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