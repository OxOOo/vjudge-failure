<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 14-10-29
 * Time: 下午2:46
 */
session_start();

require_once(dirname(__FILE__) . "/tools/mysql_tool.php");
require_once(dirname(__FILE__) . "/tools/php_tool.php");

if (isset($_GET["post"]) && $_GET["post"] == "modify") {
    if (!isset($_SESSION["user"])) {
        header("location: ./login.php");
        exit;
    }

    $oldPassword = null;
    if (isset($_POST["oldPassword"]))
        $oldPassword = $_POST["oldPassword"];
    $newPassword = null;
    if (isset($_POST["newPassword"]))
        $newPassword = $_POST["newPassword"];
    $realname = "";
    if (isset($_POST["realname"]))
        $realname = $_POST["realname"];
    $nickname = "";
    if (isset($_POST["nickname"]))
        $nickname = $_POST["nickname"];
    if (!checkPassword($oldPassword))
        die("原始密码非法");
    if ($newPassword != null && $newPassword != "" && !checkPassword($newPassword))
        die("新密码非法");
    if (!checkRealname($realname))
        die("真实姓名非法");
    if (!checkNickname($nickname))
        die("昵称非法");

    if ($_SESSION["user"]["password"] != $oldPassword)
        die("原始密码错误");
    mysql_modifyUser($_SESSION["user"]["id"], $newPassword, $realname, $nickname);
    $_SESSION["user"]= mysql_getUserInfo($_SESSION["user"]["username"]);
    header("location: ./user.php");
    exit;
}

if (isset($_GET["post"]) && $_GET["post"] == "addContest") {
    if (!isset($_SESSION["user"])) {
        header("location: ./login.php");
        exit;
    }
    if ($_SESSION["user"]["isAdmin"] == 0)
        die("你没有权限");
    $password = "";
    if (isset($_POST["password"]))
        $password = $_POST["password"];
    $contestInfo = array();
    if (isset($_POST["contestInfo"]))
        $contestInfo = json_decode($_POST["contestInfo"],true);
    if (!checkPassword($password))
        die("密码非法");
    if (!checkContestInfo($contestInfo))
        die("比赛信息非法");
    if ($password != $_SESSION["user"]["password"])
        die("密码不正确");
    mysql_addContest($_SESSION["user"]["id"], $contestInfo);
    header("location: ./contests.php");
    exit;
}

if (isset($_GET["post"]) && $_GET["post"] == "addProblem") {
    if (!isset($_SESSION["user"])) {
        header("location: ./login.php");
        exit;
    }
    if ($_SESSION["user"]["isAdmin"] == 0)
        die("你没有权限");
    $password = "";
    if (isset($_POST["password"]))
        $password = $_POST["password"];
    $oj = "";
    if (isset($_POST["oj"]))
        $oj = $_POST["oj"];
    $number = "";
    if (isset($_POST["number"]))
        $number = $_POST["number"];
    if (!checkPassword($password))
        die("密码非法");
    if (!checkNumber($oj, $number))
        die("题库或编号非法");
    if ($password != $_SESSION["user"]["password"])
        die("密码错误");
    mysql_addTask($_SESSION["user"]["id"], $oj, $number);
    header("location: ./user.php?tab=addProblem");
    exit;
}

