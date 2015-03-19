#include "cfsubmitter.h"

#include <QDebug>

CFSubmitter::CFSubmitter(QJsonObject submission, QObject *parent) :
    BaseThread(parent),submission(submission)
{
    qDebug()<<"CF Submitter";
}

void CFSubmitter::run()
{
    emit updateInfo("Submitter CF " + submission["originID"].toString() + " " + submission["sid"].toString());
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
    qDebug()<<"CF login";

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
    qDebug()<<"CF submit";

    emit updateStatus("正在获取评测结果");
    upload["result"]="正在获取评测结果";
    if(!uploadSubmission(&network,upload))return;
    getResult(&network);
}

bool CFSubmitter::login(MyNetwork *network)
{
    html = network->get("http://codeforces.com/enter");
    csrf_token = TOOL::regFind(html,"name='csrf_token' value='(\\w{32})'");
    QString tta = getTTA(network->getCookie("39ce7"));

    QByteArray postData = "action=enter";
    postData.append("&handle="+username);
    postData.append("&password="+password);
    postData.append("&_tta="+tta);
    postData.append("&csrf_token="+csrf_token);
    html = network->postByteArray("http://codeforces.com/enter",postData);
    return html == "";
}

bool CFSubmitter::submit(MyNetwork *network)
{
    QString programTypeId;
    if(submission["language"].toString() == "C++")
        programTypeId = "1";
    else if(submission["language"].toString() == "C")
        programTypeId = "10";
    else if(submission["language"].toString() == "Pascal")
        programTypeId = "4";
    else return false;

    html = network->get("http://codeforces.com/problemset/submit");
    if(html.contains("The requested URL was not found on this server") ||
       html.contains("Codeforces is temporary unavailable") ||
       html.contains("The page is temporarily blocked by administrator"))
        return false;
    QRegExp reg("(\\d+)([A-Z])");
    reg.indexIn(submission["originID"].toString());
    QString contestID = reg.cap(1);
    QString submittedProblemIndex = reg.cap(2);
    QString action = "submitSolutionFormSubmitted";
    QString source = submission["source"].toString();
    QString sourceFile = "";
    QString _tta = getTTA(network->getCookie("39ce7"));
    submission["source"] = source + "\n//" + TOOL::randomString();

    QByteArray postData;
    postData.append("csrf_token=" + csrf_token);
    postData.append("&action=" + action);
    postData.append("&contestId=" + contestID);
    postData.append("&submittedProblemIndex=" + submittedProblemIndex);
    postData.append("&programTypeId=" + programTypeId);
    postData.append("&source=" + source.toUtf8().toPercentEncoding());
    postData.append("&sourceFile=" + sourceFile);
    postData.append("&sourceCodeConfirmed=true");
    postData.append("&regexConfirm=.*[^A-Za-z\\s]ll[i|u|d][^\\w].*");
    postData.append("&doNotShowWarningAgain=no");
    postData.append("&_tta=" + _tta);
    html = network->postByteArray("http://codeforces.com/problemset/submit?csrf_token="+csrf_token,postData);
    return html == "";
}

void CFSubmitter::getResult(MyNetwork *network)
{
    emit updateStatus("正在获取提交ID");
    QString ID;
    for(int times = 0;;times ++)
    {
        if((ID = getID(network))!= "")break;
        if(times == 5)
        {
            emit errorMessage(QString("无法获取提交ID\n" + html).toUtf8());
            upload["result"]="获取评测结果失败";
            upload["afresh"]=1;
            uploadSubmission(network,upload);
            return;
        }
        msleep(1000 * 2);
    }
    emit updateStatus("正在获取评测结果");
    for(int times=0;;)
    {
        int finish = 0;
        QByteArray postData = QString("csrf_token="+csrf_token+"&submissionId="+ID).toUtf8();
        html = network->postByteArray("http://codeforces.com/data/submitSource",postData);
        QJsonParseError error;
        QJsonObject json = QJsonDocument::fromJson(html.toUtf8(),&error).object();
        if(error.error != QJsonParseError::NoError)
            times ++;
        if(times == 20)
        {
            emit errorMessage(QString("无法获取提交结果\n" + html).toUtf8());
            upload["result"]="获取评测结果失败";
            upload["afresh"]=1;
            uploadSubmission(network,upload);
            return;
        }
        upload["result"] = json["verdict"];
        if(json["waiting"].toString() == "true")
        {
        }else{
            finish = 1;
            int time=0,memory=0;
            int testCount = json["testCount"].toString().toInt();
            QJsonArray judgeLog;
            for(int kase=1;kase<=testCount;kase++)
            {
                QJsonObject temp;
                temp["result"] = json["verdict#"+QString::number(kase)];
                temp["time"] = json["timeConsumed#"+QString::number(kase)].toString() + "ms";
                temp["memory"] = QString::number(json["memoryConsumed#"+QString::number(kase)].toString().toInt()/1024) + "KB";
                if(json["verdict#"+QString::number(kase)] == "OK")
                    temp["type"] = "accepted";
                else if(json["verdict#"+QString::number(kase)] == "OK")
                    temp["type"] = "wrong";
                else temp["type"] = "le";
                time=qMax(time,json["timeConsumed#"+QString::number(kase)].toString().toInt());
                memory=qMax(memory,json["memoryConsumed#"+QString::number(kase)].toString().toInt());
                judgeLog.append(temp);
            }
            upload["judgeLog"] = judgeLog;
            upload["time"] = QString::number(time) + "ms";
            upload["memory"] = QString::number(memory/1024) + "KB";
            if(json["verdict"].toString().contains("Accepted"))
            {
                upload["accepted"]=1;
                upload["score"]=100;
            }
        }
        upload["finish"]=finish;
        if(!uploadSubmission(network,upload))return;
        if(finish)break;
        msleep(1000*2);
    }
}

QString CFSubmitter::getID(MyNetwork *network)
{
    html = network->get("http://codeforces.com/api/user.status?handle="+username+"&from=1&count=1");
    QJsonObject json = QJsonDocument::fromJson(html.toUtf8()).object();
    if(json["status"] != "OK")return "";
    return QString::number(json["result"].toArray().first().toObject()["id"].toInt());
}

QString CFSubmitter::getTTA(QByteArray _39ce7)
{
    int tta  = 0;
    for(int c = 0; c < _39ce7.length(); c++){
        tta = (tta + (c + 1) * (c + 2) * _39ce7.at(c)) % 1009;
        if(c % 3 == 0) tta++;
        if(c % 2 == 0) tta *= 2;
        if(c > 0) tta -= ((int)(_39ce7.at(c / 2) / 2)) * (tta % 5);
        while(tta < 0) tta += 1009;
        while(tta >= 1009) tta -= 1009;
    }
    return QString::number(tta);
}

bool CFSubmitter::uploadSubmission(MyNetwork *network, QJsonObject upload)
{
    qDebug()<<"CF upload : "<<upload;
    QByteArray postData = "submission="+QJsonDocument(upload).toJson().toPercentEncoding();
    html = network->postByteArray(uploadSubmissionAddress + TOOL::getToken(token),postData);
    if(html != ""){
        emit errorMessage("\n" + html.toUtf8() + "\n" + QJsonDocument(upload).toJson());
        return false;
    }
    return true;
}
