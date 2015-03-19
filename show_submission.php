<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 14-10-29
 * Time: 下午4:46
 */
session_start();

require_once(dirname(__FILE__) . "/tools/mysql_tool.php");

$sid = $_GET["id"];
$Status = mysql_getSingleStatus($sid);
if ($Status["cid"] != 0) {
    $Contest = mysql_getSingleContest($Status["cid"]);
    $now = strtotime(date("Y-m-d H:i:s"));
    $endTime = strtotime($Contest["endTime"]);
}

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
    <div class="box">
        <div class="table">
            <table>
                <thead>
                <th class="id">ID</th>
                <th class="name">题目</th>
                <th class="user">提交者</th>
                <th class="result">结果</th>
                <th class="time">时间</th>
                <th class="memory">内存</th>
                <th class="language">语言</th>
                <th class="length">代码长度</th>
                <th class="date">提交时间</th>
                </thead>
                <tbody>
                <tr class="<?php if ($Status["accepted"] == 1 && ($Status["cid"] == 0 || (resultVisible($Contest["mode"]) || $now >= $endTime))) echo "accepted"; ?>">
                    <td><a href="./show_submission.php?id=<?php echo $Status["id"]; ?>"><?php echo $Status["id"]; ?></a>
                    </td>
                    <td>
                        <a href="./show_problem.php?id=<?php echo $Status["pid"]; ?>"><?php echo $Status["title"]; ?></a>
                    </td>
                    <td>
                        <a href="./user.php?username=<?php echo $Status["username"]; ?>"><?php echo $Status["username"]; ?></a>
                    </td>
                    <?php
                    if ($Status["cid"] == 0 || $now > $endTime || resultVisible($Contest["mode"])) {
                        if ($Status["afresh"] == 0) {
                            ?>
                            <td><?php echo $Status["result"]; ?></td>
                        <?php
                        } else {
                            ?>
                            <td>
                                <a href="action.php?action=afresh&id=<?php echo $Status["id"]; ?>"><?php echo $Status["result"]; ?></a>
                            </td>
                        <?php
                        }
                        ?>
                        <td><?php echo $Status["time"]; ?></td>
                        <td><?php echo $Status["memory"]; ?></td>
                        <td><?php echo $Status["language"]; ?></td>
                        <td><?php echo $Status["length"]; ?>b</td>
                        <td><?php echo $Status["submitDatetime"]; ?></td>
                    <?php
                    } else {
                        ?>
                        <td>不可见</td>
                        <td>0ms</td>
                        <td>0kb</td>
                        <td><?php echo $Status["language"]; ?></td>
                        <td>0b</td>
                        <td><?php echo $Status["submitDatetime"]; ?></td>
                    <?php
                    }
                    ?>
                </tr>
                </tbody>
            </table>
            <?php
            if ($Status["cid"] == 0 || $now > $endTime || (isset($_SESSION["user"]) && $_SESSION["user"]["username"] == $Status["username"])) {
                ?>
                <div id="editor" class="editor"><?php echo htmlspecialchars($Status["source"]); ?></div>
                <script src="./ace/ace.js" type="text/javascript" charset="utf-8"></script>
                <script src="./ace/theme-xcode.js" type="text/javascript" charset="utf-8"></script>
                <script src="./ace/mode-c_cpp.js" type="text/javascript" charset="utf-8"></script>
                <script>
                    var editor = ace.edit("editor");
                    var modeList = { "C++": "c_cpp", "C": "c_cpp", "Pascal": "pascal" };
                    var language = "<?php echo $Status["language"] ?>";
                    editor.setTheme("ace/theme/xcode");
                    editor.getSession().setMode('ace/mode/' + modeList[language]);
                    editor.setReadOnly(true);
                </script>
            <?php
            }
            if ($Status["judgeLog"] != null && ($Status["cid"] == 0 || $now > $endTime || (resultVisible($Contest["mode"]) && isset($_SESSION["user"]) && $_SESSION["user"]["username"] == $Status["username"]))) {
                ?>
                <div class="panel">
                    <div class="head">
                        <h4>详情</h4>
                    </div>
                    <?php
                    if (strpos($Status["judgeLog"], "Compile Error") === 0) {
                        ?>
                        <div class="info">
                            <?php echo $Status["judgeLog"]; ?>
                        </div>
                    <?php
                    } else {
                        $log = json_decode($Status["judgeLog"], true);
                        for ($x = 0; $x < count($log); $x++) {
                            $item = $log[$x];
                            ?>
                            <div class="<?php echo "info " . $item["type"]; ?>">
                                <div><?php echo $item["result"]; ?></div>
                                <div><?php echo $item["time"]; ?></div>
                                <div><?php echo $item["memory"]; ?></div>
                            </div>
                        <?php
                        }
                    }
                    ?>
                </div>
            <?php
            }
            ?>
        </div>
    </div>
    <?php include "footer.php"; ?>
</div>
</body>
</html>