<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 14-10-29
 * Time: 上午8:42
 */
session_start();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8"/>
    <link rel="shortcut icon" href="./img/logo.png">
    <title>虚拟在线评测系统</title>
    <link rel="stylesheet" href="./main.css" type="text/css"/>
</head>
<body>
<div class="container">
    <?php include "header.php"; ?>
    <div class="box" style="padding: 20px">
        <h2 style="text-align: center">云IDE</h2>

        <div style="overflow: hidden">
            <div class="btn-group" id="group" style="float: left;margin: 0"></div>
            <h4 style="float: right;margin: 0">注意，本IDE未添加任何防护措施，请不要调戏服务器。</h4>
        </div>

        <div id="editor" class="editor"></div>
        <div style="overflow: hidden">
            <h5 style="float: left">状态:<label id="status">一切正常</label></h5>
            <button type="button" class="btn" style="float: right" onclick="submitTest()">提交</button>
        </div>
        <div style="overflow: hidden">
            <div style="float: left;width: 45%">
                <h3>Input</h3>

                <div id="input" class="editor"></div>
            </div>
            <div style="float: right;width: 45%">
                <h3>Output</h3>

                <div id="output" class="editor"></div>
            </div>
        </div>

        <script src="./ace/ace.js" type="text/javascript" charset="utf-8"></script>
        <script src="./ace/theme-xcode.js" type="text/javascript" charset="utf-8"></script>
        <script src="./ace/mode-c_cpp.js" type="text/javascript" charset="utf-8"></script>
        <script src="./ace/mode-pascal.js" type="text/javascript" charset="utf-8"></script>
        <script>
            var codeEditer = ace.edit('editor');
            var inputEditer = ace.edit('input');
            var outputEditer = ace.edit('output');
            var statusLabel = document.getElementById('status');
            var langList = { "C++": "cpp", "C": "c", "Pascal": "pascal" };
            var language = 'C++';
            codeEditer.setTheme('ace/theme/xcode');
            inputEditer.setTheme('ace/theme/xcode');
            outputEditer.setTheme('ace/theme/xcode');
            codeEditer.getSession().setMode('ace/mode/c_cpp');

            function getDefaultCode(language) {
                var codes = {
                    "C++": "#include <iostream>\nusing namespace std;\nint main()\n{\n\tint a, b; //定义两个变量名\n\tcin >> a >> b; //从标准输入流中输入两个整数\n\tcout << a + b << endl; //输出到标准输出流中\n\treturn 0;\n}\n",
                    "C": "#include <stdio.h>\nint main()\n{\n\tint a, b; //定义两个变量名\n\tscanf(\"%d%d\", &a, &b);//从标准输入流中输入两个整数\n\tprintf(\"%d\\n\", a+b);//输出到标准输出流中\n\treturn 0;\n}\n",
                    "Pascal": "var a, b:longint; //定义两个变量名\nbegin\n\treadln(a,b); //从标准输入流中输入两个整数\n\twriteln(a+b); //输出到标准输出流中\nend.\n"
                }
                return codes[language];
            }

            function getCode(language) {
                var code = localStorage.getItem(get_code_key(language));
                if (code != null && code != '') {
                    return code;
                } else {
                    return getDefaultCode(language);
                }
            }

            function get_code_key(language) {
                return 'IDE-code-' + language;
            }

            function changeLang(desiredLang) {
                var nick = langList[desiredLang];
                language = desiredLang;
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

                localStorage.setItem('IDE-previousLang', desiredLang);
                codeEditer.setValue(getCode(desiredLang), -1);
            }

            function submitTest() {
                var xmlHttpRequest = null;
                if (window.ActiveXObject) { //如果是IE浏览器
                    xmlHttpRequest = new ActiveXObject("Microsoft.XMLHTTP");
                } else if (window.XMLHttpRequest) { //非IE浏览器
                    xmlHttpRequest = new XMLHttpRequest();
                }
                if (xmlHttpRequest == null) {
                    alert('浏览器不支持');
                    return;
                }

                outputEditer.setValue('', -1);
                xmlHttpRequest.onreadystatechange = function () {
                    if (xmlHttpRequest.readyState == 4 && xmlHttpRequest.status == 200) {
                        var html = xmlHttpRequest.responseText;
                        var reg = new RegExp('^\\d+$');
                        if (reg.test(html)) {
                            getResult(html);
                        } else {
                            statusLabel.innerHTML = '出现错误';
                            alert(html);
                        }
                    }
                };
                statusLabel.innerHTML = '正在上传代码';
                var parm = "language=" + encodeURIComponent(language) + "&code=" + encodeURIComponent(codeEditer.getValue()) + "&input=" + encodeURIComponent(inputEditer.getValue());
                xmlHttpRequest.open('POST', './action.php?action=test', true);
                xmlHttpRequest.setRequestHeader("contentType", "text/html;charset=uft-8");
                xmlHttpRequest.setRequestHeader("Content-Type", "application/x-www-form-urlencoded;");
                xmlHttpRequest.send(parm);
            }

            function getResult(id) {
                statusLabel.innerHTML = '正在获取结果';
                var timer = setInterval(function () {
                    statusLabel.innerHTML = '正在获取结果';
                    var xmlHttpRequest = null;
                    if (window.ActiveXObject) { //如果是IE浏览器
                        xmlHttpRequest = new ActiveXObject("Microsoft.XMLHTTP");
                    } else if (window.XMLHttpRequest) { //非IE浏览器
                        xmlHttpRequest = new XMLHttpRequest();
                    }
                    if (xmlHttpRequest == null) {
                        statusLabel.innerHTML = '浏览器不支持';
                        clearInterval(timer);
                        return;
                    }

                    xmlHttpRequest.onreadystatechange = function () {
                        if (xmlHttpRequest.readyState == 4 && xmlHttpRequest.status == 200) {
                            var html = xmlHttpRequest.responseText;
                            var json = JSON.parse(html);
                            if (json['finish'] == '1') {
                                statusLabel.innerHTML = '已获取评测结果';
                                clearInterval(timer);
                                if (json['error'] == '1')
                                    statusLabel.innerHTML = '出现错误';
                                outputEditer.setValue(json['result'], -1);
                            }
                        }
                    };
                    xmlHttpRequest.open('GET', './action.php?action=getTest&id=' + id, true);
                    xmlHttpRequest.send(null);
                }, 2000);
            }

            codeEditer.getSession().on('change', function (e) {
                localStorage.setItem(get_code_key(language), codeEditer.getValue());
            });
            inputEditer.getSession().on('change', function (e) {
                localStorage.setItem('IDE-input', inputEditer.getValue());
            });

            (function () {
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
                var previousLang = localStorage.getItem('IDE-previousLang');
                if (previousLang != null && previousLang != '') {
                    changeLang(previousLang);
                } else {
                    changeLang('C++');
                }

                var input = localStorage.getItem('IDE-input');
                if (input != null)
                    inputEditer.setValue(input, -1);
            })();
        </script>
    </div>
    <?php include "footer.php"; ?>
</div>
</body>
</html>