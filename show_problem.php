<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 14-10-28
 * Time: 下午9:31
 */
session_start();

require_once(dirname(__FILE__) . "/tools/mysql_tool.php");
require_once(dirname(__FILE__) . "/tools/php_tool.php");

if (isset($_GET["post"]) && $_GET["post"] == "submit") {
    if (!isset($_SESSION["user"])) {
        header("location: ./login.php");
        exit;
    }

    $cid = 0;
    if (isset($_POST["cid"]))
        $cid = $_POST["cid"];
    $pid = 0;
    if (isset($_POST["pid"]))
        $pid = $_POST["pid"];
    $language = "";
    if (isset($_POST["language"]))
        $language = $_POST["language"];
    $code = "";
    if (isset($_POST["code"]))
        $code = $_POST["code"];

    if (!checkID($cid))
        die("比赛编号非法");
    if (!checkID($pid))
        die("题目编号非法");
    if (!checkLanguage($language))
        die("语言非法");
    if (!checkCode($code))
        die("代码非法");
    $Problem = mysql_getSingleProblem($pid);
    if ($Problem["cid"] != $cid)
        die("这道题正用于比赛中");
    if ($cid != 0) {
        if (isset($_SESSION["user"]))
            $Contest = mysql_getSingleContest($cid, $_SESSION["user"]["id"]);
        else $Contest = mysql_getSingleContest($cid);
        $now = strtotime(date("Y-m-d H:i:s"));
        $startTime = strtotime($Contest["startTime"]);
        $endTime = strtotime($Contest["endTime"]);
        if ($now < $startTime)
            die("本场比赛还未开始");
        if ($now > $endTime)
            die("本场比赛已经结束");
        if ($Contest["sign"] == 0)
            die("你没有报名这场比赛");
    }

    mysql_addSubmission($_SESSION["user"]["id"], $cid, $pid, $language, $code);
    if ($cid == 0) {
        header("location: ./status.php");
    } else {
        header("location: ./show_contest.php?tab=my&id=" . $cid);
    }
    exit;
}

if (isset($_GET["post"]) && $_GET["post"] == "modify") {
    if (!isset($_SESSION["user"]) || !isset($_SESSION["admin"]) || $_SESSION["admin"] != 1) {
        header("location: ./login.php");
        exit;
    }
    $pid = -1;
    if (isset($_POST["pid"]))
        $pid = $_POST["pid"];
    $description = null;
    if (isset($_POST["description"]) && $_POST["description"]!="<br>")
        $description = $_POST["description"];
    $input = null;
    if (isset($_POST["input"]) && $_POST["input"]!="<br>")
        $input = $_POST["input"];
    $output = null;
    if (isset($_POST["output"]) && $_POST["output"]!="<br>")
        $output = $_POST["output"];
    $sample = null;
    if (isset($_POST["sample"]) && $_POST["sample"]!="<br>")
        $sample = $_POST["sample"];
    $datarange = null;
    if (isset($_POST["datarange"]) && $_POST["datarange"]!="<br>")
        $datarange = $_POST["datarange"];
    $hint = null;
    if (isset($_POST["hint"]) && $_POST["hint"]!="<br>")
        $hint = $_POST["hint"];
    $translate = null;
    if (isset($_POST["translate"]) && $_POST["translate"]!="<br>")
        $translate = $_POST["translate"];
    mysql_modifyProblem($pid, $description, $input, $output, $sample, $datarange, $hint, $translate);
    header("Location: ./show_problem.php?id=" . $pid);
    exit;
}

