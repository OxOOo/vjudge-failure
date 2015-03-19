#include "test.h"

#include <QDebug>
#include <QFile>
#include <QDir>

Test::Test(QJsonObject test, QObject *parent) :
    BaseThread(parent),test(test)
{
    qDebug()<<"Process";
}

void Test::run()
{
    emit updateInfo("Process " + test["id"].toString());
    emit updateStatus("一切正常");

    int id = test["id"].toString().toInt();
    QString language = test["language"].toString();
    if(language == "C++")
        CPlusPlus();
    else if(language == "C")
        C();
    else if(language == "Pascal")
        Pascal();
    else uploadTest(id,1,"语言非法");
}

void Test::uploadTest(int id,int error, QString result)
{
    QJsonObject upload;
    upload["id"] = id;
    upload["error"] = error;
    upload["result"] = result;
    qDebug()<<"Process upload : "<<upload;
    QByteArray postData = "test="+QJsonDocument(upload).toJson().toPercentEncoding();
    MyNetwork network;
    QString html = network.postByteArray(uploadTestAddress + TOOL::getToken(token),postData);
    if(html != "")
        emit errorMessage("\n" + html.toUtf8() + "\n" + QJsonDocument(upload).toJson());
}

void Test::CPlusPlus()
{
    int id = test["id"].toString().toInt();
    QByteArray source = test["source"].toString().toUtf8();
    QByteArray input = test["input"].toString().toUtf8();
    Runner runner;

    emit updateStatus("正在编译");
    QFile sourceFile(TOOL::tempDir() + "/Main.cpp");
    if(!sourceFile.open(QFile::WriteOnly | QFile::Text))
    {
        uploadTest(id,1,"无法创建源文件");
        return;
    }
    sourceFile.write(source);
    sourceFile.close();

    QStringList arguments;
    arguments<<"Main.cpp"<<"-o"<<"Main.exe";
    runner.start("g++.exe",arguments,QByteArray());
    if(runner.hasError() || runner.exitCode() != 0)
    {
        uploadTest(id,1,"编译失败：\n" + runner.output(QProcess::StandardError));
        return;
    }
    emit updateStatus("正在运行");
    runner.start(TOOL::tempDir() + "/Main.exe",QStringList(),input);
    if(runner.hasError())
    {
        switch(runner.errorStatus())
        {
        case QProcess::FailedToStart:uploadTest(id,1,"无法启动");break;
        case QProcess::Crashed:uploadTest(id,1,"崩溃");break;
        case QProcess::Timedout:uploadTest(id,1,"超时");break;
        case QProcess::WriteError:uploadTest(id,1,"读写错误");break;
        case QProcess::ReadError:uploadTest(id,1,"读写错误");break;
        case QProcess::UnknownError:uploadTest(id,1,"未知错误");break;
        }
        return;
    }
    if(runner.exitCode() != 0)
    {
        uploadTest(id,1,"返回值:" + QString::number(runner.exitCode()));
        return;
    }
    uploadTest(id,0,runner.output());
}

void Test::C()
{
    int id = test["id"].toString().toInt();
    QByteArray source = test["source"].toString().toUtf8();
    QByteArray input = test["input"].toString().toUtf8();
    Runner runner;

    emit updateStatus("正在编译");
    QFile sourceFile(TOOL::tempDir() + "/Main.c");
    if(!sourceFile.open(QFile::WriteOnly | QFile::Text))
    {
        uploadTest(id,1,"无法创建源文件");
        return;
    }
    sourceFile.write(source);
    sourceFile.close();

    QStringList arguments;
    arguments<<"Main.c"<<"-o"<<"Main.exe";
    runner.start("gcc.exe",arguments,QByteArray());
    if(runner.hasError() || runner.exitCode() != 0)
    {
        uploadTest(id,1,"编译失败：\n" + runner.output(QProcess::StandardError));
        return;
    }
    emit updateStatus("正在运行");
    runner.start(TOOL::tempDir() + "/Main.exe",QStringList(),input);
    if(runner.hasError())
    {
        switch(runner.errorStatus())
        {
        case QProcess::FailedToStart:uploadTest(id,1,"无法启动");break;
        case QProcess::Crashed:uploadTest(id,1,"崩溃");break;
        case QProcess::Timedout:uploadTest(id,1,"超时");break;
        case QProcess::WriteError:uploadTest(id,1,"读写错误");break;
        case QProcess::ReadError:uploadTest(id,1,"读写错误");break;
        case QProcess::UnknownError:uploadTest(id,1,"未知错误");break;
        }
        return;
    }
    if(runner.exitCode() != 0)
    {
        uploadTest(id,1,"返回值:" + QString::number(runner.exitCode()));
        return;
    }
    uploadTest(id,0,runner.output());
}

void Test::Pascal()
{
    int id = test["id"].toString().toInt();
    QByteArray source = test["source"].toString().toUtf8();
    QByteArray input = test["input"].toString().toUtf8();
    Runner runner;

    emit updateStatus("正在编译");
    QFile sourceFile(TOOL::tempDir() + "/Main.pas");
    if(!sourceFile.open(QFile::WriteOnly | QFile::Text))
    {
        uploadTest(id,1,"无法创建源文件");
        return;
    }
    sourceFile.write(source);
    sourceFile.close();

    QStringList arguments;
    arguments<<"Main.pas";
    runner.start("fpc.exe",arguments,QByteArray());
    if(runner.hasError() || runner.exitCode() != 0)
    {
        uploadTest(id,1,"编译失败：\n" + runner.output(QProcess::StandardError));
        return;
    }
    emit updateStatus("正在运行");
    runner.start(TOOL::tempDir() + "/Main.exe",QStringList(),input);
    if(runner.hasError())
    {
        switch(runner.errorStatus())
        {
        case QProcess::FailedToStart:uploadTest(id,1,"无法启动");break;
        case QProcess::Crashed:uploadTest(id,1,"崩溃");break;
        case QProcess::Timedout:uploadTest(id,1,"超时");break;
        case QProcess::WriteError:uploadTest(id,1,"读写错误");break;
        case QProcess::ReadError:uploadTest(id,1,"读写错误");break;
        case QProcess::UnknownError:uploadTest(id,1,"未知错误");break;
        }
        return;
    }
    if(runner.exitCode() != 0)
    {
        uploadTest(id,1,"返回值:" + QString::number(runner.exitCode()));
        return;
    }
    uploadTest(id,0,runner.output());
}
