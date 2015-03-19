#include "hduspider.h"

#include <QDebug>
#include <QFileInfo>
#include <QTextCodec>

HDUSpider::HDUSpider(QString originID ,QObject *parent) :
    BaseThread(parent),originID(originID)
{
    qDebug()<<"HDU Spider";
}

void HDUSpider::run()
{
    emit updateInfo("Spider HDU " + originID);
    emit updateStatus("一切正常");

    MyNetwork network;
    QString url = "http://acm.hdu.edu.cn/showproblem.php?pid=" + originID;
    QString html = spider(url,&network);
    if(html == "")return;
    emit updateStatus("正在解析html");

    QJsonObject obj;
    obj["title"] = TOOL::regFind(html,"color:#1A5CC8'>(.*)</h1>",true);
    obj["description"] = uploadImages(TOOL::regFind(html,"Problem Description</div>(.*)<br><[^<>]*panel_title[^<>]*>",true),&network);
    obj["input"] = uploadImages(TOOL::regFind(html,"Input</div>(.*)<br><[^<>]*panel_title[^<>]*>",true),&network);
    obj["output"] = uploadImages(TOOL::regFind(html,"Output</div>(.*)<br><[^<>]*?panel_title[^<>]*>",true),&network);
    obj["sample"] = "<h4>Input</h4><div class=\"frame\">"+uploadImages(TOOL::regFind(html,"Sample Input</div>(.*)<br><[^<>]*panel_title[^<>]*>",true),&network)+"</div><h4>Output</h4><div class=\"frame\">" + uploadImages(TOOL::regFind(html,"Sample Output</div><div class=panel_content>(<pre>.*</pre>)",true),&network) + "</div>";
    obj["datarange"] = "";
    obj["hint"] = uploadImages(TOOL::regFind(html,"<i>Hint</i></div>(.*)</div>",true),&network);
    if(obj["hint"].toString() != "")
        obj["hint"] = "<pre>" + obj["hint"].toString() + "</pre>";
    obj["timelimit"] = TOOL::regFind(html,"(\\d*) MS") + "MS";
    obj["memorylimit"] = TOOL::regFind(html,"/(\\d*) K") + "K";
    obj["url"] = url;
    obj["statusUrl"] = "http://acm.hdu.edu.cn/status.php?pid=" + originID;
    obj["originOJ"] = "HDU";
    obj["originID"] = originID;
    obj["source"] = "<a href=\""+url+"\" target=\"_blank\">HDU "+originID+"</a>";
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

QString HDUSpider::spider(QString url, MyNetwork *network)
{
    emit updateStatus("正在获取html");
    QByteArray temp = network->get(url);
    QTextCodec *codec = QTextCodec::codecForHtml(temp);
    QString html = codec->toUnicode(temp);
    if(html.contains("<DIV>No such problem"))
    {
        errorMessage(html.toUtf8());
        return "";
    }
    return html;
}

QString HDUSpider::uploadImages(QString html, MyNetwork *network)
{
    QRegExp reg("<img(.*)src=['\"]?([^\"'\\s]+)['\"]?(.*)/?>");
    reg.setMinimal(true);
    for(int pos=reg.indexIn(html);pos!=-1;pos=reg.indexIn(html,pos+1))
    {
        QByteArray image = network->downloadImage("http://acm.hdu.edu.cn" + reg.cap(2));
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
