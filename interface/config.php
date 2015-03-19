<?php
/**
 * Created by PhpStorm.
 * User: 宇
 * Date: 14-11-17
 * Time: 上午8:12
 */

$token = "qazxswedcvfrtgbnhy";

function checkToken($key, $result)
{
    global $token;

    if ($key != date("YmdHi") && $key != date("YmdHi", time() + 60) && $key != date("YmdHi", time() - 60)) return false;
    if ($result != md5(md5($key) . $token)) return false;
    return true;
}

function checkID($id)
{
    $reg = "/^\\d+$/";
    return preg_match($reg, $id);
}

function checkResult($result)
{
    return true;
}

function checkScore($score)
{
    $reg = "/^\\d+$/";
    return preg_match($reg, $score);
}

function checkAccepted($accepted)
{
    return $accepted == 0 || $accepted == 1;
}

function checkTime($time)
{
    return true;
}

function checkMemory($memory)
{
    return true;
}

function checkType($type)
{
    return $type == "accepted" || $type=="wrong" || $type == "le" || $type == "none";
}

function checkJudgeLog($judgeLog)
{
    if($judgeLog == null)return true;
    if(gettype($judgeLog) == gettype("Compile Error") && strpos($judgeLog,"Compile Error") == 0)return true;
    if (gettype($judgeLog) != gettype(array())) return false;
    for ($x = 0; $x < count($judgeLog); $x++) {
        if (!isset($judgeLog[$x]["result"]) || !checkResult($judgeLog[$x]["result"])) return false;
        if (!isset($judgeLog[$x]["time"]) || !checkTime($judgeLog[$x]["time"])) return false;
        if (!isset($judgeLog[$x]["memory"]) || !checkMemory($judgeLog[$x]["memory"])) return false;
        if (!isset($judgeLog[$x]["type"]) || !checkType($judgeLog[$x]["type"])) return false;
    }
    return true;
}

function checkFinish($finish)
{
    return $finish == 0 || $finish == 1;
}

function checkAfresh($afresh)
{
    return $afresh == 0 || $afresh == 1;
}

function checkTitle($title)
{
    return true;
}

function checkDescription($description)
{
    return true;
}

function checkInput($input)
{
    return true;
}

function checkOutput($output)
{
    return true;
}

function checkSample($sample)
{
    return true;
}

function checkDatarange($datarange)
{
    return true;
}

function checkHint($hint)
{
    return true;
}

function checkTimelimit($timelimit)
{
    return true;
}

function checkMemorylimit($memorylimit)
{
    return true;
}

function checkUrl($url)
{
    return true;
}

function checkOrigin($originOJ, $originID)
{
    if ($originOJ == 'THU') { //清澄
        $reg = '/^[A-Z]\\d+$/';
        return preg_match($reg, $originID);
    } else if ($originOJ == 'CF') {
        $reg = '/^\\d+[A-Z]$/';
        return preg_match($reg, $originID);
    } else if ($originOJ == 'POJ' || $originOJ == 'HDU' || $originOJ == 'BZOJ') {
        $reg = '/^\\d+$/';
        return preg_match($reg, $originID);
    }
    return false;
}

function checkSource($source)
{
    return true;
}

function checkLLFormat($LLFormat)
{
    return true;
}

function checkTranslate($translate)
{
    return true;
}

function checkError($error)
{
    return $error == 0 || $error == 1;
}

function checkSubmission($submission)
{
    if ($submission == null) return false;
    if (!isset($submission["id"]) || !checkID($submission["id"])) return false;
    if (!isset($submission["result"]) || !checkResult($submission["result"])) return false;
    if (!isset($submission["score"]) || !checkScore($submission["score"])) return false;
    if (!isset($submission["accepted"]) || !checkAccepted($submission["accepted"])) return false;
    if (!isset($submission["time"]) || !checkTime($submission["time"])) return false;
    if (!isset($submission["memory"]) || !checkMemory($submission["memory"])) return false;
    if (!isset($submission["judgeLog"]) || !checkJudgeLog($submission["judgeLog"])) return false;
    if (!isset($submission["finish"]) || !checkFinish($submission["finish"])) return false;
    if (!isset($submission["afresh"]) || !checkAfresh($submission["afresh"])) return false;
    return true;
}

function checkTask($task)
{
    if ($task == null) return false;
    if (!isset($task["title"]) || !checkTitle($task["title"])) return false;
    if (!isset($task["description"]) || !checkDescription($task["description"])) return false;
    if (!isset($task["input"]) || !checkInput($task["input"])) return false;
    if (!isset($task["output"]) || !checkOutput($task["output"])) return false;
    if (!isset($task["sample"]) || !checkSample($task["sample"])) return false;
    if (!isset($task["datarange"]) || !checkDatarange($task["datarange"])) return false;
    if (!isset($task["hint"]) || !checkHint($task["hint"])) return false;
    if (!isset($task["timelimit"]) || !checkTimelimit($task["timelimit"])) return false;
    if (!isset($task["memorylimit"]) || !checkMemorylimit($task["memorylimit"])) return false;
    if (!isset($task["url"]) || !checkUrl($task["url"])) return false;
    if (!isset($task["statusUrl"]) || !checkUrl($task["statusUrl"])) return false;
    if (!isset($task["originOJ"]) || !isset($task["originID"]) || !checkOrigin($task["originOJ"], $task["originID"])) return false;
    if (!isset($task["source"]) || !checkSource($task["source"])) return false;
    if (!isset($task["LLFormat"]) || !checkLLFormat($task["LLFormat"])) return false;
    return true;
}

function checkTest($test)
{
    if ($test == null) return false;
    if (!isset($test["id"]) || !checkID($test["id"])) return false;
    if (!isset($test["error"]) || !checkError($test["error"])) return false;
    if (!isset($test["result"]) || !checkResult($test["result"])) return false;
    return true;
}

?>