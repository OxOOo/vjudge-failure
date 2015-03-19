#ifndef CONFIG_H
#define CONFIG_H

#include <QString>
#include <QByteArray>
#include <QCryptographicHash>
#include <QRegExp>
#include <QDateTime>
#include <QDir>
#include <QApplication>

class TOOL
{
private:
    TOOL();
public:
    static QString getToken(QString token)
    {
        QString A = QDateTime::currentDateTime().toString("yyyyMMddHHmm");
        QString B = TOOL::md5(TOOL::md5(A)+token);
        return "?key="+A+"&token="+B;
    }
    static QString md5(QString str)
    {
        QString md5;
        QByteArray bb;
        bb = QCryptographicHash::hash(str.toLatin1(),QCryptographicHash::Md5);
        md5.append(bb.toHex());
        return md5;
    }
    static QString regFind(QString str,QString pattern,bool Minimal = false)
    {
        QRegExp reg(pattern);
        reg.setMinimal(Minimal);
        reg.indexIn(str);
        return reg.cap(1);
    }
    static QString toXMLEncoding(QString str)
    {
        QString result;
        for(int i=0;i<str.length();i++)
        {
            if(str.at(i)=='&')result += "&amp;";
            else if(str.at(i)=='<')result += "&lt;";
            else if(str.at(i)=='>')result += "&gt;";
            else if(str.at(i)=='\"')result += "&quot;";
            else if(str.at(i)=='\'')result += "&apos;";
            else if(str.at(i)=='\t')result += "&#x0009;";
            else if(str.at(i)=='\r')result += "&#x000D;";
            else if(str.at(i)=='\n')result += "&#x000A;";
            else result += str.at(i);
        }
        return result;
    }
    static QString randomString()
    {
        const QString sets = "qwertyuioplkjhgfdsazxcvbnmQWERTYUIOPLKJHGFDSAZXCVBNM123456789";
        qsrand(QTime(0,0).msecsTo(QTime::currentTime()));
        QString ret;
        for(int length = qrand()%50+1;length;length--)
            ret += sets.at(qrand()%sets.length());
        return ret;
    }
    static QString tempDir()
    {
        QDir temp(qApp->applicationDirPath() + "/temp");
        if(!temp.exists())
            temp.mkpath(temp.absolutePath());
        return temp.absolutePath();
    }
};

#endif // CONFIG_H
