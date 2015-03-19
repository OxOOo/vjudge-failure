<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 14-10-28
 * Time: 下午3:32
 */
session_start();

require_once(dirname(__FILE__) . "/tools/mysql_tool.php");

$page = 1;
if (isset($_GET["page"]))
    $page = (int)$_GET["page"];
$RunningContests = mysql_getRunningContests();
$PendingContests = mysql_getPendingContests();
$PastContests = mysql_getPastContests($page);
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
        <div class="table">
            <h4>正在进行的比赛</h4>
            <table>
                <thead>
                <tr>
                    <th class="id">ID</th>
                    <th class="name">比赛名称</th>
                    <th class="date">开始时间</th>
                    <th class="duration">剩余时间</th>
                    <th class="person">报名人数</th>
                </tr>
                </thead>
                <tbody>
                <?php
                $timeList = array();
                if (count($RunningContests) == 0) {
                    ?>
                    <tr>
                        <td colspan="5">无</td>
                    </tr>
                <?php
                } else {
                    for ($x = 0; $x < count($RunningContests); $x++) {
                        $item = $RunningContests[$x];
                        array_push($timeList,$item["duration"] - time() + strtotime($item["startTime"]));
                        ?>
                        <tr>
                            <td><?php echo $item["id"]; ?></td>
                            <td>
                                <a href="./show_contest.php?id=<?php echo $item["id"]; ?>"><?php echo $item["title"]; ?></a>
                            </td>
                            <td><?php echo $item["startTime"]; ?></td>
                            <td id="time<?php echo $x; ?>"><?php echo secondsToString($item["duration"]); ?></td>
                            <td><?php echo $item["signUp"]; ?></td>
                        </tr>
                    <?php
                    }
                }
                ?>
                </tbody>
                <script>
                    var timeList = <?php echo json_encode($timeList); ?>;
                    function formatTime(time)
                    {
                        var seconds = time % 60;time = Math.floor(time / 60);
                        var minutes = time % 60;time = Math.floor(time / 60);
                        var hours = time % 24;time = Math.floor(time / 24);
                        var days = time;
                        var first = false;
                        var str = '';
                        if(days != 0 || first){
                            str += days + "天";
                            first = true;
                        }
                        if(hours != 0 || first){
                            str += hours + "小时";
                            first = true;
                        }
                        if(minutes != 0 || first){
                            str += minutes + "分钟";
                            first = true;
                        }
                        if(seconds != 0 || first){
                            str += seconds + "秒";
                            first = true;
                        }
                        if(!first)
                            str = "已结束";
                        return str;
                    }
                    function startTimer(display,time)
                    {
                        display.innerHTML = formatTime(time);
                        var timer = setInterval(function(){
                            time --;
                            display.innerHTML = formatTime(time);
                            if(time == 0)
                                clearInterval(timer);
                        },1000);
                    }
                    (function(){
                        for(x in timeList)
                            startTimer(document.getElementById("time" + x),timeList[x]);
                    })();
                </script>
            </table>
            <h4>即将到来的比赛</h4>
            <table>
                <thead>
                <tr>
                    <th class="id">ID</th>
                    <th class="name">比赛名称</th>
                    <th class="date">开始时间</th>
                    <th class="duration">比赛时长</th>
                    <th class="person">报名人数</th>
                </tr>
                </thead>
                <tbody>
                <?php
                if (count($PendingContests) == 0) {
                    ?>
                    <tr>
                        <td colspan="5">无</td>
                    </tr>
                <?php
                } else {
                    for ($x = 0; $x < count($PendingContests); $x++) {
                        $item = $PendingContests[$x];
                        ?>
                        <tr>
                            <td><?php echo $item["id"]; ?></td>
                            <td>
                                <a href="./show_contest.php?id=<?php echo $item["id"]; ?>"><?php echo $item["title"]; ?></a>
                            </td>
                            <td><?php echo $item["startTime"]; ?></td>
                            <td><?php echo secondsToString($item["duration"]); ?></td>
                            <td><?php echo $item["signUp"]; ?></td>
                        </tr>
                    <?php
                    }
                }
                ?>
                </tbody>
            </table>
            <h4>已结束的比赛</h4>
            <table>
                <thead>
                <tr>
                    <th class="id">ID</th>
                    <th class="name">比赛名称</th>
                    <th class="date">开始时间</th>
                    <th class="duration">比赛时长</th>
                    <th class="person">参赛人数</th>
                </tr>
                </thead>
                <tbody>
                <?php
                if (count($PastContests["contests"]) == 0) {
                    ?>
                    <tr>
                        <td colspan="5">无</td>
                    </tr>
                <?php
                } else {
                    for ($x = 0; $x < count($PastContests["contests"]); $x++) {
                        $item = $PastContests["contests"][$x];
                        ?>
                        <tr>
                            <td><?php echo $item["id"]; ?></td>
                            <td>
                                <a href="./show_contest.php?id=<?php echo $item["id"]; ?>"><?php echo $item["title"]; ?></a>
                            </td>
                            <td><?php echo $item["startTime"]; ?></td>
                            <td><?php echo secondsToString($item["duration"]); ?></td>
                            <td><?php echo $item["signUp"]; ?></td>
                        </tr>
                    <?php
                    }
                }
                ?>
                </tbody>
            </table>
            <div class="pagination">
                <ul>
                    <?php
                    if ($PastContests["currentPage"] == 1) {
                        ?>
                        <li class="disable">
                            <a>首页</a>
                        </li>
                    <?php
                    } else {
                        ?>
                        <li>
                            <a href="./contests.php">首页</a>
                        </li>
                    <?php
                    }
                    $minPage = max(1, $PastContests["currentPage"] - 4);
                    $maxPage = min($PastContests["totalPage"], $PastContests["currentPage"] + 4);
                    for ($x = $minPage; $x <= $maxPage; $x++) {
                        if ($x == $PastContests["currentPage"]) {
                            ?>
                            <li class="disable">
                                <a><?php echo $x; ?></a>
                            </li>
                        <?php
                        } else {
                            ?>
                            <li>
                                <a href="./contests.php?page=<?php echo $x; ?>"><?php echo $x; ?></a>
                            </li>
                        <?php
                        }
                    }
                    if ($PastContests["currentPage"] == $PastContests["totalPage"]) {
                        ?>
                        <li class="disable">
                            <a>尾页</a>
                        </li>
                    <?php
                    } else {
                        ?>
                        <li>
                            <a href="./contests.php?page=<?php echo $PastContests["totalPage"]; ?>">尾页</a>
                        </li>
                    <?php
                    }
                    ?>
                </ul>
            </div>
        </div>
    </div>
    <?php include "./footer.php"; ?>
</div>
</body>
</html>