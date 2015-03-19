<?php
/**
 * Created by PhpStorm.
 * User: 宇
 * Date: 14-11-14
 * Time: 下午3:28
 */

require_once(dirname(__FILE__) . "/mysql_config.php");

$connection = null;

function secondsToString($value)
{
    $value = (int)$value;

    $day = 0;
    $hour = 0;
    $minute = 0;
    $second = 0;

    $second = $value % 60;
    $value = (int)($value / 60);
    $minute = $value % 60;
    $value = (int)($value / 60);
    $hour = $value % 24;
    $value = (int)($value / 24);
    $day = $value;

    $result = "";
    if ($day != 0)
        $result .= $day . "天";
    if ($hour != 0)
        $result .= $hour . "小时";
    if ($minute != 0)
        $result .= $minute . "分钟";
    if ($second != 0)
        $result .= $second . "秒";
    if ($result == "")
        $result = "已结束";

    return $result;
}

function resultVisible($mode)
{
    if ($mode == "ACM") return true;
    return false;
}

function mysql_init()
{
    global $connection;
    global $mysql_server;
    global $mysql_username;
    global $mysql_password;
    global $mysql_database;

    if ($connection == null) {
        $connection = mysql_connect($mysql_server, $mysql_username, $mysql_password) or die("无法连接数据库 " . mysql_error());
        mysql_select_db($mysql_database, $connection) or die("无法启用数据库 " . mysql_error());
    }
}

/*
 * 返回通过的题目集
 * 数组
 * */
function mysql_getAccepted($uid, $cid, $problemSet)
{
    mysql_init();
    global $connection;

    $accepted = array();
    if ($problemSet != "")
        $sql = "SELECT DISTINCT `pid` FROM `submissions` WHERE `accepted`='1' and `uid`='$uid' and ('$cid'='0' or `cid`='$cid') and `pid` in ($problemSet)";
    else return $accepted;
    $result = mysql_query($sql, $connection) or die("数据库出错 " . mysql_error());
    while ($row = mysql_fetch_array($result)) {
        array_push($accepted, $row["pid"]);
    }

    return $accepted;
}

/*
 * 用于试题列表页面
 * 返回一个映射数组
 * currentPage:当前页数
 * totalPage:总页数
 * perProblem:每页题目数量
 * totalProblem:总题目数
 * problems:试题列表，一个映射数组
 *      id:数据库编号
 *      originOJ:原题库名称
 *      originID:原题库编号
 *      title:标题
 *      acceptedCount:通过总次数
 *      submitedCount:提交总次数
 * accepted:通过的题目
 * */
function mysql_getProblemList($uid = -1, $page = 1, $admin = 0)
{
    mysql_init();
    global $connection;
    global $html_perProblem;

    $uid = mysql_real_escape_string($uid, $connection);
    $page = mysql_real_escape_string($page, $connection);

    $ret = array();
    $ret["preProblem"] = $html_perProblem;

    $sql = "SELECT COUNT(*) FROM `problems` WHERE `cid`='0'";
	if($admin != 0)
		$sql = "SELECT COUNT(*) FROM `problems`";
    $result = mysql_query($sql, $connection) or die("数据库出错 " . mysql_error());
    $row = mysql_fetch_array($result) or die("数据库出错");
    $ret["totalProblem"] = $row[0];

    $ret["totalPage"] = max(1, (int)(($ret["totalProblem"] + $ret["preProblem"] - 1) / $ret["preProblem"]));
    $page = max(1, min($ret["totalPage"], $page));
    $ret["currentPage"] = $page;

    $problems = array();
    $problemSet = array();
    $sql = "SELECT `id`,`originOJ`,`originID`,`title`,`acceptedCount`,`submitedCount` FROM `problems` WHERE `cid`='0' LIMIT " . (($ret["currentPage"] - 1) * $ret["preProblem"]) . "," . $ret["preProblem"];
    if($admin != 0)
		$sql = "SELECT `id`,`originOJ`,`originID`,`title`,`acceptedCount`,`submitedCount` FROM `problems` LIMIT " . (($ret["currentPage"] - 1) * $ret["preProblem"]) . "," . $ret["preProblem"];
	$result = mysql_query($sql, $connection) or die("数据库出错 " . mysql_error());
    while ($row = mysql_fetch_array($result)) {
        $item = array();
        $item["id"] = $row["id"];
        $item["originOJ"] = $row["originOJ"];
        $item["originID"] = $row["originID"];
        $item["title"] = $row["title"];
        $item["acceptedCount"] = $row["acceptedCount"];
        $item["submitedCount"] = $row["submitedCount"];

        array_push($problems, $item);
        array_push($problemSet, $row["id"]);
    }
    $ret["problems"] = $problems;
    $ret["accepted"] = mysql_getAccepted($uid, 0, implode(",", $problemSet));

    return $ret;
}

/*
 * 获取单个题目的详细信息
 * 映射数组
 * id:编号
 * title:标题
 * timelimit:时间限制
 * memorylimit:空间限制
 * 64Format:long long输入输出格式
 * description:描述
 * input:输入格式
 * output:输出格式
 * sample:样例
 * datarange:数据范围与约定
 * hint:提示
 * translate:翻译及提示
 * source:来源
 * url:链接
 * statusUrl:提交情况链接
 * originOJ:原OJ
 * originID:原ID
 * acceptedCount:通过次数
 * submitedCount:提交次数
 * canSubmit:是否可提交
 * cid:被使用的比赛ID
 * */
