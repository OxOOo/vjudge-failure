#ifndef POJSPIDER_H
#define POJSPIDER_H

#include "basethread.h"
#include "../mynetwork.h"

#include <QJsonObject>
#include <QJsonDocument>

class POJSpider : public BaseThread
{
    Q_OBJECT
public:
    explicit POJSpider(QString originID ,QObject *parent = 0);
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

#endif // POJSPIDER_H
