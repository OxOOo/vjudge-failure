<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 14-10-28
 * Time: 下午4:30
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
    <?php include "./header.php"; ?>
    <div class="box">
        <div class="frame">
            <article>
                <header>
                    <h1 style="font-size: 30pt">常见问题及答案</h1>
                </header>
                <section>
                    <header>
                        <h4>1.什么是虚拟在线评测系统</h4>
                    </header>
                    <p>虚拟在线评测系统本身不会评测你所提交的代码，而是转交给其他题库代为评测。</p>
                </section>
                <section>
                    <header>
                        <h4>2.nsoi是啥呢？nsoier是啥呢？</h4>
                    </header>
                    <p>nsoi是指南山信息学奥赛,nsoier是指南山信息学奥赛生。</p>
                </section>
                <section>
                    <header>
                        <h4>3.赛制说明</h4>
                    </header>
                    <p>OI：以分数排名 不能实时知道评测结果 只能看自己的代码。</p>
                    <p>ACM：以分数排名 能实时知道评测结果 只能看自己的代码。</p>
                </section>
                <section>
                    <header>
                        <h4>4.支持的题库情况</h4>
                    </header>
                    <p>现目前本题库仅支持CodeForces和清澄两个题库，主要是为了今后考试方便。</p>
                    <p>目前支持的语言有：C++,C,Pascal。</p>
                </section>
                <section>
                    <header>
                        <h4>5.清澄运行环境</h4>
                    </header>
                    <p>Windows Server 2003</p>
                    <p>C++：MinGW g++ 4.7.2 ：g++ &lt;源文件名&gt; -O2 -Wl,--stack=268435456 -DONLINE_JUDGE -DTSINSEN</p>
                    <p>C：MinGW gcc 4.7.2 ： gcc &lt;源文件名&gt; -O2 -Wl,--stack=268435456 -DONLINE_JUDGE -DTSINSEN</p>
                    <p>Pascal：Free Pascal Compiler version 2.6.2 [2013/02/12] for i386 ： fpc &lt;源文件名&gt; -O2 -dONLINE_JUDGE -dTSINSEN</p>
                </section>
                <section>
                    <header>
                        <h4>6.CodeForces运行环境</h4>
                    </header>
                    <p>Windows</p>
                    <p>C++：GNU C++ 4.7</p>
                    <p>C：GNU C 4</p>
                    <p>Pascal：Free Pascal 2</p>
                </section>
                <section>
                    <header>
                        <h4>7.怎么添加题目</h4>
                    </header>
                    <p>只有管理员才能添加题目。</p>
                </section>
                <section>
                    <header>
                        <h4>8.怎么获得管理员权限</h4>
                    </header>
                    <p>请与管理员联系。</p>
                </section>
                <section>
                    <header>
                        <h4>9.怎么感觉有些页面怪怪的</h4>
                    </header>
                    <p>> <...可能是出BUG了，请与管理员联系。</p>
                </section>
            </article>
        </div>
    </div>
    <?php include "./footer.php"; ?>
</div>
</body>
</html>