function mysql_getSingleProblem($pid)
{
    mysql_init();
    global $connection;

    $pid = mysql_real_escape_string($pid, $connection);

    $sql = "SELECT * FROM `problems` WHERE `id`='$pid'";
    $result = mysql_query($sql, $connection) or die("数据库出错 " . mysql_error());
    $row = mysql_fetch_array($result) or die("不存在的题目");

    $ret = array();
    $ret["id"] = $row["id"];
    $ret["title"] = $row["title"];
    $ret["timelimit"] = $row["timelimit"];
    $ret["memorylimit"] = $row["memorylimit"];
    $ret["64Format"] = $row["LLFormat"];
    $ret["description"] = $row["description"];
    $ret["input"] = $row["input"];
    $ret["output"] = $row["output"];
    $ret["sample"] = $row["sample"];
    $ret["datarange"] = $row["datarange"];
    $ret["hint"] = $row["hint"];
    $ret["source"] = $row["source"];
    $ret["url"] = $row["url"];
    $ret["translate"] = $row["translate"];
    $ret["statusUrl"] = $row["statusUrl"];
    $ret["originOJ"] = $row["originOJ"];
    $ret["originID"] = $row["originID"];
    $ret["acceptedCount"] = $row["acceptedCount"];
    $ret["submitedCount"] = $row["submitedCount"];
    $ret["canSubmit"] = 1;
    $ret["cid"] = $row["cid"];

    return $ret;
}

/*
 * 用于接口
 * id:编号
 * title:标题
 * */
function mysql_getSearchProblem($originOJ, $originID)
{
    mysql_init();
    global $connection;

    $originOJ = mysql_real_escape_string($originOJ, $connection);
    $originID = mysql_real_escape_string($originID, $connection);

    $sql = "SELECT `id`,`title` FROM `problems` WHERE `originOJ`='$originOJ' and `originID`='$originID' and `cid`='0'";
    $result = mysql_query($sql, $connection) or die("数据库出错 " . mysql_error());
    if (mysql_num_rows($result) == 0) return null;
    $row = mysql_fetch_array($result) or die("数据库出错");
    return $row;
}

/*
 * 用于接口
 * id:编号
 * */
function mysql_getSearchTest($id)
{
    mysql_init();
    global $connection;

    $id = mysql_real_escape_string($id, $connection);

    $sql = "SELECT `finish`,`error`,`result` FROM `tests` WHERE `id`='$id'";
    $result = mysql_query($sql, $connection) or die("数据库出错 " . mysql_error());
    if (mysql_num_rows($result) == 0) return null;
    $row = mysql_fetch_array($result) or die("数据库出错");

    $ret = array();
    $ret["finish"] = $row["finish"];
    if ($row["finish"] == 1) {
        $ret["error"] = $row["error"];
        $ret["result"] = $row["result"];
    }
    return $ret;
}

/*
 * 查看题目是否存在
 * */
function mysql_getProblemExist($id)
{
    mysql_init();
    global $connection;

    $id = mysql_real_escape_string($id, $connection);

    $sql = "SELECT EXISTS(SELECT * FROM `problems` WHERE `id`='$id' and `cid`='0')";
    $result = mysql_query($sql, $connection) or die("数据库出错 " . mysql_error());
    $row = mysql_fetch_array($result) or die("数据库出错");
    if ($row[0] == 1)
        return true;
    return false;
}

/*
 * 获取正在进行比赛
 * 返回数组
 * id:比赛编号
 * title:比赛名称
 * startTime:开始时间
 * duration:持续时间
 * signUp:报名人数
 * */
function mysql_getRunningContests()
{
    mysql_init();
    global $connection;

    $now = date("Y-m-d H:i:s");
    $sql = "SELECT * FROM `contests` WHERE `endTime`>'$now' and `startTime`<'$now'";
    $result = mysql_query($sql, $connection) or die("数据库出错 " . mysql_error());

    $ret = array();
    while ($row = mysql_fetch_array($result)) {
        $item = array();
        $item["id"] = $row["id"];
        $item["title"] = $row["title"];
        $item["startTime"] = $row["startTime"];
        $item["duration"] = $row["duration"];
        $item["signUp"] = $row["signUp"];

        array_push($ret, $item);
    }

    return $ret;
}

/*
 * 获取即将到来的比赛
 * 返回数组
 * id:比赛编号
 * title:比赛名称
 * startTime:开始时间
 * duration:持续时间
 * signUp:报名人数
 * */
function mysql_getPendingContests()
{
    mysql_init();
    global $connection;

    $now = date("Y-m-d H:i:s");
    $sql = "SELECT * FROM `contests` WHERE `startTime`>'$now'";
    $result = mysql_query($sql, $connection) or die("数据库出错 " . mysql_error());

    $ret = array();
    while ($row = mysql_fetch_array($result)) {
        $item = array();
        $item["id"] = $row["id"];
        $item["title"] = $row["title"];
        $item["startTime"] = $row["startTime"];
        $item["duration"] = $row["duration"];
        $item["signUp"] = $row["signUp"];

        array_push($ret, $item);
    }

    return $ret;
}

/*
 * 获取已结束的比赛
 * 返回一个映射数组
 * currentPage:当前页数
 * totalPage:总页数
 * perContest:每页比赛数量
 * totalContest:总比赛数
 * contests:试题列表，一个映射数组
 *      id:比赛编号
 *      title:比赛名称
 *      startTime:开始时间
 *      duration:持续时间
 *      signUp:报名人数
 * */
