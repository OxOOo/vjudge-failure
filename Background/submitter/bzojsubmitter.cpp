#include "bzojsubmitter.h"

BZOJSubmitter::BZOJSubmitter(QJsonObject submission, QObject *parent) :
    BaseThread(parent),submission(submission)
{
    qDebug()<<"BZOJ Submitter";
}

void BZOJSubmitter::run()
{
    emit updateInfo("Submitter BZOJ " + submission["originID"].toString() + " " + submission["sid"].toString());
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
        if(times==5)
        {
            emit errorMessage(QString("登录失败\n" + username + "\n" + html).toUtf8());
            upload["result"]="上传代码失败";
            upload["afresh"]=1;
            uploadSubmission(&network,upload);
            return;
        }
        msleep(1000 * 2);
    }
    qDebug()<<"BZOJ login";

    emit updateStatus("正在上传代码");
    for(int times=0;;times++)
    {
        if(submit(&network))break;
        if(times==10)
        {
            emit errorMessage(QString("提交代码失败\n" + username + "\n" + html).toUtf8());
            upload["result"]="上传代码失败";
            upload["afresh"]=1;
            uploadSubmission(&network,upload);
            return;
        }
        msleep(1000 * 2);
    }
    qDebug()<<"BZOJ submit";

    emit updateStatus("正在获取评测结果");
    upload["result"]="正在获取评测结果";
    if(!uploadSubmission(&network,upload))return;
    getResult(&network);
}

bool BZOJSubmitter::login(MyNetwork *network)
{
    QByteArray postData;
    postData.append("user_id="+username);
    postData.append("&password="+password);
    postData.append("&submit=Submit");
    html = network->postByteArray("http://www.lydsy.com/JudgeOnline/login.php",postData);
    return html.contains("history.go(-2)");
}

bool BZOJSubmitter::submit(MyNetwork *network)
{
    QString language;
    if(submission["language"].toString() == "C++")
        language = "1";
    else if(submission["language"].toString() == "C")
        language = "0";
    else if(submission["language"].toString() == "Pascal")
        language = "2";
    else return false;

    QByteArray postData;
    postData.append("id=" + submission["originID"].toString());
    postData.append("&language=" + language);
    postData.append("&source=" + submission["source"].toString().toUtf8().toPercentEncoding());
    html = network->postByteArray("http://www.lydsy.com/JudgeOnline/submit.php",postData);
    return html == "";
}

void BZOJSubmitter::getResult(MyNetwork *network)
{
    upload["result"] = "Pending";
    for(int times=0;;)
    {
        QString html = network->get("http://www.lydsy.com/JudgeOnline/status.php?user_id="+username);
        QRegExp reg("class='evenrow'><td>(\\d+).*<font.*>(.*)</font>.*<td>(\\d*).*<td>(\\d*).*<td>");
        reg.setMinimal(true);
        reg.indexIn(html);
        if(reg.cap(2)!= "")upload["result"] = reg.cap(2);
        if(reg.cap(4)!="")upload["time"] = reg.cap(4) + "ms";
        if(reg.cap(3)!= "")upload["memory"] = reg.cap(3) + "KB";
        if(!reg.cap(2).contains("ing"))
        {
            if(reg.cap(2).contains("Accepted"))
            {
                upload["score"]=100;
                upload["accepted"]=1;
            }
            if(reg.cap(2).contains("Compile_Error"))
            {
                html = network->get("http://www.lydsy.com/JudgeOnline/ceinfo.php?sid=" + reg.cap(1));
                upload["judgeLog"] = "Compile Error:<br/>"+TOOL::regFind(html,"<title>Compile Error Info</title>\\s*(<pre>.*</pre>)",true);
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

bool BZOJSubmitter::uploadSubmission(MyNetwork *network, QJsonObject upload)
{
    qDebug()<<"BZOJ upload : "<<upload;
    QByteArray postData = "submission="+QJsonDocument(upload).toJson().toPercentEncoding();
    html = network->postByteArray(uploadSubmissionAddress + TOOL::getToken(token),postData);
    if(html != ""){
        emit errorMessage("\n" + html.toUtf8() + "\n" + QJsonDocument(upload).toJson());
        return false;
    }
    return true;
}
