#include "basethread.h"

BaseThread::BaseThread(QObject *parent) :
    QThread(parent)
{
}

void BaseThread::setAccount(QString username, QString password)
{
    this->username = username;
    this->password = password;
}

void BaseThread::setInfo(QString token, QString uploadSubmissionAddress, QString uploadTaskAddress,QString uploadTestAddress,QString uploadAddress)
{
    this->token = token;
    this->uploadSubmissionAddress = uploadSubmissionAddress;
    this->uploadTaskAddress = uploadTaskAddress;
    this->uploadTestAddress = uploadTestAddress;
    this->uploadAddress = uploadAddress;
}