function mysql_getPastContests($page = 1)
{
    mysql_init();
    global $connection;
    global $html_perContest;

    $page = mysql_real_escape_string($page, $connection);

    $ret = array();
    $now = date("Y-m-d H:i:s");

    $ret["preContest"] = $html_perContest;

    $sql = "SELECT COUNT(*) FROM `contests` WHERE `endTime`<='$now'";
    $result = mysql_query($sql, $connection) or die("数据库出错 " . mysql_error());
    $row = mysql_fetch_array($result) or die("数据库出错");
    $ret["totalContest"] = $row[0];

    $ret["totalPage"] = max(1, (int)(($ret["totalContest"] + $ret["preContest"] - 1) / $ret["preContest"]));
    $page = max(1, min($ret["totalPage"], $page));
    $ret["currentPage"] = $page;

    $limit = (($ret["currentPage"] - 1) * $ret["preContest"]) . "," . $ret["preContest"];
    $sql = "SELECT * FROM `contests` WHERE `endTime`<='$now' ORDER BY `id` DESC LIMIT $limit";
    $result = mysql_query($sql, $connection) or die("数据库出错 " . mysql_error());

    $contests = array();
    while ($row = mysql_fetch_array($result)) {
        $item = array();
        $item["id"] = $row["id"];
        $item["title"] = $row["title"];
        $item["startTime"] = $row["startTime"];
        $item["duration"] = $row["duration"];
        $item["signUp"] = $row["signUp"];

        array_push($contests, $item);
    }
    $ret["contests"] = $contests;

    return $ret;
}

/*
 * 返回一场比赛的详细信息
 * 返回映射数组
 * id:比赛编号
 * title:比赛名称
 * startTime:开始时间
 * endTime:结束时间
 * duration:持续时间
 * signUp:报名人数
 * mode:模式
 * manager:管理员
 * sign:是否已报名
 * */
function mysql_getSingleContest($cid, $uid = -1)
{
    mysql_init();
    global $connection;

    $cid = mysql_real_escape_string($cid, $connection);
    $uid = mysql_real_escape_string($uid, $connection);

    $ret = array();
    $sql = "SELECT `contests`.*,`users`.`username` FROM `contests`,`users` WHERE `contests`.`managerID`=`users`.`id` and `contests`.`id`='$cid'";
    $result = mysql_query($sql, $connection) or die("数据库出错 " . mysql_error());
    $row = mysql_fetch_array($result) or die("不存在的比赛");

    $ret["id"] = $row["id"];
    $ret["title"] = $row["title"];
    $ret["startTime"] = $row["startTime"];
    $ret["endTime"] = $row["endTime"];
    $ret["duration"] = $row["duration"];
    $ret["signUp"] = $row["signUp"];
    $ret["mode"] = $row["mode"];
    $ret["manager"] = $row["username"];

    $sql = "SELECT EXISTS(SELECT * FROM `cuser` WHERE `cid`='$cid' and `uid`='$uid')";
    $result = mysql_query($sql, $connection);
    $row = mysql_fetch_array($result) or die("数据库出错");
    $ret["sign"] = $row[0];

    return $ret;
}

/*
 * 返回一场比赛的题目列表
 * 返回一个映射数组
 * id:比赛编号
 * problems:比赛列表
 *      id:题目编号
 *      number:简称
 *      title:题目标题
 *      acceptedCount:通过次数
 *      submitedCount:提交次数
 * accepted:通过题目列表
 * */
function mysql_getContestProblems($cid, $uid = -1)
{
    mysql_init();
    global $connection;

    $cid = mysql_real_escape_string($cid, $connection);
    $uid = mysql_real_escape_string($uid, $connection);

    $ret = array();
    $ret["id"] = $cid;

    $problems = array();
    $problemSet = array();
    $sql = "SELECT `cproblem`.*,`problems`.`title` FROM `cproblem`,`problems` WHERE `cproblem`.cid='$cid' and `cproblem`.`pid`=`problems`.`id`";
    $result = mysql_query($sql, $connection) or die("数据库出错 " . mysql_error());
    while ($row = mysql_fetch_array($result)) {
        $item = array();
        $item["id"] = $row["pid"];
        $item["number"] = $row["number"];
        $item["title"] = $row["title"];
        $item["acceptedCount"] = $row["acceptedCount"];
        $item["submitedCount"] = $row["submitedCount"];

        array_push($problems, $item);
        array_push($problemSet, $item["id"]);
    }
    $ret["problems"] = $problems;

    $ret["accepted"] = mysql_getAccepted($uid, $cid, implode(",", $problemSet));

    return $ret;
}

/*
 * 返回比赛排名
 * 返回一个映射数组
 * id:比赛编号
 * numbers:比赛题目编号集合
 * rank:比赛排名
 *      username:用户名
 *      totalScore:总分
 *      solveLog:详细情况
 * */
function mysql_getContestRankList($cid, $isOrder)
{
    mysql_init();
    global $connection;

    $cid = mysql_real_escape_string($cid, $connection);

    $ret = array();
    $ret["id"] = $cid;

    $numbers = array();
    $sql = "SELECT `number` FROM `cproblem` WHERE `cid`='$cid'";
    $result = mysql_query($sql, $connection) or die("数据库出错 " . mysql_error());
    while ($row = mysql_fetch_array($result)) {
        array_push($numbers, $row["number"]);
    }
    $ret["numbers"] = $numbers;

    $rank = array();
    if ($isOrder)
        $sql = "SELECT `users`.`username`,`cuser`.`totalScore`,`cuser`.`solveLog` FROM `users`,`cuser` WHERE `cuser`.`cid`='$cid' and `users`.id=`cuser`.`uid` ORDER BY `cuser`.`totalScore` DESC";
    else $sql = "SELECT `users`.`username`,`cuser`.`totalScore`,`cuser`.`solveLog` FROM `users`,`cuser` WHERE `cuser`.`cid`='$cid' and `users`.id=`cuser`.`uid`";
    $result = mysql_query($sql, $connection) or die("数据库出错 " . mysql_error());
    while ($row = mysql_fetch_array($result)) {
        $item = array();
        $item["username"] = $row["username"];
        $item["totalScore"] = $row["totalScore"];
        $item["solveLog"] = $row["solveLog"];

        array_push($rank, $item);
    }
    $ret["rank"] = $rank;

    return $ret;
}