$tab = "home";
if (isset($_GET["tab"])) {
    if ($_GET["tab"] == "modify")
        $tab = "modify";
    else if ($_GET["tab"] == "addContest" && isset($_SESSION["user"]) && $_SESSION["user"]["isAdmin"] == 1)
        $tab = "addContest";
    else if ($_GET["tab"] == "addProblem" && isset($_SESSION["user"]) && $_SESSION["user"]["isAdmin"] == 1)
        $tab = "addProblem";
}
if ($tab == "home") {
    $username = "";
    if (isset($_GET["username"]))
        $username = $_GET["username"];
    else if (isset($_SESSION["user"]))
        $username = $_SESSION["user"]["username"];
    if (!checkUsername($username))
        die("用户名非法");
    $Info = mysql_getUserInfo($username);
}
if ($tab == "modify") {
    if (isset($_SESSION["user"]))
        $username = $_SESSION["user"]["username"];
    else {
        header("location: ./login.php");
        exit;
    }
    $Info = mysql_getUserInfo($username);
}
if ($tab == "addContest") {

}
if ($tab == "addProblem") {

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
<?php include "header.php"; ?>
<div class="box">
<?php
if (!isset($_GET["username"])) {
    ?>
    <div class="tab">
        <ul style="position: fixed">
            <li id="home" style="float: none"><a href="./user.php">基本资料</a></li>
            <li id="modify" style="float: none"><a href="./user.php?tab=modify">修改资料</a></li>
            <?php
            if ($_SESSION["user"]["isAdmin"] == 1) {
                ?>
                <li id="addContest" style="float: none"><a href="./user.php?tab=addContest">添加比赛</a></li>
                <li id="addProblem" style="float: none"><a href="./user.php?tab=addProblem">添加题目</a></li>
            <?php
            }
            ?>
            <script>
                (function () {
                    document.getElementById('<?php echo $tab; ?>').className = 'active';
                })();
            </script>
        </ul>
    </div>
<?php
}
?>
<?php
if ($tab == "home") {
    ?>
    <h2 style="text-align: center">基本资料</h2>

    <div class="table" style="margin: 20px 30%">
        <table>
            <tbody>
            <tr>
                <th colspan="2">
                    <?php echo $Info["username"]; ?>
                </th>
            </tr>
            <tr>
                <th>
                    真实姓名
                </th>
                <td>
                    <?php echo htmlspecialchars($Info["realname"]); ?>
                </td>
            </tr>
            <tr>
                <th>
                    昵称
                </th>
                <td>
                    <?php echo htmlspecialchars($Info["nickname"]); ?>
                </td>
            </tr>
            <tr>
                <th>
                    注册时间
                </th>
                <td>
                    <?php echo $Info["registerDatetime"]; ?>
                </td>
            </tr>
            <tr>
                <th>
                    最近登录
                </th>
                <td>
                    <?php echo $Info["lastLoginDatetime"]; ?>
                </td>
            </tr>
            <tr>
                <th>
                    通过次数
                </th>
                <td>
                    <?php echo $Info["acceptedCount"]; ?>
                </td>
            </tr>
            <tr>
                <th>
                    提交次数
                </th>
                <td>
                    <?php echo $Info["submitedCount"]; ?>
                </td>
            </tr>
            <tr>
                <th>
                    账号类型
                </th>
                <td>
                    <?php echo($Info["isAdmin"] == 1 ? "管理员" : "普通用户"); ?>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
<?php
} else
    if ($tab == "modify") {
        ?>
        <h2 style="text-align: center">修改资料</h2>

        <div class="form">
            <form action="./user.php?post=modify" method="post" style="float: inherit" onsubmit="return checkForm()">
                <table>
                    <tbody>
                    <tr>
                        <td colspan="3">请输入密码，验证你的身份。</td>
                    </tr>
                    <tr>
                        <th style="width: 10em;text-align: right">密码&nbsp;&nbsp;&nbsp;&nbsp;</th>
                        <td style="width: 10em"><input type="password" name="oldPassword" id="oldPassword"
                                                       placeholder="输入密码"/></td>
                        <td id="oldPasswordError" class="error">&nbsp;</td>
                    </tr>
                    <tr>
                        <td colspan="3">请输入你的新资料，密码若不修改请留空。</td>
                    </tr>
                    <tr>
                        <th style="text-align: right">新密码&nbsp;&nbsp;&nbsp;&nbsp;</th>
                        <td><input type="password" name="newPassword" id="newPassword" placeholder="输入新密码"/></td>
                        <td id="newPasswordError" class="error">&nbsp;</td>
                    </tr>
                    <tr>
                        <th style="text-align: right">&nbsp;</th>
                        <td><input type="password" id="repeatPassword" placeholder="再次输入密码"/></td>
                        <td id="repeatPasswordError" class="error">&nbsp;</td>
                    </tr>
                    <tr>
                        <td colspan="3">&nbsp;</td>
                    </tr>
                    <tr>
                        <th style="text-align: right">真实姓名&nbsp;&nbsp;&nbsp;&nbsp;</th>
                        <td><input type="text" name="realname" id="realname" placeholder="输入真实姓名"
                                   value="<?php echo htmlspecialchars($Info["realname"]); ?>"/></td>
                        <td id="realnameError" class="error">&nbsp;</td>
                    </tr>
                    <tr>
                        <th style="text-align: right">昵称&nbsp;&nbsp;&nbsp;&nbsp;</th>
                        <td><input type="text" name="nickname" id="nickname" placeholder="输入昵称"
                                   value="<?php echo htmlspecialchars($Info["nickname"]); ?>"/></td>
                        <td id="nicknameError" class="error">&nbsp;</td>
                    </tr>
                    <tr>
                        <th>&nbsp;</th>
                        <td><input type="submit" value="  提交  "/></td>
                        <th>&nbsp;</th>
                    </tr>
                    </tbody>
                </table>
                <script src="./js/tools.js" type="text/javascript" charset="utf-8"></script>
                <script src="./js/md5.js" type="text/javascript" charset="utf-8"></script>
                <script>
                    function checkForm() {
                        document.getElementById('oldPasswordError').innerHTML = '&nbsp;';
                        document.getElementById('newPasswordError').innerHTML = '&nbsp;';
                        document.getElementById('repeatPasswordError').innerHTML = '&nbsp;';
                        document.getElementById('realnameError').innerHTML = '&nbsp;';
                        document.getElementById('nicknameError').innerHTML = '&nbsp;';

                        var error = null;
                        var hasError = false;

                        error = checkPassword(document.getElementById('oldPassword').value);
                        if (error != null) {
                            hasError = true;
                            document.getElementById('oldPasswordError').innerHTML = error;
                        }

                        if (document.getElementById('newPassword').value != '')
                            error = checkPassword(document.getElementById('newPassword').value);
                        else error = null;
                        if (error != null) {
                            hasError = true;
                            document.getElementById('newPasswordError').innerHTML = error;
                        }

                        if (document.getElementById('newPassword').value != document.getElementById('repeatPassword').value)
                            error = '两次输入的密码不一致';
                        else error = null;
                        if (error != null) {
                            hasError = true;
                            document.getElementById('repeatPasswordError').innerHTML = error;
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

                        if (!hasError) {
                            document.getElementById('oldPassword').value = md5(document.getElementById('oldPassword').value);
                            if (document.getElementById('newPassword').value != "")
                                document.getElementById('newPassword').value = md5(document.getElementById('newPassword').value);
                            return true;
                        }
                        return false;
                    }
                </script>
            </form>
        </div>
    <?php
    } else if ($tab == "addContest") {
        ?>
        <h2 style="text-align: center">添加比赛</h2>

        <div class="form">
        <form action="./user.php?post=addContest" method="post" style="float: inherit"
              onsubmit="return checkForm()">
        <input type="hidden" name="contestInfo" id="contestInfo" value=""/>
        <table>
            <tbody>
            <tr>
                <td colspan="3">请输入密码，验证你的身份。</td>
            </tr>
            <tr>
                <th style="width: 10em;text-align: right">密码&nbsp;&nbsp;&nbsp;&nbsp;</th>
                <td><input type="password" name="password" id="password"
                           placeholder="输入密码"/></td>
                <td id="passwordError" class="error">&nbsp;</td>
            </tr>
            <tr>
                <td colspan="3">&nbsp;</td>
            </tr>
            <tr>
                <td colspan="3">请填写比赛详情。</td>
            </tr>
            <tr>
                <th style="text-align: right">比赛名称&nbsp;&nbsp;&nbsp;&nbsp;</th>
                <td><input type="text" id="title" placeholder="输入比赛名称"/></td>
                <td id="titleError" class="error">&nbsp;</td>
            </tr>
            <tr>
                <th style="text-align: right">比赛模式&nbsp;&nbsp;&nbsp;&nbsp;</th>
                <td><select id="mode">
                        <option value="OI">OI</option>
                        <option value="ACM">ACM</option>
                    </select></td>
            </tr>
            <tr>
                <th style="text-align: right">开始时间&nbsp;&nbsp;&nbsp;&nbsp;</th>
                <td><input id="date" type="date"/><input id="time" type="time"/></td>
                <td id="datetimeError" class="error">&nbsp;</td>
            </tr>
            <tr>
                <th style="text-align: right">比赛时长&nbsp;&nbsp;&nbsp;&nbsp;</th>
                <td><select id="durationHour">
                        <option value="0">0小时</option>
                        <option value="1">1小时</option>
                        <option value="2">2小时</option>
                        <option value="3">3小时</option>
                        <option value="4">4小时</option>
                        <option value="5">5小时</option>
                    </select><select id="durationMinutes">
                        <option value="0">0分钟</option>
                        <option value="10">10分钟</option>
                        <option value="30">30分钟</option>
                    </select></td>
                <td id="durationError" class="error">&nbsp;</td>
            </tr>
            <tr>
                <th colspan="2" style="text-align: center">题目&nbsp;
                    <button type="button" class="small" onclick="addProblem()">+</button>
                </th>
                <td id="problemError" class="error">&nbsp;</td>
            </tr>
            <tr>
                <td colspan="3">
                    <table id="problems">
                    </table>
                </td>
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
        var probList = new Array();
        var length = 0;

        function addProblem() {
            length++;
            var tr = document.createElement('tr');
            tr.id = 'p' + length;
            var td = document.createElement('td');
            td.colSpan = 3;
            var div = document.createElement('div');
            var button = document.createElement('button');
            button.type = 'button';
            button.className = 'small';
            button.setAttribute('onclick', 'minusProblem(' + length + ')');
            button.textContent = '-';
            var select = document.createElement('select');
            select.id = 'oj' + length;
            select.options.add(new Option('CF', 'CF'));
            select.options.add(new Option('清澄', 'THU'));
            select.options.add(new Option('POJ', 'POJ'));
            select.options.add(new Option('HDU', 'HDU'));
            select.options.add(new Option('BZOJ', 'BZOJ'));
            select.setAttribute('onchange', 'searchProblem(' + length + ')');
            var input = document.createElement('input');
            input.id = 'id' + length;
            input.type = 'text';
            input.setAttribute('style', 'width:5em');
            input.placeholder = '原题库ID';
            input.setAttribute('onpropertychange', 'searchProblem(' + length + ')');
            input.setAttribute('oninput', 'searchProblem(' + length + ')');
            var link = document.createElement('a');
            link.id = 'link' + length;
            link.href = './problems.php';
            link.target = '_blank';
            link.textContent = '试题列表';
            link.className = 'error';
            var hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.id = 'number' + length;

            tr.appendChild(td);
            td.appendChild(div);
            div.appendChild(button);
            div.appendChild(document.createTextNode('   '));
            div.appendChild(select);
            div.appendChild(document.createTextNode('   '));
            div.appendChild(input);
            div.appendChild(document.createTextNode('   '));
            div.appendChild(link);
            div.appendChild(hidden);
            document.getElementById('problems').appendChild(tr);

            probList.push(length);
        }

        function minusProblem(index) {
            document.getElementById('p' + index).parentNode.removeChild(document.getElementById('p' + index));
            for (x in probList) {
                if (probList[x] == index) {
                    probList.splice(x, 1);
                    break;
                }
            }
        }

        function searchProblem(index) {
            var oj = document.getElementById('oj' + index);
            var id = document.getElementById('id' + index);
            var link = document.getElementById('link' + index);
            var number = document.getElementById('number' + index);
            if (id.value == "") {
                link.href = './problems.php';
                link.textContent = '试题列表';
                link.className = 'error';
                number.value = '';
                return;
            }
            var error = checkNumber(oj.value, id.value);
            if (error != null) {
                link.className = 'error';
                link.href = '';
                link.textContent = error;
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
                        link.href = './problems.php?id=' + json['id'];
                        link.textContent = json['title'];
                        link.className = '';
                        number.value = json['id'];
                    } else {
                        link.href = './problems.php';
                        link.textContent = '未找到合法题目';
                        link.className = 'error';
                        number.value = '';
                    }
                    return;
                }
                link.href = './problems.php';
                link.textContent = '等待查询结果...';
                link.className = 'error';
                number.value = '';
            };
            xmlHttpRequest.open('GET', './interface/searchProblem.php?oj=' + oj.value + '&id=' + id.value, true);
            xmlHttpRequest.send(null);
        }

        function clearError() {
            document.getElementById('passwordError').innerHTML = '&nbsp;';
            document.getElementById('titleError').innerHTML = '&nbsp;';
            document.getElementById('datetimeError').innerHTML = '&nbsp;';
            document.getElementById('durationError').innerHTML = '&nbsp;';
            document.getElementById('problemError').innerHTML = '&nbsp;';
        }

        function checkForm() {
            clearError();

            var error = null;
            var hasError = false;

            error = checkPassword(document.getElementById('password').value);
            if (error != null) {
                hasError = true;
                document.getElementById('passwordError').innerHTML = error;
            }

            error = checkTitle(document.getElementById('title').value);
            if (error != null) {
                hasError = true;
                document.getElementById('titleError').innerHTML = error;
            }

            if (document.getElementById('date').value == '' || document.getElementById('time').value == '') {
                error = '请填写开始时间';
            } else {
                var datetime = new Date(document.getElementById('date').value + ' ' + document.getElementById('time').value);
                if (datetime <= new Date()) {
                    error = '不能使用已经结束的时间';
                } else error = null;
            }
            if (error != null) {
                hasError = true;
                document.getElementById('datetimeError').innerHTML = error;
            }

            if (document.getElementById('durationHour').value == 0 && document.getElementById('durationMinutes').value == 0)
                error = '比赛时长不能为0';
            else error = null;
            if (error != null) {
                hasError = true;
                document.getElementById('durationError').innerHTML = error;
            }

            if (probList.length == 0)
                error = '比赛题目不能为空';
            else if (probList.length > 20)
                error = '比赛题目数量不能超过20';
            else error = null;
            if (error != null) {
                hasError = true;
                document.getElementById('problemError').innerHTML = error;
            }

            if (hasError == false) {
                error = null;
                for (x in probList)
                    if (document.getElementById('number' + probList[x]).value == '')
                        error = "请填写完整的比赛试题列表"
                if (error != null) {
                    hasError = true;
                    document.getElementById('problemError').innerHTML = error;
                }
            }
            if (hasError == false) {
                error = null;
                for (x in probList)
                    for (y in probList)
                        if (x != y && document.getElementById('number' + probList[x]).value == document.getElementById('number' + probList[y]).value)
                            error = "一场比赛中不能有相同的比赛";
                if (error != null) {
                    hasError = true;
                    document.getElementById('problemError').innerHTML = error;
                }
            }

            if (hasError == false) {
                document.getElementById('password').value = md5(document.getElementById('password').value);
                var json = new Object();
                json.title = document.getElementById('title').value;
                json.mode = document.getElementById('mode').value;
                json.datetime = document.getElementById('date').value + ' ' + document.getElementById('time').value;
                json.duration = document.getElementById('durationHour').value * 60 * 60 + document.getElementById('durationMinutes').value * 60;
                var problems = new Array();
                for (x in probList)
                    problems.push(document.getElementById('number' + probList[x]).value);
                json.problems = problems;
                document.getElementById('contestInfo').value = JSON.stringify(json);
                return true;
            }

            return false;
        }
        </script>
        </form>
        </div>
    <?php
    } else if ($tab == "addProblem") {
        ?>
        <h2 style="text-align: center">添加题目</h2>

        <div class="form">
            <form action="user.php?post=addProblem" method="post" style="float: inherit" onsubmit="return checkForm()">
                <table>
                    <tbody>
                    <tr>
                        <td colspan="3">请输入密码，验证你的身份。</td>
                    </tr>
                    <tr>
                        <th style="width: 10em;text-align: right">密码&nbsp;&nbsp;&nbsp;&nbsp;</th>
                        <td><input type="password" name="password" id="password" placeholder="输入密码"/></td>
                        <td id="passwordError" class="error">&nbsp;</td>
                    </tr>
                    <tr>
                        <td colspan="3">&nbsp;</td>
                    </tr>
                    <tr>
                        <td colspan="3">请填写题目详情。</td>
                    </tr>
                    <tr>
                        <th style="text-align: right">题库&nbsp;&nbsp;&nbsp;&nbsp;</th>
                        <td><select name="oj" id="oj">
                                <option value="CF">CF</option>
                                <option value="THU">清澄</option>
                                <option value="POJ">POJ</option>
                                <option value="HDU">HDU</option>
                                <option value="BZOJ">BZOJ</option>
                            </select></td>
                    </tr>
                    <tr>
                        <th style="text-align: right">编号&nbsp;&nbsp;&nbsp;&nbsp;</th>
                        <td><input type="text" name="number" id="number"/></td>
                        <td id="numberError" class="error">&nbsp;</td>
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
                        document.getElementById('passwordError').innerHTML = '&nbsp;';
                        document.getElementById('numberError').innerHTML = '&nbsp;';

                        var error = null;
                        var hasError = false;
                        error = checkPassword(document.getElementById('password').value);
                        if (error != null) {
                            hasError = true;
                            document.getElementById('passwordError').innerHTML = error;
                        }

                        error = checkNumber(document.getElementById('oj').value, document.getElementById('number').value);
                        if (error != null) {
                            hasError = true;
                            document.getElementById('numberError').innerHTML = error;
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
    <?php
    }
?>
</div>
<?php include "footer.php"; ?>
</div>
</body>
</html>