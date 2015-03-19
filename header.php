<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 14-10-28
 * Time: 下午3:30
 */
?>
<div class="box">
    <h1 style="text-align: center">&nbsp; 虚拟在线评测系统 &nbsp;<sub>南山内部培训专用</sub></h1>
</div>
<div class="menu box">
    <ul>
        <li>
            <a href="./">主页</a>
        </li>
        <li>
            <a href="./problems.php">题库</a>
        </li>
        <li>
            <a href="./contests.php">比赛</a>
        </li>
        <li>
            <a href="./status.php">提交状态</a>
        </li>
        <li>
            <a href="./rank.php">排名</a>
        </li>
        <li>
            <a href="./faq.php">帮助</a>
        </li>
        <li>
            <a href="./ide.php">云IDE</a>
        </li>
    </ul>
    <ul style="float: right">
        <?php
        if (isset($_SESSION["user"])) {
            ?>
            <li>
                <a href="./user.php"><?php echo $_SESSION["user"]["username"]; ?></a>
            </li>
            <li>
                <a href="./logout.php">登出</a>
            </li>
            <?php
            if ($_SESSION["user"]["isAdmin"] == 1) {
                if (isset($_SESSION["admin"]) && $_SESSION["admin"] == 1) {
                    ?>
                    <li>
                        <a href="./action.php?action=admin">退出管理者模式</a>
                    </li>
                <?php
                } else {
                    ?>
                    <li>
                        <a href="./action.php?action=admin">进入管理者模式</a>
                    </li>
                <?php
                }
            }
        } else {
            ?>
            <li>
                <a href="./login.php">登录</a>
            </li>
            <li>
                <a href="./register.php">注册</a>
            </li>
        <?php
        }
        ?>
    </ul>
</div>