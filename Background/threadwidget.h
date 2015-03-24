#ifndef THREADWIDGET_H
#define THREADWIDGET_H

#include "basethread.h"
#include "spider/cfspider.h"
#include "spider/thuspider.h"
#include "spider/pojspider.h"
#include "spider/hduspider.h"
#include "spider/bzojspider.h"
#include "submitter/cfsubmitter.h"
#include "submitter/thusubmitter.h"
#include "submitter/pojsubmitter.h"
#include "submitter/hdusubmitter.h"
#include "submitter/bzojsubmitter.h"
#include "test/test.h"

#include <QWidget>
#include <QJsonObject>
#include <QLabel>

class ThreadWidget : public QWidget
{
    Q_OBJECT
public:
    explicit ThreadWidget(QJsonObject mission, QWidget *parent = 0);

    void setInfo(QString token,QString uploadSubmissionAddress,QString uploadTaskAddress,QString uploadTestAddress,QString uploadAddress);

    QJsonObject getMission();

    void setAccount(QString username,QString password);

signals:
    void finishMission();

    void askForAccount(QString);

    void releaseAccount(QString,QString);

    void appendError(QByteArray);
public slots:
    void updateInfo(QString str);

    void updateStatus(QString str);

    void errorMessage(QByteArray msg);

    void finished();
private:
    QJsonObject mission;

    QLabel *info,*status;
    BaseThread *thread;
    QString username,OJ;
};

#endif // THREADWIDGET_H
