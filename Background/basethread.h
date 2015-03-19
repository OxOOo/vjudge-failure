#ifndef BASETHREAD_H
#define BASETHREAD_H

#include "Config.h"
#include <QThread>

class BaseThread : public QThread
{
    Q_OBJECT
public:
    explicit BaseThread(QObject *parent = 0);

    void setAccount(QString username,QString password);

    void setInfo(QString token,QString uploadSubmissionAddress,QString uploadTaskAddress,QString uploadTestAddress,QString uploadAddress);

    virtual bool isNeedAccount() = 0;
signals:
    void updateInfo(QString);

    void updateStatus(QString);

    void errorMessage(QByteArray);
public slots:

protected:
    QString username,password;
    QString token;
    QString uploadSubmissionAddress;
    QString uploadTaskAddress;
    QString uploadTestAddress;
    QString uploadAddress;
};

#endif // BASETHREAD_H
