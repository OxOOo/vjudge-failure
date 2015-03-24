#include "thuspider.h"

#include <QJsonObject>
#include <QJsonDocument>
#include <QFileInfo>
#include <QTextCodec>

THUSpider::THUSpider(QString originID, QObject *parent) :
    BaseThread(parent),originID(originID)
{
}

void THUSpider::run()
{
    emit updateInfo("Spider THU " + originID);
    emit updateStatus("一切正常");

    MyNetwork network;

    emit updateStatus("正在登录");
    for(int times=0;;times++)
    {
        if(login(&network))break;
        if(times == 5)
        {
            emit errorMessage("登陆失败\n");
            return;
        }
    }

    emit updateStatus("正在获取html");
    QString url = "http://tsinsen.com/"+originID;
    QByteArray temp = network.get(url);
    QTextCodec *codec = QTextCodec::codecForHtml(temp);
    QString html = codec->toUnicode(temp);
    emit updateStatus("正在解析html");

    QJsonObject obj;
    obj["title"] = TOOL::regFind(html,"<div class=\"probtitle\" id=\"ptit\">"+originID+"\\. (.*)</div>",true);
    obj["description"] = uploadImages(TOOL::regFind(html,"<div class='pdsec'>问题描述</div>(.*</div>)",true),&network);
    obj["input"] = uploadImages(TOOL::regFind(html,"<div class='pdsec'>输入格式</div>(.*</div>)",true),&network);
    obj["output"] = uploadImages(TOOL::regFind(html,"<div class='pdsec'>输出格式</div>(.*</div>)",true),&network);
    obj["sample"] = "<h4>Input</h4><div class=\"frame\">"+uploadImages(TOOL::regFind(html,"<div class='pdsec'>样例输入</div>(.*</div>)",true),&network)+"</div><h4>Output</h4><div class=\"frame\">" + uploadImages(TOOL::regFind(html,"<div class='pdsec'>样例输出</div>(.*</div>)",true),&network) + "</div>";
    obj["datarange"] = uploadImages(TOOL::regFind(html,"<div class='pdsec'>数据规模和约定</div>(.*</div>)",true),&network);
    obj["hint"] = "";
    obj["timelimit"] = TOOL::regFind(html,"时间限制：<span class=\"uline\">([\\d\\.a-z]*)</span>",true).toUpper();
    obj["memorylimit"] = TOOL::regFind(html,"内存限制：<span class=\"uline\">([\\d\\.A-Z]*)</span>",true).toUpper();
    obj["url"] = url;
    obj["statusUrl"] = "http://tsinsen.com/AllSubmits.page?type=a&gpid="+originID;
    obj["originOJ"] = "THU";
    obj["originID"] = originID;
    obj["source"] = "<a href=\""+url+"\" target=\"_blank\">清澄 "+originID+"</a>";
    obj["LLFormat"] = "%I64d & %I64u";
    QJsonDocument doc;
    doc.setObject(obj);

    emit updateStatus("正在上传");
    QByteArray postData = "task="+doc.toJson().toPercentEncoding();
    html = network.postByteArray(uploadTaskAddress + TOOL::getToken(token),postData);
    if(html != ""){
        emit errorMessage(doc.toJson() + "\n" + html.toUtf8());
    }
}

bool THUSpider::login(MyNetwork *network)
{
    QByteArray loginData = QString("<xml>\n<pusername>"+TOOL::toXMLEncoding(username)+"</pusername>\n"+
                           "<ppassword>"+TOOL::toXMLEncoding(password)+"</ppassword>\n</xml>").toUtf8();
    QString html = network->postByteArray("http://tsinsen.com/user.Login.dt",loginData,"text/xml; charset=UTF-8");
    return html.contains("\"ret\":\"1\"");
}

QString THUSpider::uploadImages(QString html, MyNetwork *network)
{
    QRegExp reg("<img(.*)src=['\"]?([^\"'\\s]+)['\"]?(.*)/?>");
    reg.setMinimal(true);
    for(int pos=reg.indexIn(html);pos!=-1;pos=reg.indexIn(html,pos+1))
    {
        QByteArray image = network->downloadImage(TOOL::joinUrl("http://tsinsen.com",reg.cap(2)));
        if(image.isEmpty())
        {
            emit errorMessage(QString("image empty\nhtml: " + reg.cap(0) + "\n" +
                              reg.cap(1) + " " + reg.cap(2) + " " + reg.cap(3) +
                              "\nurl: " + TOOL::joinUrl("http://tsinsen.com",reg.cap(2))).toUtf8());
            continue;
        }
        QHttpMultiPart multiPart(QHttpMultiPart::FormDataType);

        QHttpPart imagePart;
        imagePart.setHeader(QNetworkRequest::ContentTypeHeader, QVariant("image/png"));
        imagePart.setHeader(QNetworkRequest::ContentDispositionHeader, QVariant("form-data; name=\"file\"; filename=\"pic.png\""));
        imagePart.setBody(image);
        multiPart.append(imagePart);

        QString result = network->postHttpPart(uploadAddress + TOOL::getToken(token),&multiPart);
        if(!result.startsWith("Path:"))
        {
            emit errorMessage(result.toUtf8());
            this->exit();
        }
        QString path = result.mid(5);
        html.replace(pos,reg.cap(0).length(),"<img "+reg.cap(1)+"src=\""+path+"\""+reg.cap(3)+" />");
    }
    return html;
}
