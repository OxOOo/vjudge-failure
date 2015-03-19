#ifndef THUSPIDER_H
#define THUSPIDER_H

#include "../basethread.h"
#include "../mynetwork.h"

class THUSpider : public BaseThread
{
    Q_OBJECT
public:
    explicit THUSpider(QString originID, QObject *parent = 0);

protected:
    bool isNeedAccount(){return true;}

    void run();
signals:

public slots:

private:
    bool login(MyNetwork *network);

    QString uploadImages(QString html, MyNetwork *network);

    QString originID;
};

#endif // THUSPIDER_H
