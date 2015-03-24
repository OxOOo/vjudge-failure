#include "threadwidget.h"

#include <QHBoxLayout>
#include <QFile>
#include <QDateTime>
#include <QJsonDocument>
#include <QDebug>

ThreadWidget::ThreadWidget(QJsonObject mission, QWidget *parent) :
    QWidget(parent),mission(mission)
{
    info = new QLabel(this);
    status = new QLabel(this);
    status->setText("正在等待获取账号");

    QHBoxLayout *layout = new QHBoxLayout(this);
    layout->addWidget(new QLabel("信息：",this));
    layout->addWidget(info);
    layout->addSpacerItem(new QSpacerItem(20,20));
    layout->addWidget(new QLabel("状态：",this));
    layout->addWidget(status);
    layout->addSpacerItem(new QSpacerItem(20,20));

    thread = NULL;
    if(mission["result"] == "task")
    {
        if(mission["task"].toObject()["originOJ"] == "CF")
            thread = new CFSpider(mission["task"].toObject()["originID"].toString(),this);
        if(mission["task"].toObject()["originOJ"] == "THU")
            thread = new THUSpider(mission["task"].toObject()["originID"].toString(),this);
        if(mission["task"].toObject()["originOJ"] == "POJ")
            thread = new POJSpider(mission["task"].toObject()["originID"].toString(),this);
        if(mission["task"].toObject()["originOJ"] == "HDU")
            thread = new HDUSpider(mission["task"].toObject()["originID"].toString(),this);
        if(mission["task"].toObject()["originOJ"] == "BZOJ")
            thread = new BZOJSpider(mission["task"].toObject()["originID"].toString(),this);
        OJ = mission["task"].toObject()["originOJ"].toString();
    }
    if(mission["result"] == "submission")
    {
        if(mission["submission"].toObject()["originOJ"] == "CF")
            thread = new CFSubmitter(mission["submission"].toObject(),this);
        if(mission["submission"].toObject()["originOJ"] == "THU")
            thread = new THUSubmitter(mission["submission"].toObject(),this);
        if(mission["submission"].toObject()["originOJ"] == "POJ")
            thread = new POJSubmitter(mission["submission"].toObject(),this);
        if(mission["submission"].toObject()["originOJ"] == "HDU")
            thread = new HDUSubmitter(mission["submission"].toObject(),this);
        if(mission["submission"].toObject()["originOJ"] == "BZOJ")
            thread = new BZOJSubmitter(mission["submission"].toObject(),this);
        OJ = mission["submission"].toObject()["originOJ"].toString();
    }
    if(mission["result"] == "test")
    {
        thread = new Test(mission["test"].toObject(),this);
    }

    if(thread != NULL)
    {
        connect(thread,SIGNAL(updateInfo(QString)),SLOT(updateInfo(QString)));
        connect(thread,SIGNAL(updateStatus(QString)),SLOT(updateStatus(QString)));
        connect(thread,SIGNAL(errorMessage(QByteArray)),SLOT(errorMessage(QByteArray)));
        connect(thread,SIGNAL(finished()),SLOT(finished()));
    }
}

void ThreadWidget::setInfo(QString token,QString uploadSubmissionAddress,QString uploadTaskAddress,QString uploadTestAddress,QString uploadAddress)
{
    qDebug()<<"setInfo"<<endl;
    if(thread == NULL)
    {
        qDebug()<<"setInfo"<<endl;
        emit finishMission();
        return;
    }
    thread->setInfo(token,uploadSubmissionAddress,uploadTaskAddress,uploadTestAddress,uploadAddress);
    if(thread->isNeedAccount())
        emit askForAccount(OJ);
    else thread->start();
}

QJsonObject ThreadWidget::getMission()
{
    return mission;
}

void ThreadWidget::setAccount(QString username, QString password)
{
    qDebug()<<"setAccount"<<endl;
    this->username = username;
    thread->setAccount(username,password);
    thread->start();
}

void ThreadWidget::updateInfo(QString str)
{
    info->setText(str);
}

void ThreadWidget::updateStatus(QString str)
{
    status->setText(str);
}

void ThreadWidget::errorMessage(QByteArray msg)
{
    QByteArray buffer;
    buffer.append("thread error:\n");
    QJsonDocument doc;
    doc.setObject(mission);
    buffer.append(doc.toJson() + "\n");
    buffer.append(msg);
    emit appendError(msg);
}

void ThreadWidget::finished()
{
    qDebug()<<"finish"<<endl;
    if(thread->isNeedAccount())
        emit releaseAccount(OJ,username);
    emit finishMission();
}