/*
 * 返回比赛排名
 * 返回一个映射数组
 * username:用户名
 * totalScore:总分
 * */
function mysql_getRankList()
{
    mysql_init();
    global $connection;

    $ret = array();

    $sql = "SELECT `username`,`totalScore` FROM `users` ORDER BY `totalScore` DESC";
    $result = mysql_query($sql, $connection) or die("数据库出错 " . mysql_error());
    while ($row = mysql_fetch_array($result)) {
        $item = array();
        $item["username"] = $row["username"];
        $item["totalScore"] = $row["totalScore"];

        array_push($ret, $item);
    }

    return $ret;
}

/*
 * 获取提交状态
 * 返回一个映射数组
 * currentPage:当前页数
 * totalPage:总页数
 * perStatus:每页状态数量
 * totalStatus:总比赛数
 * status:状态列表，一个映射数组
 *      id:提交编号
 *      pid:题目编号
 *      title:题目名称
 *      originOJ:原题库
 *      originID:原题库ID
 *      username:提交用户名
 *      result:提交结果
 *      time:时间
 *      memory:空间
 *      language:语言
 *      length:代码长度
 *      submitDatetime:提交时间
 *      accepted:是否已通过
 *      afresh:是否可以重新评测
 * */
function mysql_getStatusList($page = 1, $cid = 0, $uid = -1)
{
    mysql_init();
    global $connection;
    global $html_perStatus;

    $page = mysql_real_escape_string($page, $connection);
    $cid = mysql_real_escape_string($cid, $connection);
    $uid = mysql_real_escape_string($uid, $connection);

    $ret = array();

    $ret["preStatus"] = $html_perStatus;

    $sql = "SELECT COUNT(*) FROM `submissions`,`problems` WHERE ('$uid'='-1' or `submissions`.`uid`='$uid') and `submissions`.`cid`='$cid' and `submissions`.`pid`=`problems`.`id` and ('$cid'!='0' or `problems`.cid='$cid')";
    $result = mysql_query($sql, $connection) or die("数据库出错 " . mysql_error());
    $row = mysql_fetch_array($result) or die("数据库出错");
    $ret["totalStatus"] = $row[0];

    $ret["totalPage"] = max(1, (int)(($ret["totalStatus"] + $ret["preStatus"] - 1) / $ret["preStatus"]));
    $page = max(1, min($ret["totalPage"], $page));
    $ret["currentPage"] = $page;

    $limit = (($ret["currentPage"] - 1) * $ret["preStatus"]) . "," . $ret["preStatus"];
    $sql = "SELECT `submissions`.*,`problems`.`title`,`problems`.`originOJ`,`problems`.`originID`,`users`.`username` FROM `submissions`,`problems`,`users` WHERE ('$uid'='-1' or `submissions`.`uid`='$uid') and `submissions`.`cid`='$cid' and `problems`.`id`=`submissions`.`pid` and ('$cid'!='0' or `problems`.cid='$cid') and `users`.id=`submissions`.`uid` ORDER BY `submissions`.`id` DESC LIMIT $limit";
    $result = mysql_query($sql, $connection) or die("数据库出错 " . mysql_error());

    $status = array();
    while ($row = mysql_fetch_array($result)) {
        $item = array();
        $item["id"] = $row["id"];
        $item["pid"] = $row["pid"];
        $item["title"] = $row["title"];
        $item["originOJ"] = $row["originOJ"];
        $item["originID"] = $row["originID"];
        $item["username"] = $row["username"];
        $item["result"] = $row["result"];
        $item["time"] = $row["time"];
        $item["memory"] = $row["memory"];
        $item["language"] = $row["language"];
        $item["length"] = $row["length"];
        $item["submitDatetime"] = $row["submitDatetime"];
        $item["accepted"] = $row["accepted"];
        $item["afresh"] = $row["afresh"];

        array_push($status, $item);
    }
    $ret["status"] = $status;

    return $ret;
}

/*
 * 获取单个提交的详细情况
 * 返回一个映射数组
 * id:提交编号
 * pid:题目编号
 * cid:考试编号
 * title:题目名称
 * username:用户名
 * result:结果
 * time:时间
 * memory:内存
 * language:语言
 * length:代码长度
 * submitDatetime:提交时间
 * source:源代码
 * accepted:是否已通过
 * afresh:是否可以重新评测
 * judgeLog:测评详情
 *      文本
 *      json数组
 *          result:结果
 *          time:时间
 *          memory:内存
 *          type:类型,accepted,wrong,le,none
 * */
function mysql_getSingleStatus($sid)
{
    mysql_init();
    global $connection;

    $sid = mysql_real_escape_string($sid, $connection);

    $ret = array();
    $sql = "SELECT `submissions`.*,`problems`.`title`,`users`.`username` FROM `submissions`,`problems`,`users` WHERE `submissions`.`id`='$sid' and `problems`.`id`=`submissions`.`pid` and `users`.id=`submissions`.`uid` and (`problems`.`cid`='0' or `problems`.`cid`=`submissions`.`cid`)";
    $result = mysql_query($sql, $connection) or die("数据库出错 " . mysql_error());
    $row = mysql_fetch_array($result) or die("不存在的提交");
    $ret["id"] = $row["id"];
    $ret["pid"] = $row["pid"];
    $ret["cid"] = $row["cid"];
    $ret["title"] = $row["title"];
    $ret["username"] = $row["username"];
    $ret["result"] = $row["result"];
    $ret["time"] = $row["time"];
    $ret["memory"] = $row["memory"];
    $ret["language"] = $row["language"];
    $ret["length"] = $row["length"];
    $ret["submitDatetime"] = $row["submitDatetime"];
    $ret["source"] = $row["source"];
    $ret["accepted"] = $row["accepted"];
    $ret["judgeLog"] = $row["judgeLog"];
    $ret["afresh"] = $row["afresh"];

    return $ret;
}

