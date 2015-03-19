/**
 * Created by 宇 on 14-11-13.
 */
function checkUsername(username) {
    var reg = new RegExp('^\\w{3,20}$');
    if (reg.test(username))
        return null;
    return '用户名非法，请输入3-20个大写字母，小写字母，数字或下划线';
}

function checkPassword(password) {
    var reg = new RegExp('^\\w{6,20}$');
    if (reg.test(password))
        return null;
    return '密码非法，请输入6-20个大写字母，小写字母，数字或下划线';
}

function checkRealname(realname) {
    var reg = new RegExp('^.{1,20}$');
    if (reg.test(realname))
        return null;
    return '真实姓名非法，请输入1-20个字符';
}

function checkNickname(nickname) {
    var reg = new RegExp('^.{1,20}$');
    if (reg.test(nickname))
        return null;
    return '昵称非法，请输入1-20个字符';
}

function checkTitle(title) {
    var reg = new RegExp('^.{1,20}$');
    if (reg.test(title))
        return null;
    return '名称非法，请输入1-20个字符';
}

function checkNumber(oj, number) {
    if (oj == 'THU') {//清澄
        var reg = new RegExp('^[A-Z]\\d+$');
        if (reg.test(number) == false)
            return '编号格式不正确，清澄的编号为大写字母+数字组成，例如：D9208';
    } else if (oj == 'CF') {
        var reg = new RegExp('^\\d+[A-Z]$');
        if (reg.test(number) == false)
            return '编号格式不正确，CF的编号为数字+大写字母组成，例如：486E';
    } else if (oj == 'POJ') {
        var reg = new RegExp('^\\d+$');
        if (reg.test(number) == false)
            return '编号格式不正确，POJ的编号为数字组成，例如：1039';
    } else if (oj == 'HDU') {
        var reg = new RegExp('^\\d+$');
        if (reg.test(number) == false)
            return '编号格式不正确，HDU的编号为数字组成，例如：1039';
    } else if (oj == 'BZOJ') {
        var reg = new RegExp('^\\d+$');
        if (reg.test(number) == false)
            return '编号格式不正确，BZOJ的编号为数字组成，例如：1039';
    }
    return null;
}
