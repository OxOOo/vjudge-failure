#include "cfspider.h"
#include <QRegExp>
#include <QDebug>
#include <QJsonDocument>
#include <QJsonObject>
#include <QFileInfo>
#include <QTextCodec>

CFSpider::CFSpider(QString originID, QObject *parent) :
    BaseThread(parent),originID(originID)
{
    qDebug()<<"CF Spider";
}

void CFSpider::run()
{
    emit updateInfo("Spider CF " + originID);
    emit updateStatus("一切正常");

    MyNetwork network;
    QRegExp reg("(\\d+)([A-Z]+)");
    reg.indexIn(originID);
    QString url = "http://codeforces.com/problemset/problem/"+reg.cap(1)+"/"+reg.cap(2);
    QString html = spider(url,&network);
    if(html == "")return;
    emit updateStatus("正在解析html");

    QJsonObject obj;
    obj["title"] = TOOL::regFind(html,"<div class=\"title\">[A-Z]\\. (.*)</div><div class=\"time-limit\">");
    obj["description"] = uploadImages(TOOL::regFind(html,"standard output</div></div><div>(.*)</div><div class=\"input-specification"),&network);
    obj["input"] = uploadImages(TOOL::regFind(html,"<div class=\"section-title\">Input</div>(.*)</div><div class=\"output-specification\">"),&network);
    obj["output"] = uploadImages(TOOL::regFind(html,"<div class=\"section-title\">Output</div>(.*)</div><div class=\"sample-tests\">"),&network);
    obj["sample"] = "<style type=\"text/css\">.input, .output {border: 1px solid #888888;} .output {margin-bottom:1em;position:relative;top:-1px;} .output pre,.input pre {background-color:#EFEFEF;line-height:1.25em;margin:0;padding:0.25em;} .title {background-color:#FFFFFF;border-bottom: 1px solid #888888;font-family:arial;font-weight:bold;padding:0.25em;}</style>" + uploadImages(TOOL::regFind(html,"<div class=\"sample-test\">(.*</pre></div>)</div>"),&network);
    obj["datarange"] = "";
    obj["hint"] = uploadImages(TOOL::regFind(html,"</div></div></div><div class=\"note\"><div class=\"section-title\">Note</div>(.*</p>)</div></div></div>"),&network);
    obj["timelimit"] = TOOL::regFind(html,"time limit per test</div>(\\d+) second") + "S";
    obj["memorylimit"] = TOOL::regFind(html,"memory limit per test</div>(\\d+) megabytes") + "MB";
    obj["url"] = url;
    obj["statusUrl"] = "http://codeforces.com/contest/"+reg.cap(1)+"/status/"+reg.cap(2);
    obj["originOJ"] = "CF";
    obj["originID"] = originID;
    obj["source"] = "<a href=\""+url+"\" target=\"_blank\">CF "+originID+"</a>";
    obj["LLFormat"] = "%I64d & %I64u";
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

QString CFSpider::spider(QString url,MyNetwork *network)
{
    emit updateStatus("正在获取html");
    QString html;
    for(int times = 0;;times ++)
    {
        if(times == 5)
        {
            errorMessage(html.toUtf8());
            return "";
        }
        QByteArray temp = network->get(url);
        QTextCodec *codec = QTextCodec::codecForHtml(temp);
        html = codec->toUnicode(temp);
        if(html.contains("The requested URL was not found on this server") ||
           html.contains("Codeforces is temporary unavailable") ||
           html.contains("The page is temporarily blocked by administrator"))
            continue;
        break;
        msleep(1000 * 10);
    }
    return html;
}

QString CFSpider::uploadImages(QString html, MyNetwork *network)
{
    QRegExp reg("<img(.*)src=['\"]?([^\"'\\s]+)['\"]?(.*)/?>");
    reg.setMinimal(true);
    for(int pos=reg.indexIn(html);pos!=-1;pos=reg.indexIn(html,pos+1))
    {
        QByteArray image = network->downloadImage(reg.cap(2));
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