/*
 * 获取用户详细信息
 * 返回一个映射数组
 * id:用户编号
 * username:用户名
 * password:密码
 * realname:真实姓名
 * nickname:昵称
 * registerDatetime:注册时间
 * lastLoginDatetime:上次登录时间
 * acceptedCount:通过次数
 * submitedCount:提交次数
 * isAdmin:是否是管理员
 * */
function mysql_getUserInfo($username)
{
    mysql_init();
    global $connection;

    $username = mysql_real_escape_string($username, $connection);

    $ret = array();
    $sql = "SELECT * FROM `users` WHERE `username`='$username'";
    $result = mysql_query($sql, $connection) or die("数据库出错 " . mysql_error());
    $row = mysql_fetch_array($result) or die("不存在的用户");
    $ret["id"] = $row["id"];
    $ret["username"] = $row["username"];
    $ret["password"] = $row["password"];
    $ret["realname"] = $row["realname"];
    $ret["nickname"] = $row["nickname"];
    $ret["registerDatetime"] = $row["registerDatetime"];
    $ret["lastLoginDatetime"] = $row["lastLoginDatetime"];
    $ret["acceptedCount"] = $row["acceptedCount"];
    $ret["submitedCount"] = $row["submitedCount"];
    $ret["isAdmin"] = $row["isAdmin"];

    return $ret;
}

/*
 * 获取用户的密码
 * 返回一个字符串表示密码
 * 如果没有该用户，返回null
 * */
function mysql_getPassword($username)
{
    mysql_init();
    global $connection;

    $username = mysql_real_escape_string($username, $connection);

    $sql = "SELECT `password` FROM `users` WHERE `username`='$username'";
    $result = mysql_query($sql, $connection) or die("数据库出错 " . mysql_error());
    if (mysql_num_rows($result) == 0)
        return null;
    $row = mysql_fetch_array($result) or die("数据库出错");
    return $row["password"];
}

/*
 * 注册一个用户
 * */
function mysql_registerUser($username, $password, $realname, $nickname)
{
    mysql_init();
    global $connection;

    $username = mysql_real_escape_string($username, $connection);
    $password = mysql_real_escape_string($password, $connection);
    $realname = mysql_real_escape_string($realname, $connection);
    $nickname = mysql_real_escape_string($nickname, $connection);

    $now = date("Y-m-d H:i:s");
    $sql = "INSERT INTO `users`(`username`,`password`,`realname`,`nickname`,`registerDatetime`) VALUES('$username','$password','$realname','$nickname','$now')";
    mysql_query($sql, $connection) or die("数据库出错 " . mysql_error());
}

/*
 * 修改信息
 * */
function mysql_modifyUser($id, $password, $realname, $nickname)
{
    mysql_init();
    global $connection;

    $id = mysql_real_escape_string($id, $connection);
    $password = mysql_real_escape_string($password, $connection);
    $realname = mysql_real_escape_string($realname, $connection);
    $nickname = mysql_real_escape_string($nickname, $connection);

    if ($password != null && $password != "")
        $sql = "UPDATE `users` SET `password`='$password',`realname`='$realname',`nickname`='$nickname' WHERE `id`='$id'";
    else $sql = "UPDATE `users` SET `realname`='$realname',`nickname`='$nickname' WHERE `id`='$id'";
    mysql_query($sql, $connection) or die("数据库出错 " . mysql_error());
}

/*
 * 修改最近登录时间
 * */
function mysql_lastLogin($username)
{
    mysql_init();
    global $connection;

    $username = mysql_real_escape_string($username, $connection);

    $now = date("Y-m-d H:i:s");
    $sql = "UPDATE `users` SET `lastLoginDatetime`='$now' WHERE `username`='$username'";
    mysql_query($sql, $connection) or die("数据库出错 " . mysql_error());
}

/*
 * 添加一场比赛
 * */
function mysql_addContest($uid, $contestInfo)
{
    mysql_init();
    global $connection;

    $uid = mysql_real_escape_string($uid, $connection);

    $date = new DateTime($contestInfo["datetime"]);
    $date->modify($contestInfo["duration"] . " seconds");

    $title = mysql_real_escape_string($contestInfo["title"], $connection);
    $startTime = mysql_real_escape_string($contestInfo["datetime"], $connection);
    $endTime = $date->format("Y-m-d H:i:s");
    $duration = mysql_real_escape_string($contestInfo["duration"], $connection);
    $mode = mysql_real_escape_string($contestInfo["mode"], $connection);

    $sql = "INSERT INTO `contests`(`title`,`startTime`,`endTime`,`duration`,`mode`,`managerID`) VALUES('$title','$startTime','$endTime','$duration','$mode','$uid')";
    mysql_query($sql, $connection) or die("数据库出错 " . mysql_error());
    $cid = mysql_insert_id($connection);

    $problems = $contestInfo["problems"];
    for ($x = 0; $x < count($problems); $x++) {
        $pid = mysql_real_escape_string($problems[$x], $connection);
        $number = chr(ord('A') + $x);
        $sql = "INSERT INTO `cproblem`(`pid`,`cid`,`number`) VALUES('$pid','$cid','$number')";
        mysql_query($sql, $connection) or die("数据库出错 " . mysql_error());
    }
    $problems = implode(",", $problems);
    $sql = "UPDATE `problems` SET `cid`='$cid' WHERE `id` in ($problems)";
    mysql_query($sql, $connection) or die("数据库出错 " . mysql_error());
    $time = $date->format("Y-m-d H:i:s");
	$sql = "SET GLOBAL event_scheduler = ON";
	mysql_query($sql, $connection) or die("数据库出错 " . mysql_error());
    $sql = "CREATE EVENT `Recover$cid` ON SCHEDULE AT '$time' ON COMPLETION PRESERVE ENABLE DO\n" .
        "BEGIN\n" .
        "UPDATE `problems` SET `cid`='0' WHERE `id` in ($problems);\n" .
        "UPDATE `users` SET `users`.`totalScore`=(SELECT SUM(`totalScore`) FROM `cuser` WHERE `cuser`.`uid`=`users`.`id`) WHERE EXISTS(SELECT * FROM `cuser` WHERE `cuser`.`uid`=`users`.`id`);\n" .
        "END";
    mysql_query($sql, $connection) or die("数据库出错 " . mysql_error());
}

