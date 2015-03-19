<?php
/**
 * Created by PhpStorm.
 * User: 宇
 * Date: 14-11-16
 * Time: 下午5:14
 */

/**
 * Created by 宇 on 14-11-13.
 */
function checkUsername($username)
{
    $reg = '/^\\w{3,20}$/';
    return preg_match($reg, $username);
}

function checkPassword($password)
{
    $reg = '/^\\w{32}$/';
    return preg_match($reg, $password);
}

function checkRealname($realname)
{
    $reg = '/^.{1,40}$/';
    return preg_match($reg, $realname);
}

function checkNickname($nickname)
{
    $reg = '/^.{1,40}$/';
    return preg_match($reg, $nickname);
}

function checkTitle($title)
{
    $reg = '/^.{1,20}$/';
    return preg_match($reg, $title);
}

function checkMode($mode)
{
    return $mode == "OI" || $mode == "ACM";
}

function checkNumber($oj, $number)
{
    if ($oj == 'THU') { //清澄
        $reg = '/^[A-Z]\\d+$/';
        return preg_match($reg, $number);
    } else if ($oj == 'CF') {
        $reg = '/^\\d+[A-Z]$/';
        return preg_match($reg, $number);
    } else if ($oj == 'POJ' || $oj == 'HDU' || $oj == 'BZOJ') {
        $reg = '/^\\d+$/';
        return preg_match($reg, $number);
    }
    return false;
}

function checkID($id)
{
    $reg = '/^\\d+$/';
    return preg_match($reg, $id);
}

function checkLanguage($language)
{
    return $language == "C++" || $language == "C" || $language == "Pascal";
}

function checkCode($code)
{
    return 0 < strlen($code) && strlen($code) < 65535;
}

function checkContestInfo($contestInfo)
{
    if ($contestInfo == null) return false;
    if (!isset($contestInfo["title"]) || !checkTitle($contestInfo["title"])) return false;
    if (!isset($contestInfo["mode"]) || !checkMode($contestInfo["mode"])) return false;
    if (!isset($contestInfo["datetime"]) || strtotime($contestInfo["datetime"]) == false || strtotime($contestInfo["datetime"]) <= time()) return false;
    if (!isset($contestInfo["duration"]) || $contestInfo["duration"] <= 0 || $contestInfo["duration"] > 24 * 60 * 60) return false;
    if (!isset($contestInfo["problems"]) || gettype($contestInfo["problems"]) != gettype(array())) return false;
    $problems = $contestInfo["problems"];
    if (count($problems) == 0 || count($problems) > 20) return false;
    for ($x = 0; $x < count($problems); $x++)
        for ($y = $x + 1; $y < count($problems); $y++)
            if ($problems[$x] == $problems[$y])
                return false;
    for ($x = 0; $x < count($problems); $x++)
        if (!checkID($problems[$x]) || !mysql_getProblemExist($problems[$x]))
            return false;
    return true;
}

?>