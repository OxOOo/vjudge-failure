#ifndef CFSPIDER_H
#define CFSPIDER_H

#include "../basethread.h"
#include "../mynetwork.h"

class CFSpider : public BaseThread
{
    Q_OBJECT
public:
    explicit CFSpider(QString originID ,QObject *parent = 0);

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

#endif // CFSPIDER_H