/*
 * 添加一个任务
 * */
function mysql_addTask($uid, $OJ, $number)
{
    mysql_init();
    global $connection;

    $uid = mysql_real_escape_string($uid, $connection);
    $OJ = mysql_real_escape_string($OJ, $connection);
    $number = mysql_real_escape_string($number, $connection);

    $now = date("Y-m-d H:i:s");

    $sql = "INSERT INTO `tasks`(`uid`,`OJ`,`number`,`buildDatetime`) VALUES('$uid','$OJ','$number','$now')";
    mysql_query($sql, $connection) or die("数据库出错 " . mysql_error());
}

/*
 * 询问是否有未评测的Test
 * 返回ID
 * */
function mysql_testFinish($ip)
{
    mysql_init();
    global $connection;

    $ip = mysql_real_escape_string($ip, $connection);

    $sql = "SELECT EXISTS(SELECT * FROM `tests` WHERE `ip`='$ip' and `finish`='0');";
    $result = mysql_query($sql, $connection) or die("数据库出错 " . mysql_error());
    $row = mysql_fetch_array($result) or die("数据库出错");
    if($row[0] == 1)
        return false;
    return true;
}

/*
 * 添加一个Test
 * 返回ID
 * */
function mysql_addTest($ip, $language, $source, $input)
{
    mysql_init();
    global $connection;

    $ip = mysql_real_escape_string($ip, $connection);
    $language = mysql_real_escape_string($language, $connection);
    $source = mysql_real_escape_string($source, $connection);
    $input = mysql_real_escape_string($input, $connection);
    $now = date("Y-m-d H:i:s");

    $sql = "INSERT INTO `tests`(`ip`,`language`,`source`,`input`,`result`,`datetime`) VALUES('$ip','$language','$source','$input','null','$now')";
    mysql_query($sql, $connection) or die("数据库出错 " . mysql_error());
    return mysql_insert_id($connection);
}

/*
 * 添加一个提交
 * */
function mysql_addSubmission($uid, $cid, $pid, $language, $code)
{
    mysql_init();
    global $connection;

    $uid = mysql_real_escape_string($uid, $connection);
    $cid = mysql_real_escape_string($cid, $connection);
    $pid = mysql_real_escape_string($pid, $connection);
    $language = mysql_real_escape_string($language, $connection);
    $code = mysql_real_escape_string($code, $connection);

    $now = date("Y-m-d H:i:s");
    $length = strlen($code);

    $sql = "INSERT INTO `submissions`(`pid`,`cid`,`uid`,`result`,`language`,`length`,`submitDatetime`,`source`) VALUES('$pid','$cid','$uid','等待','$language','$length','$now','$code')";
    mysql_query($sql, $connection) or die("数据库出错 " . mysql_error());
    $sql = "UPDATE `users` SET `submitedCount`=`submitedCount`+1 WHERE `id`='$uid'";
    mysql_query($sql, $connection) or die("数据库出错 " . mysql_error());
    if ($cid == 0)
        $sql = "UPDATE `problems` SET `submitedCount`=`submitedCount`+1 WHERE `id`='$pid'";
    else $sql = "UPDATE `cproblem` SET `submitedCount`=`submitedCount`+1 WHERE `pid`='$pid' and `cid`='$cid'";
    mysql_query($sql, $connection) or die("数据库出错 " . mysql_error());
}

/*
 * 重新评测一个提交
 * */
function mysql_afreshSubmission($sid)
{
    mysql_init();
    global $connection;

    $sid = mysql_real_escape_string($sid, $connection);

    $sql = "SELECT `afresh` FROM `submissions` WHERE `id`='$sid'";
    $result = mysql_query($sql, $connection) or die("数据库出错 " . mysql_error());
    $row = mysql_fetch_array($result) or die("不存在的提交");
    if ($row["afresh"] != 1)
        die("无法重新测评");

    $sql = "UPDATE `submissions` SET `result`='等待',`finish`='0',`task`='0',`afresh`='0' WHERE `id`='$sid'";
    mysql_query($sql, $connection) or die("数据库出错 " . mysql_error());
}

/*
 * 报名
 * */
function mysql_signUp($uid, $cid)
{
    mysql_init();
    global $connection;

    $uid = mysql_real_escape_string($uid, $connection);
    $cid = mysql_real_escape_string($cid, $connection);

    $sql = "INSERT INTO `cuser`(`cid`,`uid`,`solveLog`) VALUES('$cid','$uid','null')";
    mysql_query($sql, $connection) or die("数据库出错 " . mysql_error());
    $sql = "UPDATE `contests` SET `signUp`=(SELECT COUNT(*) FROM `cuser` WHERE `contests`.`id`=`cuser`.`cid`) WHERE `id`='$cid'";
    mysql_query($sql, $connection) or die("数据库出错 " . mysql_error());
}

