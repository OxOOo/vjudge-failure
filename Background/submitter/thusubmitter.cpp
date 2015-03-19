#include "thusubmitter.h"
#include <QDebug>
#include <QJsonArray>

THUSubmitter::THUSubmitter(QJsonObject submission,QObject *parent) :
    BaseThread(parent),submission(submission)
{
    qDebug()<<"THU Submitter";
}

void THUSubmitter::run()
{
    emit updateInfo("Submitter THU " + submission["originID"].toString() + " " + submission["sid"].toString());
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
        if(times == 5)
        {
            emit errorMessage(QString("登陆失败\n" + username + "\n" + html).toUtf8());
            upload["result"]="上传代码失败";
            upload["afresh"]=1;
            uploadSubmission(&network,upload);
            return;
        }
        msleep(1000 * 2);
    }
    qDebug()<<"THU login";

    emit updateStatus("正在上传代码");
    for(int times=0;;times++)
    {
        if(submit(&network))break;
        if(times == 20)
        {
            emit errorMessage(QString("提交代码失败\n" + html).toUtf8());
            upload["result"]="上传代码失败";
            upload["afresh"]=1;
            uploadSubmission(&network,upload);
            return;
        }
        msleep(1000 * 10);
    }
    qDebug()<<"THU submit";

    emit updateStatus("正在获取评测结果");
    upload["result"]="正在获取评测结果";
    if(!uploadSubmission(&network,upload))return;
    getResult(&network);
}

bool THUSubmitter::login(MyNetwork *network)
{
    QByteArray loginData = QString("<xml>\n<pusername>"+TOOL::toXMLEncoding(username)+"</pusername>\n"+
                           "<ppassword>"+TOOL::toXMLEncoding(password)+"</ppassword>\n</xml>").toUtf8();
    html = network->postByteArray("http://tsinsen.com/user.Login.dt",loginData,"text/xml; charset=UTF-8");
    if(!html.contains("\"ret\":\"1\""))
    {
        return false;
    }
    return true;
}

bool THUSubmitter::submit(MyNetwork *network)
{
    QString language;
    if(submission["language"].toString() == "C++")
        language = "CPP";
    else if(submission["language"].toString() == "C")
        language = "C";
    else if(submission["language"].toString() == "Pascal")
        language = "PAS";
    else return false;
    QByteArray submitData = QString("<xml>\n<pgpid>"+TOOL::toXMLEncoding(submission["originID"].toString())+
                            "</pgpid>\n<plang>"+TOOL::toXMLEncoding(language)+"</plang>\n<pcode>"+
                            TOOL::toXMLEncoding(submission["source"].toString())+"</pcode>\n</xml>").toUtf8();
    html = network->postByteArray("http://tsinsen.com/test.SubmitCode.dt",submitData,"text/xml; charset=UTF-8");
    if(!html.contains("\"ret\":\"1\""))
    {
        return false;
    }
    return true;
}

void THUSubmitter::getResult(MyNetwork *network)
{
    for(int times=0;;)
    {
        int finish = 0;
        QByteArray postData = QString("<xml>\n<puserid>-1</puserid>\n<ppage>1</ppage>\n</xml>").toUtf8();
        html = network->postByteArray("http://tsinsen.com/test.SelectResults.dt",postData,"text/xml; charset=UTF-8");
        if(html == "")times++;
        if(times >= 10)
        {
            emit errorMessage(QString("无法获取提交结果\n" + html).toUtf8());
            upload["result"]="获取评测结果失败";
            upload["afresh"]=1;
            uploadSubmission(network,upload);
            return;
        }
        QJsonParseError error;
        QJsonObject result = QJsonDocument::fromJson(TOOL::regFind(html,"ret.+=(\\{.*\\});",true).toUtf8(),&error).object();
        //errorMessage(TOOL::regFind(html,"ret.+=(\\{.*\\});",true).toUtf8());
        if(error.error != QJsonParseError::NoError)
        {
            times ++;
            continue;
        }
        QStringList finishList;
        finishList<<"Color_AC"<<"Color_RJ"<<"Color_WA"<<"Color_CE"<<"Color_RE"<<"Color_REL"<<"Color_TLE"<<"Color_MLE";
        foreach(QString str,finishList)
            if(result["result"].toString().contains(str))
            {
                finish = 1;
                break;
            }
        upload["finish"]=finish;
        if(result.contains("result"))upload["result"]=result["result"];
        if(result.contains("time"))upload["time"]=result["time"];
        if(result.contains("memory"))upload["memory"]=result["memory"];
        if(result.contains("score") && result["score"].toString().toInt())upload["score"]=result["score"];
        if(upload["result"].toString().contains("Color_AC"))
            upload["accepted"]=1;
        if(finish)
        {
            html = network->get("http://tsinsen.com/DetailResult.page?submitid="+result["id"].toString());
            qDebug()<<TOOL::regFind(html,"var obj = (\\{\"username\":\".*\"\\})",true);
            QJsonObject log = QJsonDocument::fromJson(TOOL::regFind(html,"var obj = (\\{.*username.*\\})",true).toUtf8(),&error).object();
            if(error.error != QJsonParseError::NoError)
            {
                times ++;
                continue;
            }
            QJsonArray judgeLog;
            for(int index=0;;index++)
            {
                if(!log.contains("d"+QString::number(index)))break;
                QJsonObject json = log["d"+QString::number(index)].toObject();
                QJsonObject temp;
                temp["result"] = json["result"];
                temp["time"] = json["time"];
                temp["memory"] = json["memory"];
                if(json["result"].toString().contains("Color_AC"))
                    temp["type"] = "accepted";
                else if(json["result"].toString().contains("Color_WA"))
                    temp["type"] = "wrong";
                else temp["type"] = "le";
                judgeLog.append(temp);
            }
            upload["judgeLog"]=judgeLog;
        }
        if(!uploadSubmission(network,upload))return;
        if(finish)break;
        msleep(1000*2);
    }
}

bool THUSubmitter::uploadSubmission(MyNetwork *network, QJsonObject upload)
{
    qDebug()<<"THU upload : "<<upload;
    QByteArray postData = "submission="+QJsonDocument(upload).toJson().toPercentEncoding();
    html = network->postByteArray(uploadSubmissionAddress + TOOL::getToken(token),postData);
    if(html != ""){
        emit errorMessage("\n" + html.toUtf8() + "\n" + QJsonDocument(upload).toJson());
        return false;
    }
    return true;
}
