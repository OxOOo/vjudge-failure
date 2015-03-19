<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 14-10-29
 * Time: 上午8:42
 */
session_start();

require_once(dirname(__FILE__) . "/tools/mysql_tool.php");

$Rank = mysql_getRankList();
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
        <div class="table">
            <table style="width: 50%">
                <thead>
                <th class="id">ID</th>
                <th class="user">账号</th>
                <th class="score">总分</th>
                </thead>
                <tbody>
                <?php
                if (count($Rank) == 0) {
                    ?>
                    <td colspan="3">无</td>
                <?php
                } else {
                    for ($x = 0; $x < count($Rank); $x++) {
                        $item = $Rank[$x];
                        ?>
                        <tr>
                            <td><?php echo $x+1; ?></td>
                            <td><a href="./user.php?username=<?php echo $item["username"];?>"><?php echo $item["username"]; ?></a></td>
                            <td><?php echo $item["totalScore"]; ?></td>
                        </tr>
                    <?php
                    }
                }
                ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php include "footer.php"; ?>
</div>
</body>
</html>