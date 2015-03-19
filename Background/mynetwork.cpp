#include "mynetwork.h"

#include <QNetworkRequest>
#include <QNetworkReply>
#include <QPixmap>
#include <QBuffer>
#include <QDebug>

MyNetwork::MyNetwork(QObject *parent) :
    QObject(parent)
{
    manager = new QNetworkAccessManager(this);
    loop = new QEventLoop(this);
    cookieJar = new MyCookieJar(this);
    manager->setCookieJar(cookieJar);
}

QByteArray MyNetwork::get(QString url)
{
    QNetworkRequest request;
    request.setUrl(QUrl(url));
    if(!cookieJar->getCookies().empty())
    {
        QVariant var;
        var.setValue(cookieJar->getCookies());
        request.setHeader(QNetworkRequest::CookieHeader,var);
    }
    QNetworkReply *reply = manager->get(request);
    reply->ignoreSslErrors();
    connect(reply,SIGNAL(finished()),loop,SLOT(quit()));
    loop->exec();
    QByteArray data = reply->readAll();
    reply->deleteLater();
    return data;
}

QByteArray MyNetwork::postHttpPart(QString url, QHttpMultiPart *httpPart)
{
    QNetworkRequest request;
    request.setUrl(QUrl(url));
    if(!cookieJar->getCookies().empty())
    {
        QVariant var;
        var.setValue(cookieJar->getCookies());
        request.setHeader(QNetworkRequest::CookieHeader,var);
    }
    QNetworkReply *reply = NULL;
    reply = manager->post(request,httpPart);
    reply->ignoreSslErrors();
    connect(reply,SIGNAL(finished()),loop,SLOT(quit()));
    loop->exec();
    QByteArray result = reply->readAll();
    reply->deleteLater();
    return result;
}

QByteArray MyNetwork::postByteArray(QString url, QByteArray data, QString contentType)
{
    QNetworkRequest request;
    request.setUrl(QUrl(url));
    request.setHeader(QNetworkRequest::ContentTypeHeader,contentType);
    if(!cookieJar->getCookies().empty())
    {
        QVariant var;
        var.setValue(cookieJar->getCookies());
        request.setHeader(QNetworkRequest::CookieHeader,var);
    }
    QNetworkReply *reply = NULL;
    reply = manager->post(request,data);
    reply->ignoreSslErrors();
    connect(reply,SIGNAL(finished()),loop,SLOT(quit()));
    loop->exec();
    QByteArray result = reply->readAll();
    reply->deleteLater();
    return result;
}

QByteArray MyNetwork::downloadImage(QString url)
{
    QPixmap pixmap;
    pixmap.loadFromData(get(url));
    QByteArray image;
    QBuffer buffer(&image);
    pixmap.save(&buffer,"PNG");
    buffer.open(QIODevice::WriteOnly);
    return image;
}

QByteArray MyNetwork::getCookie(QByteArray name)
{
    return cookieJar->getCookie(name);
}
