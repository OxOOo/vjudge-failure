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
if (isset($_SESSION["user"]))
    $List = mysql_getProblemList($_SESSION["user"]["id"], $page, (isset($_SESSION["admin"]) ? $_SESSION["admin"] : 0));
else $List = mysql_getProblemList(-1, $page);

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
                <tr>
                    <th class="oj">题库</th>
                    <th class="id">编号</th>
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
                        <td colspan="5">无</td>
                    </tr>
                <?php
                } else {
                    for ($x = 0; $x < count($List["problems"]); $x++) {
                        $item = $List["problems"][$x];
                        ?>
                        <tr id="p<?php echo $item["id"]; ?>">
                            <td><?php echo $item["originOJ"]; ?></td>
                            <td><?php echo $item["originID"]; ?></td>
                            <td style="text-align: left"><a
                                    href="./show_problem.php?id=<?php echo $item["id"]; ?>"><?php echo $item["title"]; ?></a>
                            </td>
                            <td><?php echo $item["acceptedCount"]; ?></td>
                            <td><?php echo $item["submitedCount"]; ?></td>
                        </tr>
                    <?php
                    }
                }
                ?>
                <script>
                    var acProblems = <?php echo json_encode($List["accepted"]); ?>;
                    (function () {
                        for (x in acProblems) {
                            document.getElementById('p' + acProblems[x]).className = 'accepted';
                        }
                    })();
                </script>
                </tbody>
            </table>
        </div>
        <div class="pagination">
            <ul>
                <?php
                if ($List["currentPage"] == 1) {
                    ?>
                    <li class="disable">
                        <a>首页</a>
                    </li>
                <?php
                } else {
                    ?>
                    <li>
                        <a href="./problems.php">首页</a>
                    </li>
                <?php
                }
                $minPage = max(1, $List["currentPage"] - 4);
                $maxPage = min($List["totalPage"], $List["currentPage"] + 4);
                for ($x = $minPage; $x <= $maxPage; $x++) {
                    if ($x == $List["currentPage"]) {
                        ?>
                        <li class="disable">
                            <a><?php echo $x; ?></a>
                        </li>
                    <?php
                    } else {
                        ?>
                        <li>
                            <a href="./problems.php?page=<?php echo $x; ?>"><?php echo $x; ?></a>
                        </li>
                    <?php
                    }
                }
                if ($List["currentPage"] == $List["totalPage"]) {
                    ?>
                    <li class="disable">
                        <a>尾页</a>
                    </li>
                <?php
                } else {
                    ?>
                    <li>
                        <a href="./problems.php?page=<?php echo $List["totalPage"]; ?>">尾页</a>
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