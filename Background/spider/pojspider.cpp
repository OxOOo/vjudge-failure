#include "pojspider.h"

#include <QDebug>
#include <QFileInfo>
#include <QTextCodec>

POJSpider::POJSpider(QString originID, QObject *parent) :
    BaseThread(parent),originID(originID)
{
    qDebug()<<"POJ Spider";
}

void POJSpider::run()
{
    emit updateInfo("Spider POJ " + originID);
    emit updateStatus("一切正常");

    MyNetwork network;
    QString url = "http://poj.org/problem?id=" + originID;
    QString html = spider(url,&network);
    if(html == "")return;
    emit updateStatus("正在解析html");

    QJsonObject obj;
    obj["title"] = TOOL::regFind(html,"<title>\\d+ -- (.*)</title>",true);
    obj["description"] = uploadImages(TOOL::regFind(html,"<p class=\"pst\">Description</p>(.*)<p class=\"pst\">",true),&network);
    obj["input"] = uploadImages(TOOL::regFind(html,"<p class=\"pst\">Input</p>(.*)<p class=\"pst\">",true),&network);
    obj["output"] = uploadImages(TOOL::regFind(html,"<p class=\"pst\">Output</p>(.*)<p class=\"pst\">",true),&network);
    obj["sample"] = "<h4>Input</h4><div class=\"frame\">"+uploadImages(TOOL::regFind(html,"<p class=\"pst\">Sample Input</p>(.*)<p class=\"pst\">",true),&network)+"</div><h4>Output</h4><div class=\"frame\">" + uploadImages(TOOL::regFind(html,"<p class=\"pst\">Sample Output</p>(.*)<p class=\"pst\">",true),&network) + "</div>";
    obj["datarange"] = "";
    obj["hint"] = uploadImages(TOOL::regFind(html,"<p class=\"pst\">Hint</p>(.*)<p class=\"pst\">",true),&network);
    obj["timelimit"] = TOOL::regFind(html,"<b>Time Limit:</b> (\\d{3,})MS</td>") + "MS";
    obj["memorylimit"] = TOOL::regFind(html,"<b>Memory Limit:</b> (\\d{2,})K</td>") + "K";
    obj["url"] = url;
    obj["statusUrl"] = "http://poj.org/status?problem_id=" + originID;
    obj["originOJ"] = "POJ";
    obj["originID"] = originID;
    obj["source"] = "<a href=\""+url+"\" target=\"_blank\">POJ "+originID+"</a>";
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

QString POJSpider::spider(QString url, MyNetwork *network)
{
    emit updateStatus("正在获取html");
    QByteArray temp = network->get(url);
    QTextCodec *codec = QTextCodec::codecForHtml(temp);
    QString html = codec->toUnicode(temp);
    if(html.contains("<li>Can not find problem"))
    {
        errorMessage(html.toUtf8());
        return "";
    }
    return html;
}

QString POJSpider::uploadImages(QString html, MyNetwork *network)
{
    QRegExp reg("<img(.*)src=['\"]?([^\"'\\s]+)['\"]?(.*)/?>");
    reg.setMinimal(true);
    for(int pos=reg.indexIn(html);pos!=-1;pos=reg.indexIn(html,pos+1))
    {
        QByteArray image = network->downloadImage("http://poj.org/" + reg.cap(2));

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
