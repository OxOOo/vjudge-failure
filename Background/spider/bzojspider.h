#ifndef BZOJSPIDER_H
#define BZOJSPIDER_H

#include "basethread.h"
#include "../mynetwork.h"

#include <QJsonObject>
#include <QJsonDocument>

class BZOJSpider : public BaseThread
{
    Q_OBJECT
public:
    explicit BZOJSpider(QString originID ,QObject *parent = 0);
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

#endif // BZOJSPIDER_H
