<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 14-10-29
 * Time: 下午4:11
 */
session_start();

require_once(dirname(__FILE__) . "/tools/mysql_tool.php");

$cid = $_GET["id"];
$tab = "home";
if (isset($_GET["tab"])) {
    if ($_GET["tab"] == "my")
        $tab = "my";
    else if ($_GET["tab"] == "status")
        $tab = "status";
    else if ($_GET["tab"] == "rank")
        $tab = "rank";
}
if (isset($_SESSION["user"]))
    $Contest = mysql_getSingleContest($cid, $_SESSION["user"]["id"]);
else $Contest = mysql_getSingleContest($cid);
$now = time();
$startTime = strtotime($Contest["startTime"]);
$endTime = strtotime($Contest["endTime"]);

if (isset($_GET["signUp"])) { //报名
    if (!isset($_SESSION["user"])) {
        header("location: ./login.php");
        exit;
    }
    if ($now >= $startTime)
        die("本场比赛已经过了报名时间");
    if ($Contest["sign"] == 0)
        mysql_signUp($_SESSION["user"]["id"], $cid);
    header("location: ./show_contest.php?id=" . $cid);
    exit;
}

if (isset($_GET["signDown"])) { //取消报名
    if (!isset($_SESSION["user"])) {
        header("location: ./login.php");
        exit;
    }
    if ($now >= $startTime)
        die("本场比赛已经过了报名时间");
    if ($Contest["sign"] == 1)
        mysql_signDown($_SESSION["user"]["id"], $cid);
    header("location: ./show_contest.php?id=" . $cid);
    exit;
}