$pid = $_GET["id"];
$Problem = mysql_getSingleProblem($_GET["id"]);
$cid = $Problem["cid"];
if ($cid != 0 && (!isset($_SESSION["admin"]) || $_SESSION["admin"] != 1)) {
    if (isset($_SESSION["user"]))
        $Contest = mysql_getSingleContest($cid, $_SESSION["user"]["id"]);
    else $Contest = mysql_getSingleContest($cid);
    $now = strtotime(date("Y-m-d H:i:s"));
    $startTime = strtotime($Contest["startTime"]);
    $endTime = strtotime($Contest["endTime"]);
    if ($now < $startTime)
        die("题目不可看");
    if ($Contest["sign"] == 0)
        die("你没有报名这场比赛");
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8"/>
    <link rel="shortcut icon" href="./img/logo.png">
    <title>虚拟在线评测系统</title>
    <link rel="stylesheet" href="./main.css" type="text/css"/>
    <style>
        textarea {
            width: 100%;
        }
    </style>
</head>
<body>
<div class="container">
<?php include "./header.php"; ?>
<div class="box problem">

    <h1><?php echo $Problem["title"]; ?></h1>
    <?php
    if ($Problem["cid"] != 0) {
        ?>
        <h5><a href="./show_contest.php?id=<?php echo $Problem["cid"]; ?>">返回比赛</a></h5>
    <?php
    }
    ?>
    <p style="text-align: center"><strong>时间限制：</strong><?php echo $Problem["timelimit"]; ?> &nbsp;&nbsp;
        <strong>空间限制：</strong><?php echo $Problem["memorylimit"]; ?>
    </p>

    <p style="text-align: center"><strong>64位整数输入输出格式：</strong><?php echo $Problem["64Format"]; ?></p>

    <?php
    if (isset($_SESSION["admin"]) && $_SESSION["admin"] == 1) {
        ?>
        <form action="./show_problem.php?post=modify" method="post">
            <input type="hidden" name="pid" value="<?php echo $Problem["id"]; ?>"/>

            <h3>题目描述</h3>
            <textarea name="description" id="description"><?php echo $Problem["description"]; ?></textarea>

            <h3>输入格式</h3>
            <textarea name="input" id="input"><?php echo $Problem["input"]; ?></textarea>

            <h3>输出格式</h3>
            <textarea name="output" id="output"><?php echo $Problem["output"]; ?></textarea>

            <h3>样例</h3>
            <textarea name="sample" id="sample"><?php echo $Problem["sample"]; ?></textarea>

            <h3>数据范围与约定</h3>
            <textarea name="datarange" id="datarange"><?php echo $Problem["datarange"]; ?></textarea>

            <h3>Note</h3>
            <textarea name="hint" id="hint"><?php echo $Problem["hint"]; ?></textarea>

            <h3>翻译及提示</h3>
            <textarea name="translate" id="translate"><?php echo $Problem["translate"]; ?></textarea>

            <button type="submit" class="btn" style="float: right">&nbsp; 修改 &nbsp;</button>

            <script src="./nicEdit/nicEdit.js" type="text/javascript" charset="utf-8"></script>
            <script>
                (function () {
                    nicEditors.allTextAreas();
                })();
                function submitForm() {
                    if (document.getElementById('description').innerHTML == '&lt;br&gt;')
                        document.getElementById('description').innerHTML = '';
                    if (document.getElementById('input').innerHTML == '&lt;br&gt;')
                        document.getElementById('input').innerHTML = '';
                    if (document.getElementById('output').innerHTML == '&lt;br&gt;')
                        document.getElementById('output').innerHTML = '';
                    if (document.getElementById('sample').innerHTML == '&lt;br&gt;')
                        document.getElementById('sample').innerHTML = '';
                    if (document.getElementById('datarange').innerHTML == '&lt;br&gt;')
                        document.getElementById('datarange').innerHTML = '';
                    if (document.getElementById('hint').innerHTML == '&lt;br&gt;')
                        document.getElementById('hint').innerHTML = '';
                    if (document.getElementById('translate').innerHTML == '&lt;br&gt;')
                        document.getElementById('translate').innerHTML = '';
                    alert(document.getElementById('translate').innerHTML);
                    alert(document.getElementById('translate').innerHTML == '&lt;br&gt;');
                }
            </script>
        </form>
    <?php
    } else {
        if ($Problem["description"] != null && $Problem["description"] != "") {
            ?>
            <h3>题目描述</h3>

            <p><?php echo $Problem["description"]; ?></p>
        <?php
        }
        if ($Problem["input"] != null && $Problem["input"] != "") {
            ?>
            <h3>输入格式</h3>

            <p><?php echo $Problem["input"]; ?></p>
        <?php
        }
        if ($Problem["output"] != null && $Problem["output"] != "") {
            ?>
            <h3>输出格式</h3>

            <p><?php echo $Problem["output"]; ?></p>
        <?php
        }
        if ($Problem["sample"] != null && $Problem["sample"] != "") {
            ?>
            <h3>样例</h3>

            <p><?php echo $Problem["sample"]; ?></p>
        <?php
        }
        if ($Problem["datarange"] != null && $Problem["datarange"] != "") {
            ?>
            <h3>数据范围与约定</h3>

            <p><?php echo $Problem["datarange"]; ?></p>
        <?php
        }
        if ($Problem["hint"] != null && $Problem["hint"] != "") {
            ?>
            <h3>Note</h3>

            <p><?php echo $Problem["hint"]; ?></p>
        <?php
        }
        if ($Problem["translate"] != null && $Problem["translate"] != "") {
            ?>
            <h3>翻译及提示</h3>

            <p><?php echo $Problem["translate"]; ?></p>
        <?php
        }
    }
    if ($Problem["source"] != null && $Problem["source"] != "") {
        ?>
        <h3>来源</h3>

        <p><?php echo $Problem["source"]; ?></p>
    <?php
    }
    if ($Problem["statusUrl"] != null && $Problem["statusUrl"] != "" && $Problem["cid"] == 0) {
        ?>
        <h3>提交情况</h3>

        <p><a href="<?php echo $Problem["statusUrl"]; ?>" target="_blank">提交情况</a></p>
    <?php
    }
    ?>

</div>
<div class="box submit">
    <form
        action="./show_problem.php?post=submit" method="post">
        <input type="hidden" id="cid" name="cid" value="<?php echo $Problem["cid"]; ?>"/>
        <input type="hidden" id="pid" name="pid" value="<?php echo $Problem["id"]; ?>"/>
        <select style="display: none" id="language" name="language"></select>
        <input type="hidden" id="code" name="code"/>

        <div class="btn-group" id="group"></div>
        <div id="editor" class="editor"></div>
        <button type="submit" class="btn btn-clicked">&nbsp; 提交 &nbsp;</button>
        <?php
        if ($Problem["cid"] == 0) {
            ?>
            <a href="./status.php">查看提交记录</a>
        <?php
        } else {
            ?>
            <a href="./show_contest.php?cid=<?php echo $Problem["cid"]; ?>&tab=my">查看提交记录</a>
        <?php
        }
        ?>

        <script src="./ace/ace.js" type="text/javascript" charset="utf-8"></script>
        <script src="./ace/theme-xcode.js" type="text/javascript" charset="utf-8"></script>
        <script src="./ace/mode-c_cpp.js" type="text/javascript" charset="utf-8"></script>
        <script src="./ace/mode-pascal.js" type="text/javascript" charset="utf-8"></script>
        <script>
            var langList = { "C++": "cpp", "C": "c", "Pascal": "pascal" };
            var codeEditer = ace.edit('editor');
            codeEditer.setTheme('ace/theme/xcode');
            codeEditer.getSession().setMode('ace/mode/c_cpp');

            function getDefaultCode(language) {
                var codes = {
                    "C++": "/*\n//如何写一份可以提交的代码？以A+B为例\n#include <iostream>\nusing namespace std;\nint main()\n{\n\tint a, b; //定义两个变量名\n\tcin >> a >> b; //从标准输入流中输入两个整数\n\tcout << a + b << endl; //输出到标准输出流中\n\treturn 0;\n}\n// 完成程序以后，点击下方的提交，即可看到测试结果\n*/\n",
                    "C": "/*\n//如何写一份可以提交的代码？以A+B为例\n#include <stdio.h>\nint main()\n{\n\tint a, b; //定义两个变量名\n\tscanf(\"%d%d\", &a, &b);//从标准输入流中输入两个整数\n\tprintf(\"%d\\n\", a+b);//输出到标准输出流中\n\treturn 0;\n}\n// 完成程序以后，点击下方的提交，即可看到测试结果\n*/\n",
                    "Pascal": "{\n//如何写一份可以提交的代码？以A+B为例\nvar a, b:longint; //定义两个变量名\nbegin\n\treadln(a,b); //从标准输入流中输入两个整数\n\twriteln(a+b); //输出到标准输出流中\nend.\n// 完成程序以后，点击下方的提交，即可看到测试结果\n}\n"
                }
                return codes[language];
            }

            function getCode(language) {
                var code = localStorage.getItem(get_code_key());
                if (code != null && code != '') {
                    return code;
                } else {
                    return getDefaultCode(language);
                }
            }

            function get_code_key() {
                return 'problem-' + document.getElementById('pid').value + '-language-' + document.getElementById('language').value;
            }

            function changeLang(desiredLang) {
                var nick = langList[desiredLang];
                document.getElementById('language').value = desiredLang;
                for (x in langList) {
                    var btn = langList[x] + '-btn';
                    if (x == desiredLang)
                        document.getElementById(btn).className = 'btn btn-clicked';
                    else document.getElementById(btn).className = 'btn';
                }

                var mode = 'ace/mode/c_cpp';
                if (desiredLang == 'Pascal')
                    mode = 'ace/mode/pascal';
                codeEditer.getSession().setMode(mode);

                localStorage.setItem('previousLang', desiredLang);
                codeEditer.setValue(getCode(desiredLang), -1);
            }

            codeEditer.getSession().on('change', function (e) {
                document.getElementById('code').value = codeEditer.getValue();
                localStorage.setItem(get_code_key(), codeEditer.getValue());
            });

            (function () {
                /*语言选项*/
                var langSelect = document.getElementById('language');
                langSelect.options.length = 0;
                for (x in langList) {
                    var varItem = new Option(x, x);
                    langSelect.options.add(varItem);
                }
                /*按钮*/
                var btnGroup = document.getElementById('group');
                var buttons = '';
                for (x in langList) {
                    buttons += '<button type="button" class="btn" ';
                    buttons += 'onclick="changeLang(\'' + x + '\')" ';
                    buttons += 'id="' + langList[x] + '-btn">';
                    buttons += '&nbsp;' + x + '&nbsp;</button>';
                    buttons += '\n';
                }
                btnGroup.innerHTML = buttons;
                /*代码*/
                var previousLang = localStorage.getItem('previousLang');
                if (previousLang != '' && previousLang != null) {
                    changeLang(previousLang);
                } else {
                    changeLang('C++');
                }
            })();
        </script>
    </form>
</div>
<?php include "./footer.php"; ?>
</div>
</body>
</html>