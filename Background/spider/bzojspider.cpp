#include "bzojspider.h"

#include <QDebug>
#include <QFileInfo>
#include <QTextCodec>

BZOJSpider::BZOJSpider(QString originID, QObject *parent) :
    BaseThread(parent),originID(originID)
{
    qDebug()<<"BZOJ Spider";
}

void BZOJSpider::run()
{
    emit updateInfo("Spider BZOJ " + originID);
    emit updateStatus("一切正常");

    MyNetwork network;
    QString url = "http://www.lydsy.com/JudgeOnline/problem.php?id=" + originID;
    QString html = spider(url,&network);
    if(html == "")return;
    emit updateStatus("正在解析html");

    QJsonObject obj;
    obj["title"] = TOOL::regFind(html,"<center><h2>"+ originID +": (.*)</h2>",true);
    obj["description"] = uploadImages(TOOL::regFind(html,"<h2>Description</h2>(.*)<h2>Input</h2>",true),&network);
    obj["input"] = uploadImages(TOOL::regFind(html,"<h2>Input</h2>(.*)<h2>Output</h2>",true),&network);
    obj["output"] = uploadImages(TOOL::regFind(html,"<h2>Output</h2>(.*)<h2>Sample Input</h2>",true),&network);
    obj["sample"] = "<h4>Input</h4><div class=\"frame\">"+uploadImages(TOOL::regFind(html,"<h2>Sample Input</h2>(.*)<h2>Sample Output</h2>",true),&network)+"</div><h4>Output</h4><div class=\"frame\">" + uploadImages(TOOL::regFind(html,"<h2>Sample Output</h2>(.*)<h2>HINT</h2>",true),&network) + "</div>";
    obj["datarange"] = "";
    obj["hint"] = uploadImages(TOOL::regFind(html,"<h2>HINT</h2>(.*)<h2>Source</h2>",true),&network);
    QRegExp replace("<.*>");
    replace.setMinimal(true);
    if(obj["hint"].toString().trimmed().replace(replace,"") == "")
        obj["hint"] = "";
    obj["timelimit"] = TOOL::regFind(html,"Time Limit: </span>(\\d+) Sec") + "S";
    obj["memorylimit"] = TOOL::regFind(html,"Memory Limit: </span>(\\d+) MB") + "MB";
    obj["url"] = url;
    obj["statusUrl"] = "http://www.lydsy.com/JudgeOnline/status.php?problem_id=" + originID;
    obj["originOJ"] = "BZOJ";
    obj["originID"] = originID;
    obj["source"] = "<a href=\""+url+"\" target=\"_blank\">BZOJ "+originID+"</a>";
    obj["LLFormat"] = "%lld & %llu";
    QJsonDocument doc;
    doc.setObject(obj);

    emit updateStatus("正在上传");
    QByteArray postData = "task="+doc.toJson().toPercentEncoding();
    html = network.postByteArray(uploadTaskAddress + TOOL::getToken(token),postData);
    emit updateStatus("结束");
    if(html != ""){
        emit errorMessage(doc.toJson() + "\n" + html.toUtf8());
    }
}

QString BZOJSpider::spider(QString url, MyNetwork *network)
{
    emit updateStatus("正在获取html");
    QByteArray temp = network->get(url);
    QTextCodec *codec = QTextCodec::codecForHtml(temp);
    QString html = codec->toUnicode(temp);
    if(html.contains("<title>Problem is not Availables"))
    {
        errorMessage(html.toUtf8());
        return "";
    }
    return html;
}

QString BZOJSpider::uploadImages(QString html, MyNetwork *network)
{
    QRegExp reg("<img(.*)src=['\"]?([^\"'\\s]+)['\"]?(.*)/?>");
    reg.setMinimal(true);
    for(int pos=reg.indexIn(html);pos!=-1;pos=reg.indexIn(html,pos+1))
    {
        QByteArray image = network->downloadImage("http://www.lydsy.com/JudgeOnline/" + reg.cap(2));
        QHttpMultiPart multiPart(QHttpMultiPart::FormDataType);

        QHttpPart imagePart;
        imagePart.setHeader(QNetworkRequest::ContentTypeHeader, QVariant("image/png"));
        imagePart.setHeader(QNetworkRequest::ContentDispositionHeader, QVariant("form-data; name=\"file\"; filename=\"pic.png\""));
        imagePart.setBody(image);
        multiPart.append(imagePart);

        QString result = network->postHttpPart(uploadAddress + TOOL::getToken(token),&multiPart);
        if(!result.startsWith("Path:"))
        {
            errorMessage(result.toUtf8());
            this->exit();
        }
        QString path = result.mid(5);
        html.replace(pos,reg.cap(0).length(),"<img "+reg.cap(1)+"src=\""+path+"\""+reg.cap(3)+" />");
    }
    return html;
}