/*
 * 取消报名
 * */
function mysql_signDown($uid, $cid)
{
    mysql_init();
    global $connection;

    $uid = mysql_real_escape_string($uid, $connection);
    $cid = mysql_real_escape_string($cid, $connection);

    $sql = "DELETE FROM `cuser` WHERE `cid`='$cid' and `uid`='$uid'";
    mysql_query($sql, $connection) or die("数据库出错 " . mysql_error());
    $sql = "UPDATE `contests` SET `signUp`=(SELECT COUNT(*) FROM `cuser` WHERE `contests`.`id`=`cuser`.`cid`) WHERE `id`='$cid'";
    mysql_query($sql, $connection) or die("数据库出错 " . mysql_error());
}

/*
 * 获取一个未评测的提交
 * sid:
 * originOJ:
 * originID:
 * language:
 * source:
 * */
function mysql_getSubmission()
{
    mysql_init();
    global $connection;

    $sql = "SELECT `submissions`.*,`problems`.`originOJ`,`problems`.`originID` FROM `problems`,`submissions` WHERE `submissions`.`pid`=`problems`.`id` and `task`='0' LIMIT 0,1";
    $result = mysql_query($sql, $connection) or die("数据库出错 " . mysql_error());
    if (mysql_num_rows($result) == 0) return null;
    $row = mysql_fetch_array($result) or die("数据库出错");

    $ret = array();
    $ret["sid"] = $row["id"];
    $ret["originOJ"] = $row["originOJ"];
    $ret["originID"] = $row["originID"];
    $ret["language"] = $row["language"];
    $ret["source"] = $row["source"];

    $id = $ret["sid"];
    $sql = "UPDATE `submissions` SET `task`='1' WHERE `id`='$id'";
    mysql_query($sql, $connection) or die("数据库出错 " . mysql_error());

    return $ret;
}

/*
 * 获取一个未开始的试题任务
 * id:
 * originOJ:
 * originID:
 * */
function mysql_getTask()
{
    mysql_init();
    global $connection;

    $sql = "SELECT `id`,`OJ`,`number` FROM `tasks` WHERE `task`='0' LIMIT 0,1";
    $result = mysql_query($sql, $connection) or die("数据库出错 " . mysql_error());
    if (mysql_num_rows($result) == 0) return null;
    $row = mysql_fetch_array($result) or die("数据库出错");

    $ret = array();
    $ret["id"] = $row["id"];
    $ret["originOJ"] = $row["OJ"];
    $ret["originID"] = $row["number"];

    $id = $ret["id"];
    $sql = "UPDATE `tasks` SET `task`='1' WHERE `id`='$id'";
    mysql_query($sql, $connection) or die("数据库出错 " . mysql_error());

    return $ret;
}

/*
 * 获取一个未完成的测试任务
 * id:
 * language:
 * source:
 * input:
 * */
function mysql_getTest()
{
    mysql_init();
    global $connection;

    $sql = "SELECT `id`,`language`,`source`,`input` FROM `tests` WHERE `task`='0' LIMIT 0,1";
    $result = mysql_query($sql, $connection) or die("数据库出错 " . mysql_error());
    if (mysql_num_rows($result) == 0) return null;
    $row = mysql_fetch_array($result) or die("数据库出错");

    $ret = array();
    $ret["id"] = $row["id"];
    $ret["language"] = $row["language"];
    $ret["source"] = $row["source"];
    $ret["input"] = $row["input"];

    $id = $ret["id"];
    $sql = "UPDATE `tests` SET `task`='1' WHERE `id`='$id'";
    mysql_query($sql, $connection) or die("数据库出错 " . mysql_error());

    return $ret;
}

/*
 * 更新提交
 * 输入：
 * id
 * result
 * score
 * accepted
 * time
 * memory
 * judgeLog
 *      文本
 *      json数组
 *          result:结果
 *          time:时间
 *          memory:内存
 *          type:类型,accepted,wrong,le,none
 *      或Compile Error
 * finish
 * */
