#ifndef MYNETWORK_H
#define MYNETWORK_H

#include <QObject>
#include <QNetworkAccessManager>
#include <QHttpMultiPart>
#include <QEventLoop>
#include <QNetworkCookieJar>
#include <QNetworkCookie>

class MyCookieJar : public QNetworkCookieJar
{
    Q_OBJECT

public:
    MyCookieJar(QObject *parent = 0):QNetworkCookieJar(parent){}
    ~MyCookieJar(){}

    QList<QNetworkCookie> getCookies(){return allCookies();}
    void setCookies(const QList<QNetworkCookie>& cookieList){setAllCookies(cookieList);}
    QByteArray getCookie(QByteArray name)
    {
        foreach (QNetworkCookie cookie,allCookies()) {
            if(cookie.name() == name)
                return cookie.value();
        }
        return QByteArray();
    }
};

class MyNetwork : public QObject
{
    Q_OBJECT
public:
    explicit MyNetwork(QObject *parent = 0);

    QByteArray get(QString url);

    QByteArray postHttpPart(QString url, QHttpMultiPart *httpPart);

    QByteArray postByteArray(QString url, QByteArray data, QString contentType = "application/x-www-form-urlencoded");

    QByteArray downloadImage(QString url);

    QByteArray getCookie(QByteArray name);
signals:

public slots:

private:
    QNetworkAccessManager *manager;
    QEventLoop *loop;
    MyCookieJar *cookieJar;
};

#endif // MYNETWORK_H
