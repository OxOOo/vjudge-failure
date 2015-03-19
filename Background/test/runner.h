#ifndef RUNNER_H
#define RUNNER_H

#include "../Config.h"

#include <QObject>
#include <QProcess>
#include <QString>

class Runner : public QObject
{
    Q_OBJECT
public:
    explicit Runner(QObject *parent = 0);

    void start(QString program,QStringList arguments,QByteArray input,int waitMsecs = 10000);

    bool hasError(){return _hasError;}

    QByteArray output(QProcess::ProcessChannel channel = QProcess::StandardOutput);

    QProcess::ProcessError errorStatus(){return process->error();}

    int	exitCode(){return process->exitCode();}
signals:

public slots:
    void error();
private:
    QProcess *process;
    bool _hasError;
};

#endif // RUNNER_H
