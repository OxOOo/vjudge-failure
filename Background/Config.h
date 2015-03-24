#ifndef CONFIG_H
#define CONFIG_H

#include <QString>
#include <QByteArray>
#include <QCryptographicHash>
#include <QRegExp>
#include <QDateTime>
#include <QDir>
#include <QApplication>

namespace TOOL
{
    QString getToken(QString token);

    QString md5(QString str);

    QString regFind(QString str,QString pattern,bool Minimal = false);

    QString toXMLEncoding(QString str);

    QString randomString();

    QString tempDir();

    QString joinUrl(QString a, QString b);
}

#endif // CONFIG_H
