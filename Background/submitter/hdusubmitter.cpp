#include "hdusubmitter.h"

HDUSubmitter::HDUSubmitter(QJsonObject submission, QObject *parent) :
    BaseThread(parent),submission(submission)
{
    qDebug()<<"HDU Submitter";
}

void HDUSubmitter::run()
{
    emit updateInfo("Submitter HDU " + submission["originID"].toString() + " " + submission["sid"].toString());
    emit updateStatus("一切正常");

    MyNetwork network;
    upload["id"]=submission["sid"];
    upload["result"]="正在上传代码";
    upload["score"]=0;
    upload["accepted"]=0;
    upload["time"]="0ms";
    upload["memory"]="0KB";
    upload["judgeLog"]=QJsonArray();
    upload["finish"]=0;
    upload["afresh"]=0;
    if(!uploadSubmission(&network,upload))return;

    emit updateStatus("正在登录");
    for(int times=0;;times++)
    {
        if(login(&network))break;
        if(times==3)
        {
            emit errorMessage(QString("登录失败\n" + username + "\n" + html).toUtf8());
            upload["result"]="上传代码失败";
            upload["afresh"]=1;
            uploadSubmission(&network,upload);
            return;
        }
        msleep(1000 * 2);
    }
    qDebug()<<"HDU login";

    emit updateStatus("正在上传代码");
    for(int times=0;;times++)
    {
        if(submit(&network))break;
        if(times==5)
        {
            emit errorMessage(QString("提交代码失败\n" + username + "\n" + html).toUtf8());
            upload["result"]="上传代码失败";
            upload["afresh"]=1;
            uploadSubmission(&network,upload);
            return;
        }
        msleep(1000 * 2);
    }
    qDebug()<<"HDU submit";

    emit updateStatus("正在获取评测结果");
    upload["result"]="正在获取评测结果";
    if(!uploadSubmission(&network,upload))return;
    getResult(&network);
}

bool HDUSubmitter::login(MyNetwork *network)
{
    QByteArray postData;
    postData.append("username="+username);
    postData.append("&userpass="+password);
    postData.append("&login="+QByteArray("Sign In").toPercentEncoding());
    html = network->postByteArray("http://acm.hdu.edu.cn/userloginex.php?action=login",postData);
    return html == "";
}

bool HDUSubmitter::submit(MyNetwork *network)
{
    QString language;
    if(submission["language"].toString() == "C++")
        language = "2";
    else if(submission["language"].toString() == "C")
        language = "3";
    else if(submission["language"].toString() == "Pascal")
        language = "4";
    else return false;

    QByteArray postData;
    postData.append("problemid=" + submission["originID"].toString());
    postData.append("&language=" + language);
    postData.append("&usercode=" + submission["source"].toString().toUtf8().toPercentEncoding());
    postData.append("&check=0");
    html = network->postByteArray("http://acm.hdu.edu.cn/submit.php?action=submit",postData);
    return html == "";
}

void HDUSubmitter::getResult(MyNetwork *network)
{
    upload["result"] = "Pending";
    for(int times=0;;)
    {
        QString html = network->get("http://acm.hdu.edu.cn/status.php?user=" + username);
        QRegExp reg(">(\\d+)</td><td>.*</td><td>(.*)</td><td>.*</td><td>(\\d*)MS</td><td>(\\d*)K</td>");
        QRegExp replace("<.*>");
        replace.setMinimal(true);
        reg.setMinimal(true);
        reg.indexIn(html);
        if(reg.cap(2).replace(replace,"") != "")upload["result"] = reg.cap(2).replace(replace,"");
        if(reg.cap(3) != "")upload["time"] = reg.cap(3) + "ms";
        if(reg.cap(4) != "")upload["memory"] = reg.cap(4) + "KB";
        if(!reg.cap(2).contains("ing"))
        {
            if(reg.cap(2).contains("Accepted"))
            {
                upload["score"]=100;
                upload["accepted"]=1;
            }
            if(reg.cap(2).contains("Compilation Error"))
            {
                html = network->get("http://acm.hdu.edu.cn/viewerror.php?rid=" + reg.cap(1));
                upload["judgeLog"] = "Compile Error:<br/>"+TOOL::regFind(html,"(<pre>.*</pre>)",true);
            }
            upload["finish"]=1;
            if(!uploadSubmission(network,upload))return;
            break;
        }
        if(times == 20)
        {
            emit errorMessage(QString("无法获取提交结果\n" + html).toUtf8());
            upload["result"]="获取评测结果失败";
            upload["afresh"]=1;
            uploadSubmission(network,upload);
            return;
        }
        msleep(1000*2);
    }
}

bool HDUSubmitter::uploadSubmission(MyNetwork *network, QJsonObject upload)
{
    qDebug()<<"HDU upload : "<<upload;
    QByteArray postData = "submission="+QJsonDocument(upload).toJson().toPercentEncoding();
    html = network->postByteArray(uploadSubmissionAddress + TOOL::getToken(token),postData);
    if(html != ""){
        emit errorMessage("\n" + html.toUtf8() + "\n" + QJsonDocument(upload).toJson());
        return false;
    }
    return true;
}
