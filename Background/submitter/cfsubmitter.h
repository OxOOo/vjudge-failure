#ifndef CFSUBMITTER_H
#define CFSUBMITTER_H

#include "../basethread.h"
#include "../mynetwork.h"

#include <QJsonObject>
#include <QJsonDocument>
#include <QJsonArray>
#include <QJsonParseError>

class CFSubmitter : public BaseThread
{
    Q_OBJECT
public:
    explicit CFSubmitter(QJsonObject submission,QObject *parent = 0);

protected:
    bool isNeedAccount(){return true;}

    void run();
signals:

public slots:

private:
    bool login(MyNetwork *network);

    bool submit(MyNetwork *network);

    void getResult(MyNetwork *network);

    QString getID(MyNetwork *network);

    QString getTTA(QByteArray _39ce7);

    bool uploadSubmission(MyNetwork *network, QJsonObject upload);

    QJsonObject submission;
    QJsonObject upload;
    QString csrf_token;
    QString html;
};

#endif // CFSUBMITTER_H
