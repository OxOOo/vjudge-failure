#ifndef HDUSPIDER_H
#define HDUSPIDER_H

#include "../basethread.h"
#include "../mynetwork.h"

#include <QJsonObject>
#include <QJsonDocument>

class HDUSpider : public BaseThread
{
    Q_OBJECT
public:
    explicit HDUSpider(QString originID ,QObject *parent = 0);
protected:
    bool isNeedAccount(){return false;}

    void run();
signals:

public slots:

private:
    QString spider(QString url, MyNetwork *network);

    QString uploadImages(QString html, MyNetwork *network);

    QString originID;
};

#endif // HDUSPIDER_H
