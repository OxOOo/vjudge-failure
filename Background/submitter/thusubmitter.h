#ifndef THUSUBMITTER_H
#define THUSUBMITTER_H

#include "../basethread.h"
#include "../mynetwork.h"

#include <QJsonObject>
#include <QJsonDocument>

class THUSubmitter : public BaseThread
{
    Q_OBJECT
public:
    explicit THUSubmitter(QJsonObject submission,QObject *parent = 0);

protected:
    bool isNeedAccount(){return true;}

    void run();
signals:

public slots:

private:
    bool login(MyNetwork *network);

    bool submit(MyNetwork *network);

    void getResult(MyNetwork *network);

    bool uploadSubmission(MyNetwork *network,QJsonObject upload);

    QJsonObject submission;
    QJsonObject upload;
    QString html;
};

#endif // THUSUBMITTER_H
