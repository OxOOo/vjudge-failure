#include "pojsubmitter.h"

POJSubmitter::POJSubmitter(QJsonObject submission, QObject *parent) :
    BaseThread(parent),submission(submission)
{
    qDebug()<<"POJ Submitter";
}

void POJSubmitter::run()
{
    emit updateInfo("Submitter POJ " + submission["originID"].toString() + " " + submission["sid"].toString());
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
    qDebug()<<"POJ login";

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
    qDebug()<<"POJ submit";

    emit updateStatus("正在获取评测结果");
    upload["result"]="正在获取评测结果";
    if(!uploadSubmission(&network,upload))return;
    getResult(&network);
}

bool POJSubmitter::login(MyNetwork *network)
{
    QByteArray postData;
    postData.append("user_id1="+username);
    postData.append("&password1="+password);
    postData.append("&B1=login");
    postData.append("&url="+QByteArray("/").toPercentEncoding());
    network->postByteArray("http://poj.org/login",postData);
    html = network->get("http://poj.org/");
    return html.contains("<a href=userstatus?user_id="+username+" target=_parent>");
}

bool POJSubmitter::submit(MyNetwork *network)
{
    QString language;
    if(submission["language"].toString() == "C++")
        language = "4";
    else if(submission["language"].toString() == "C")
        language = "5";
    else if(submission["language"].toString() == "Pascal")
        language = "3";
    else return false;

    QByteArray postData;
    postData.append("problem_id=" + submission["originID"].toString());
    postData.append("&language=" + language);
    postData.append("&source=" + submission["source"].toString().toUtf8().toPercentEncoding());
    postData.append("&submit=Submit");
    html = network->postByteArray("http://poj.org/submit",postData);
    return html == "";
}

void POJSubmitter::getResult(MyNetwork *network)
{
    upload["result"] = "Pending";
    for(int times=0;;)
    {
        QString html = network->get("http://poj.org/status?user_id="+username);
        QRegExp reg("<td>(\\d+)</td>.*<font.*>(.*)</font>.*<td>(.*)</td><td>(.*)</td>");
        reg.setMinimal(true);
        reg.indexIn(html);
        if(reg.cap(2) != "")upload["result"] = reg.cap(2);
        if(reg.cap(4) != "")upload["time"] = reg.cap(4);
        if(reg.cap(3) != "")upload["memory"] = reg.cap(3);
        if(!reg.cap(2).contains("ing"))
        {
            if(reg.cap(2).contains("Accepted"))
            {
                upload["score"]=100;
                upload["accepted"]=1;
            }
            if(reg.cap(2).contains("Compile Error"))
            {
                html = network->get("http://poj.org/showcompileinfo?solution_id=" + reg.cap(1));
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

bool POJSubmitter::uploadSubmission(MyNetwork *network, QJsonObject upload)
{
    qDebug()<<"POJ upload : "<<upload;
    QByteArray postData = "submission="+QJsonDocument(upload).toJson().toPercentEncoding();
    html = network->postByteArray(uploadSubmissionAddress + TOOL::getToken(token),postData);
    if(html != ""){
        emit errorMessage("\n" + html.toUtf8() + "\n" + QJsonDocument(upload).toJson());
        return false;
    }
    return true;
}