if ($tab == "home") {
    if (isset($_SESSION["user"]))
        $List = mysql_getContestProblems($cid, $_SESSION["user"]["id"]);
    else $List = mysql_getContestProblems($cid);
} else if ($tab == "my") {
    $page = 1;
    if (isset($_GET["page"]))
        $page = (int)$_GET["page"];
    if (isset($_SESSION["user"]))
        $Status = mysql_getStatusList($page, $cid, $_SESSION["user"]["id"]);
    else $Status = mysql_getStatusList($page, $cid, -2);
} else if ($tab == "status") {
    $page = 1;
    if (isset($_GET["page"]))
        $page = (int)$_GET["page"];
    $Status = mysql_getStatusList($page, $cid);
} else if ($tab == "rank") {
    $Rank = mysql_getContestRankList($cid, $now >= $startTime && (resultVisible($Contest["mode"]) || $now >= $endTime));
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
<h1 style="text-align: center"><?php echo $Contest["title"]; ?></h1>

<div class="tab">
    <ul style="width: 100%">
        <li id="home"><a href="./show_contest.php?id=<?php echo $cid; ?>">比赛主页</a></li>
        <li id="my"><a href="./show_contest.php?tab=my&id=<?php echo $cid; ?>">我的提交记录</a></li>
        <li id="status"><a href="./show_contest.php?tab=status&id=<?php echo $cid; ?>">提交记录</a></li>
        <li id="rank"><a href="./show_contest.php?tab=rank&id=<?php echo $cid; ?>">排行榜</a></li>
        <script>
            (function () {
                document.getElementById('<?php echo $tab;?>').className = 'active';
            })();
        </script>
    </ul>
</div>
<?php
if ($tab == "home") {
    ?>
    <div class="table" style="margin:20px 30%">
        <table>
            <tbody>
            <tr>
                <th>比赛模式</th>
                <td><?php echo $Contest["mode"] ?></td>
            </tr>
            <tr>
                <th>开始时间</th>
                <td><?php echo $Contest["startTime"]; ?></td>
            </tr>
            <?php
            if ($startTime <= $now && $now <= $endTime) {
                ?>
                <tr>
                    <th>剩余时间</th>
                    <td id="time0"><?php echo secondsToString($Contest["duration"]); ?></td>
                    <script>
                        var timeList = [<?php echo $endTime - $now; ?>];
                        function formatTime(time) {
                            var seconds = time % 60;
                            time = Math.floor(time / 60);
                            var minutes = time % 60;
                            time = Math.floor(time / 60);
                            var hours = time % 24;
                            time = Math.floor(time / 24);
                            var days = time;
                            var first = false;
                            var str = '';
                            if (days != 0 || first) {
                                str += days + "天";
                                first = true;
                            }
                            if (hours != 0 || first) {
                                str += hours + "小时";
                                first = true;
                            }
                            if (minutes != 0 || first) {
                                str += minutes + "分钟";
                                first = true;
                            }
                            if (seconds != 0 || first) {
                                str += seconds + "秒";
                                first = true;
                            }
                            if (!first)
                                str = "已结束";
                            return str;
                        }
                        function startTimer(display, time) {
                            display.innerHTML = formatTime(time);
                            var timer = setInterval(function(){
                                time --;
                                display.innerHTML = formatTime(time);
                                if(time == 0)
                                    clearInterval(timer);
                            },1000);
                        }
                        (function () {
                            for (x in timeList)
                                startTimer(document.getElementById("time" + x), timeList[x]);
                        })();
                    </script>
                </tr>
            <?php
            } else {
                ?>
                <tr>
                    <th>比赛时长</th>
                    <td><?php echo secondsToString($Contest["duration"]); ?></td>
                </tr>
            <?php
            }
            ?>
            <tr>
                <th>报名人数</th>
                <td><?php echo $Contest["signUp"]; ?></td>
            </tr>
            <tr>
                <th>管理员</th>
                <td>
                    <a href="./user.php?username=<?php echo $Contest["manager"]; ?>"><?php echo $Contest["manager"]; ?></a>
                </td>
            </tr>
            <?php
            if ($now < $startTime) {
                if ($Contest["sign"] == 0) {
                    ?>
                    <tr>
                        <td colspan="2">
                            <button class="btn"
                                    onclick="location.href='./show_contest.php?signUp&id=<?php echo $cid; ?>'">报名
                            </button>
                        </td>
                    </tr>
                <?php
                } else {
                    ?>
                    <tr>
                        <td colspan="2">
                            <button class="btn"
                                    onclick="location.href='./show_contest.php?signDown&id=<?php echo $cid; ?>'">取消报名
                            </button>
                        </td>
                    </tr>
                <?php
                }
            }
            ?>
            </tbody>
        </table>
    </div>
    <?php
    if ($now >= $endTime || ($now >= $startTime && $Contest["sign"] == 1)) {
        ?>
        <h2 style="text-align: center">试题列表</h2>
        <div class="table">
            <table>
                <thead>
                <tr>
                    <th class="id">#</th>
                    <th class="name">试题名</th>
                    <th class="person">通过次数</th>
                    <th class="person">提交次数</th>
                </tr>
                </thead>
                <tbody>
                <?php
                if (count($List["problems"]) == 0) {
                    ?>
                    <tr>
                        <td colspan="4">无</td>
                    </tr>
                <?php
                } else {
                    for ($x = 0; $x < count($List["problems"]); $x++) {
                        $item = $List["problems"][$x];
                        ?>
                        <tr id="p<?php echo $item["id"]; ?>">
                            <td><?php echo $item["number"]; ?></td>
                            <td>
                                <a href="./show_problem.php?id=<?php echo $item["id"]; ?>"><?php echo $item["title"] ?></a>
                            </td>
                            <td><?php if ($now >= $startTime && (resultVisible($Contest["mode"]) || $now >= $endTime)) echo $item["acceptedCount"]; else echo "0"; ?></td>
                            <td><?php echo $item["submitedCount"]; ?></td>
                        </tr>
                    <?php
                    }
                }
                ?>
                <script>
                    var acProblems = <?php if($now >= $startTime && (resultVisible($Contest["mode"]) || $now >= $endTime))echo json_encode($List["accepted"]);else echo "{}"; ?>;
                    (function () {
                        for (x in acProblems) {
                            document.getElementById('p' + acProblems[x]).className = 'accepted';
                        }
                    })();
                </script>
                </tbody>
            </table>
        </div>
    <?php
    }
}
if ($tab == "status" || $tab == "my") {
    ?>
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
            <?php
            if (count($Status["status"]) == 0) {
                ?>
                <td colspan="9">无</td>
            <?php
            } else {
                for ($x = 0; $x < count($Status["status"]); $x++) {
                    $item = $Status["status"][$x];
                    ?>
                    <tr class="<?php if ($item["accepted"] == 1 && $now >= $startTime && (resultVisible($Contest["mode"]) || $now >= $endTime)) echo "accepted"; ?>">
                        <td><a href="./show_submission.php?id=<?php echo $item["id"]; ?>"><?php echo $item["id"]; ?></a>
                        </td>
                        <td><a href="./show_problem.php?id=<?php echo $item["pid"]; ?>"><?php echo $item["title"] ?></a>
                        </td>
                        <td>
                            <a href="./user.php?username=<?php echo $item["username"]; ?>"><?php echo $item["username"]; ?></a>
                        </td>
                        <?php
                        if (resultVisible($Contest["mode"]) || $now >= $endTime) {
                            if($item["afresh"] == 0)
                            {
                                ?>
                                <td><?php echo $item["result"]; ?></td>
                            <?php
                            }else{
                                ?>
                                <td><a href="action.php?action=afresh&id=<?php echo $item["id"]; ?>"><?php echo $item["result"]; ?></a></td>
                            <?php
                            }
                            ?>
                            <td><?php echo $item["time"]; ?></td>
                            <td><?php echo $item["memory"]; ?></td>
                            <td><?php echo $item["language"]; ?></td>
                            <td><?php echo $item["length"]; ?>b</td>
                            <td><?php echo $item["submitDatetime"]; ?></td>
                        <?php
                        } else {
                            ?>
                            <td>不可见</td>
                            <td>0ms</td>
                            <td>0kb</td>
                            <td><?php echo $item["language"]; ?></td>
                            <td>0b</td>
                            <td><?php echo $item["submitDatetime"]; ?></td>
                        <?php
                        }
                        ?>
                    </tr>
                <?php
                }
            }
            ?>
            </tbody>
        </table>
    </div>
    <div class="pagination">
        <ul>
            <?php
            if ($Status["currentPage"] == 1) {
                ?>
                <li class="disable">
                    <a>首页</a>
                </li>
            <?php
            } else {
                ?>
                <li>
                    <a href="./show_contest.php?tab=<?php echo $tab; ?>&id=<?php echo $cid; ?>">首页</a>
                </li>
            <?php
            }
            $minPage = max(1, $Status["currentPage"] - 4);
            $maxPage = min($Status["totalPage"], $Status["currentPage"] + 4);
            for ($x = $minPage; $x <= $maxPage; $x++) {
                if ($x == $Status["currentPage"]) {
                    ?>
                    <li class="disable">
                        <a><?php echo $x; ?></a>
                    </li>
                <?php
                } else {
                    ?>
                    <li>
                        <a href="./show_contest.php?tab=<?php echo $tab; ?>&id=<?php echo $cid; ?>&page=<?php echo $x; ?>"><?php echo $x; ?></a>
                    </li>
                <?php
                }
            }
            if ($Status["currentPage"] == $Status["totalPage"]) {
                ?>
                <li class="disable">
                    <a>尾页</a>
                </li>
            <?php
            } else {
                ?>
                <li>
                    <a href="./show_contest.php?tab=<?php echo $tab; ?>&id=<?php echo $cid; ?>&page=<?php echo $Status["totalPage"]; ?>">尾页</a>
                </li>
            <?php
            }
            ?>
        </ul>
    </div>
<?php
} else if ($tab == "rank") {
    ?>
    <div class="table">
        <table>
            <thead>
            <tr>
                <th class="rank">#</th>
                <th class="user">用户名</th>
                <th class="score">总分</th>
                <?php
                if ($now >= $startTime) {
                    for ($x = 0; $x < count($Rank["numbers"]); $x++) {
                        ?>
                        <th class="score"><?php echo $Rank["numbers"][$x]; ?></th>
                    <?php
                    }
                }
                ?>
            </tr>
            </thead>
            <tbody>
            <?php
            if (count($Rank["rank"]) == 0) {
                if ($now >= $startTime) {
                    ?>
                    <tr>
                        <td colspan="<?php echo(3 + count($Rank["numbers"])); ?>">无</td>
                    </tr>
                <?php
                } else {
                    ?>
                    <tr>
                        <td colspan="3">无</td>
                    </tr>
                <?php
                }
            } else {
                for ($num = 0; $num < count($Rank["rank"]); $num++) {
                    $item = $Rank["rank"][$num];
                    ?>
                    <tr>
                        <td><?php echo($num + 1); ?></td>
                        <td>
                            <a href="./user.php?username=<?php echo $item["username"]; ?>"><?php echo $item["username"]; ?></a>
                        </td>
                        <?php
                        if ($now >= $startTime) {
                            if (resultVisible($Contest["mode"]) || $now >= $endTime) {
                                ?>
                                <td><?php echo $item["totalScore"]; ?></td>
                                <?php
                                $log = json_decode($item["solveLog"], true);
                                for ($x = 0; $x < count($Rank["numbers"]); $x++) {
                                    if (isset($log[$Rank["numbers"][$x]])) {
                                        ?>
                                        <td><?php echo $log[$Rank["numbers"][$x]]; ?></td>
                                    <?php
                                    } else {
                                        ?>
                                        <td>0</td>
                                    <?php
                                    }
                                    ?>
                                <?php
                                }
                            } else {
                                ?>
                                <td>0</td>
                                <?php
                                for ($x = 0; $x < count($Rank["numbers"]); $x++) {
                                    ?>
                                    <td>0</td>
                                <?php
                                }
                            }
                        } else {
                            ?>
                            <td>0</td>
                        <?php
                        }
                        ?>
                    </tr>
                <?php
                }
            }
            ?>
            </tbody>
        </table>
    </div>
<?php
}
?>
</div>
<?php include "footer.php"; ?>
</div>
</body>
</html>