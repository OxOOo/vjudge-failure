#include "Config.h"

#include <QUrl>
#include <QDebug>

namespace TOOL
{
    QString getToken(QString token)
    {
        QString A = QDateTime::currentDateTime().toString("yyyyMMddHHmm");
        QString B = TOOL::md5(TOOL::md5(A)+token);
        return "?key="+A+"&token="+B;
    }

    QString md5(QString str)
    {
        QString md5;
        QByteArray bb;
        bb = QCryptographicHash::hash(str.toLatin1(),QCryptographicHash::Md5);
        md5.append(bb.toHex());
        return md5;
    }

    QString regFind(QString str,QString pattern,bool Minimal)
    {
        QRegExp reg(pattern);
        reg.setMinimal(Minimal);
        reg.indexIn(str);
        return reg.cap(1);
    }

    QString toXMLEncoding(QString str)
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

    QString randomString()
    {
        const QString sets = "qwertyuioplkjhgfdsazxcvbnmQWERTYUIOPLKJHGFDSAZXCVBNM123456789";
        qsrand(QTime(0,0).msecsTo(QTime::currentTime()));
        QString ret;
        for(int length = qrand()%50+1;length;length--)
            ret += sets.at(qrand()%sets.length());
        return ret;
    }

    QString tempDir()
    {
        QDir temp(qApp->applicationDirPath() + "/temp");
        if(!temp.exists())
            temp.mkpath(temp.absolutePath());
        return temp.absolutePath();
    }

    QString joinUrl(QString a, QString b)
    {
        QUrl url(a);
        if(!b.isEmpty() && b.at(0) == '/')
            url.setPath(b);
        else url = QUrl(url.url() + "/" + b);

        QString path = url.path();
        while(path.startsWith("/.."))
            path = path.right(path.length() - 3);
        url.setPath(path);

        return url.url();
    }
}