function mysql_updateSubmission($submission)
{
    mysql_init();
    global $connection;

    $id = mysql_real_escape_string($submission["id"], $connection);
    $result = mysql_real_escape_string($submission["result"], $connection);
    $score = mysql_real_escape_string($submission["score"], $connection);
    $accepted = mysql_real_escape_string($submission["accepted"], $connection);
    $time = mysql_real_escape_string($submission["time"], $connection);
    $memory = mysql_real_escape_string($submission["memory"], $connection);
    if (gettype($submission["judgeLog"]) != gettype(array()) && strpos($submission["judgeLog"], "Compile Error") == 0)
        $judgeLog = mysql_real_escape_string($submission["judgeLog"], $connection);
    else $judgeLog = mysql_real_escape_string(json_encode($submission["judgeLog"]), $connection);
    $finish = mysql_real_escape_string($submission["finish"], $connection);
    $afresh = mysql_real_escape_string($submission["afresh"], $connection);

    $sql = "UPDATE `submissions` SET `result`='$result',`score`='$score',`accepted`='$accepted',`time`='$time',`memory`='$memory',`judgeLog`='$judgeLog',`finish`='$finish',`afresh`='$afresh' WHERE `id`='$id'";
    mysql_query($sql, $connection) or die("数据库出错 " . mysql_error());
    if ($finish == 1) {
        $sql = "SELECT `pid`,`cid`,`uid` FROM `submissions` WHERE `id`='$id'";
        $result = mysql_query($sql, $connection) or die("数据库出错 " . mysql_error());
        $row = mysql_fetch_array($result) or die("不存在的记录");
        $pid = $row["pid"];
        $cid = $row["cid"];
        $uid = $row["uid"];

        if ($cid == 0) {
            if ($accepted == 1) {
                $sql = "UPDATE `users` SET `acceptedCount`=`acceptedCount`+1 WHERE `id`='$uid'";
                mysql_query($sql, $connection) or die("数据库出错 " . mysql_error());
                $sql = "UPDATE `problems` SET `acceptedCount`=`acceptedCount`+1 WHERE `id`='$pid'";
                mysql_query($sql, $connection) or die("数据库出错 " . mysql_error());
            }
        } else {
            if ($accepted == 1) {
                $sql = "UPDATE `cproblem` SET `acceptedCount`=`acceptedCount`+1 WHERE `pid`='$pid' and `cid`='$cid'";
                mysql_query($sql, $connection) or die("数据库出错 " . mysql_error());
            }
            $sql = "SELECT MAX(`submissions`.`score`),`cproblem`.`number` FROM `submissions`,`cproblem` WHERE `submissions`.`pid`=`cproblem`.`pid` and `submissions`.`cid`='$cid' and `submissions`.`uid`='$uid' GROUP BY `submissions`.`pid`";
            $result = mysql_query($sql, $connection) or die("数据库出错 " . mysql_error());
            $totalScore = 0;
            $solveLog = array();
            while ($row = mysql_fetch_array($result)) {
                $totalScore += $row[0];
                $solveLog[$row["number"]] = $row[0];
            }
            $solveLog = json_encode($solveLog);
            $sql = "UPDATE `cuser` SET `totalScore`='$totalScore',`solveLog`='$solveLog' WHERE `cid`='$cid' and `uid`='$uid'";
            mysql_query($sql, $connection) or die("数据库出错 " . mysql_error());
        }
    }
}

/*
 * 更新task
 * 输入:
 * title
 * description
 * input
 * output
 * sample
 * datarange
 * hint
 * timelimit
 * memorylimit
 * url
 * originOJ
 * originID
 * source
 * LLFormat
 * */
function mysql_updateTask($task)
{
    mysql_init();
    global $connection;

    $title = mysql_real_escape_string($task["title"], $connection);
    $description = mysql_real_escape_string($task["description"], $connection);
    $input = mysql_real_escape_string($task["input"], $connection);
    $output = mysql_real_escape_string($task["output"], $connection);
    $sample = mysql_real_escape_string($task["sample"], $connection);
    $datarange = mysql_real_escape_string($task["datarange"], $connection);
    $hint = mysql_real_escape_string($task["hint"], $connection);
    $timelimit = mysql_real_escape_string($task["timelimit"], $connection);
    $memorylimit = mysql_real_escape_string($task["memorylimit"], $connection);
    $url = mysql_real_escape_string($task["url"], $connection);
    $statusUrl = mysql_real_escape_string($task["statusUrl"], $connection);
    $originOJ = mysql_real_escape_string($task["originOJ"], $connection);
    $originID = mysql_real_escape_string($task["originID"], $connection);
    $source = mysql_real_escape_string($task["source"], $connection);
    $LLFormat = mysql_real_escape_string($task["LLFormat"], $connection);

    $sql = "SELECT `id` FROM `problems` WHERE `originOJ`='$originOJ' and `originID`='$originID'";
    $result = mysql_query($sql, $connection) or die("数据库错误 " . mysql_error());
    if (mysql_num_rows($result) == 0) {
        $sql = "INSERT INTO `problems`(`originOJ`,`originID`) VALUES('$originOJ','$originID')";
        mysql_query($sql, $connection) or die("数据库错误 " . mysql_error());
        $pid = mysql_insert_id($connection);
    } else {
        $row = mysql_fetch_array($result) or die("数据库错误 " . mysql_error());
        $pid = $row["id"];
    }
    $sql = "UPDATE `problems` SET `title`='$title',`description`='$description',`input`='$input',`output`='$output',`sample`='$sample',`datarange`='$datarange',`hint`='$hint',`timelimit`='$timelimit',`memorylimit`='$memorylimit',`url`='$url',`statusUrl`='$statusUrl',`source`='$source',`LLFormat`='$LLFormat' WHERE `id`='$pid'";
    mysql_query($sql, $connection) or die("数据库错误 " . mysql_error());
}

/*
 * 更新test
 * 输入:
 * id
 * error
 * result
 * */
function mysql_updateTest($test)
{
    mysql_init();
    global $connection;

    $id = mysql_real_escape_string($test["id"], $connection);
    $error = mysql_real_escape_string($test["error"], $connection);
    $result = mysql_real_escape_string($test["result"], $connection);


    $sql = "UPDATE `tests` SET `error`='$error',`result`='$result',`finish`='1' WHERE `id`='$id'";
    mysql_query($sql, $connection) or die("数据库错误 " . mysql_error());
}

function mysql_modifyProblem($pid, $description, $input, $output, $sample, $datarange, $hint, $translate)
{
    mysql_init();
    global $connection;

    $pid = mysql_real_escape_string($pid, $connection);
    $description = mysql_real_escape_string($description, $connection);
    $input = mysql_real_escape_string($input, $connection);
    $output = mysql_real_escape_string($output, $connection);
    $sample = mysql_real_escape_string($sample, $connection);
    $datarange = mysql_real_escape_string($datarange, $connection);
    $hint = mysql_real_escape_string($hint, $connection);
    $translate = mysql_real_escape_string($translate, $connection);

    $sql = "UPDATE `problems` SET `description`='$description',`input`='$input',`output`='$output',`sample`='$sample',`datarange`='$datarange',`hint`='$hint',`translate`='$translate' WHERE `id`='$pid'";
    mysql_query($sql, $connection) or die("数据库错误 " . mysql_error());
}

?>