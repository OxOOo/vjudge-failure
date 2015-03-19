<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 14-10-28
 * Time: 下午3:33
 */
session_start();

require_once(dirname(__FILE__) . "/tools/mysql_tool.php");

$page = 1;
if (isset($_GET["page"]))
    $page = (int)$_GET["page"];
$Status = mysql_getStatusList($page);
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
            <table>
                <thead>
                <th class="id">ID</th>
                <th class="oj">题库</th>
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
                    <td colspan="10">无</td>
                <?php
                } else {
                    for ($x = 0; $x < count($Status["status"]); $x++) {
                        $item = $Status["status"][$x];
                        ?>
                        <tr class="<?php if($item["accepted"]==1)echo "accepted"; ?>">
                            <td>
                                <a href="./show_submission.php?id=<?php echo $item["id"]; ?>"><?php echo $item["id"]; ?></a>
                            </td>
                            <td><?php echo $item["originOJ"]; ?></td>
                            <td>
                                <a href="./show_problem.php?id=<?php echo $item["pid"]; ?>"><?php echo $item["title"] ?></a>
                            </td>
                            <td>
                                <a href="./user.php?username=<?php echo $item["username"]; ?>"><?php echo $item["username"]; ?></a>
                            </td>
                            <?php
                            if($item["afresh"] == 0){
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
                        <a href="./status.php">首页</a>
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
                            <a href="./status.php?page=<?php echo $x; ?>"><?php echo $x; ?></a>
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
                        <a href="./status.php?page=<?php echo $Status["totalPage"]; ?>">尾页</a>
                    </li>
                <?php
                }
                ?>
            </ul>
        </div>
    </div>
    <?php include "./footer.php"; ?>
</div>
</body>
</html>