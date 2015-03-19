#ifndef TEST_H
#define TEST_H

#include "../basethread.h"
#include "../mynetwork.h"
#include "runner.h"

#include <QJsonObject>
#include <QJsonDocument>
#include <QJsonArray>
#include <QJsonParseError>

class Test : public BaseThread
{
    Q_OBJECT
public:
    explicit Test(QJsonObject test, QObject *parent = 0);
protected:
    bool isNeedAccount(){return false;}

    void run();
signals:

public slots:

private:
    QJsonObject test;

    void uploadTest(int id, int error, QString result);

    void CPlusPlus();

    void C();

    void Pascal();
};

#endif // TEST_H
