#include "runner.h"
#include <QDebug>

Runner::Runner(QObject *parent) :
    QObject(parent)
{
    process = new QProcess(this);
    process->setWorkingDirectory(TOOL::tempDir());
    connect(process,SIGNAL(error(QProcess::ProcessError)),SLOT(error()));
}

void Runner::start(QString program,QStringList arguments,QByteArray input,int waitMsecs)
{
    _hasError = false;
    if(process->state() == QProcess::Running)
        process->terminate();
    process->start(program,arguments);
    process->write(input);
    process->closeWriteChannel();
    if(!process->waitForFinished(waitMsecs))
    {
        _hasError = true;
        if(process->state() == QProcess::Running)
            process->kill();
    }
}

QByteArray Runner::output(QProcess::ProcessChannel channel)
{
    process->setReadChannel(channel);
    return process->readAll();
}

void Runner::error()
{
    _hasError = true;
    if(process->state() == QProcess::Running)
        process->kill();
}
