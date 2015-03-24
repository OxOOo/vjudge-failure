#include "mainwindow.h"
#include "ui_mainwindow.h"
#include <QFile>
#include <QInputDialog>
#include <QMessageBox>

#include <QJsonDocument>
#include <QJsonArray>
#include <QJsonObject>
#include <QJsonValue>

#include <QRadioButton>
#include <QDebug>
#include <QDateTime>
#include <QNetworkRequest>

MainWindow::MainWindow(QWidget *parent) :
    QMainWindow(parent),
    ui(new Ui::MainWindow)
{
    ui->setupUi(this);

    readSettings();

    initUI();

    connect(ui->tokenLineEdit,SIGNAL(editingFinished()),SLOT(settingsChange()));
    connect(ui->getSubmissionLineEdit,SIGNAL(editingFinished()),SLOT(settingsChange()));
    connect(ui->uploadSubmissionLineEdit,SIGNAL(editingFinished()),SLOT(settingsChange()));
    connect(ui->getTaskLineEdit,SIGNAL(editingFinished()),SLOT(settingsChange()));
    connect(ui->uploadTaskLineEdit,SIGNAL(editingFinished()),SLOT(settingsChange()));
    connect(ui->uploadLineEdit,SIGNAL(editingFinished()),SLOT(settingsChange()));
    connect(ui->threadSpinBox,SIGNAL(valueChanged(int)),SLOT(settingsChange()));

    timer = new QTimer();
    timer->setInterval(1000 * 10);
    connect(timer,SIGNAL(timeout()),SLOT(timeout()));
    manager = new QNetworkAccessManager(this);
    connect(manager,SIGNAL(finished(QNetworkReply*)),SLOT(replyFinished(QNetworkReply*)));
    trayIcon = new QSystemTrayIcon(this);
    trayIcon->setIcon(QIcon("://img/logo.png"));
    connect(trayIcon,SIGNAL(activated(QSystemTrayIcon::ActivationReason)),SLOT(trayActivated()));
    trayIcon->show();
}

MainWindow::~MainWindow()
{
    delete ui;
}

void MainWindow::start()
{
    on_startAction_triggered();
}

void MainWindow::closeEvent(QCloseEvent *)
{
    writeSettings();
}

void MainWindow::readSettings()
{
    qDebug()<<"readSettings"<<endl;

    QFile file("CONFIG");
    if(file.open(QFile::ReadOnly)){
        QJsonDocument doc = QJsonDocument::fromBinaryData(qUncompress(file.readAll()));
        settings = doc.object();
    }
    if(!settings.contains("token"))settings.insert("token","");
    if(!settings.contains("getSubmissionAddress"))settings.insert("getSubmissionAddress","");
    if(!settings.contains("uploadSubmissionAddress"))settings.insert("uploadSubmissionAddress","");
    if(!settings.contains("getTaskAddress"))settings.insert("getTaskAddress","");
    if(!settings.contains("uploadTaskAddress"))settings.insert("uploadTaskAddress","");
    if(!settings.contains("getTestAddress"))settings.insert("getTestAddress","");
    if(!settings.contains("uploadTestAddress"))settings.insert("uploadTestAddress","");
    if(!settings.contains("uploadAddress"))settings.insert("uploadAddress","");
    if(!settings.contains("threadNum"))settings.insert("threadNum",1);
    if(!settings.contains("account"))settings.insert("account",QJsonObject());
    if(!settings.contains("tab"))settings.insert("tab",0);
    if(!settings.contains("mission"))settings.insert("mission",QJsonArray());
}

void MainWindow::writeSettings()
{
    qDebug()<<"writeSettings"<<endl;

    settings["tab"]=ui->tabWidget->currentIndex();
    QJsonArray missions;
    for(int i=0;i<threadWidgetList.size();i++)
        missions.push_back(threadWidgetList.at(i)->getMission());
    for(int i=0;i<waitMissionList.size();i++)
        missions.push_back(waitMissionList.at(i));
    settings["mission"]=missions;

    QFile file("CONFIG");
    if(file.open(QFile::WriteOnly)){
        QJsonDocument doc;
        doc.setObject(settings);
        file.write(qCompress(doc.toBinaryData()));
    }
}

void MainWindow::initUI()
{
    qDebug()<<"initUI"<<endl;

    ui->tokenLineEdit->setText(settings.value("token").toString());
    ui->getSubmissionLineEdit->setText(settings.value("getSubmissionAddress").toString());
    ui->uploadSubmissionLineEdit->setText(settings.value("uploadSubmissionAddress").toString());
    ui->getTaskLineEdit->setText(settings.value("getTaskAddress").toString());
    ui->uploadTaskLineEdit->setText(settings.value("uploadTaskAddress").toString());
    ui->getTestLineEdit->setText(settings.value("getTestAddress").toString());
    ui->uploadTestLineEdit->setText(settings.value("uploadTestAddress").toString());
    ui->uploadLineEdit->setText(settings.value("uploadAddress").toString());
    ui->threadSpinBox->setValue(settings.value("threadNum").toInt());
    ui->tabWidget->setCurrentIndex(settings["tab"].toInt());
    QRadioButton *first = NULL;
    foreach(QString oj,settings.value("account").toObject().keys())
    {
        QRadioButton *button = new QRadioButton(oj,this);
        ui->OJLayout->addWidget(button);
        connect(button,SIGNAL(clicked(bool)),SLOT(OJChange(bool)));
        if(first == NULL)
            first = button;
    }
    if(first != NULL){
        first->setChecked(true);
        currentOJ = first->text();
        initAccount();
    }
}

void MainWindow::settingsChange()
{
    qDebug()<<"settingsChange"<<endl;

    settings["token"]=ui->tokenLineEdit->text();
    settings["getSubmissionAddress"]=ui->getSubmissionLineEdit->text();
    settings["uploadSubmissionAddress"]=ui->uploadSubmissionLineEdit->text();
    settings["getTaskAddress"]=ui->getTaskLineEdit->text();
    settings["uploadTaskAddress"]=ui->uploadTaskLineEdit->text();
    settings["getTestAddress"]=ui->getTestLineEdit->text();
    settings["uploadTestAddress"]=ui->uploadTestLineEdit->text();
    settings["uploadAddress"]=ui->uploadLineEdit->text();
    settings["threadNum"]=ui->threadSpinBox->value();
}

void MainWindow::OJChange(bool checked)
{
    qDebug()<<"OJChange"<<endl;

    if(checked == false)return;
    QRadioButton *button = static_cast<QRadioButton*>(sender());
    if(button == NULL)return;
    currentOJ = button->text();
    initAccount();
}

void MainWindow::accountChange()
{
    qDebug()<<"accountChange"<<endl;

    QJsonArray accounts;
    for(int i=0;i<accountList.size();i++)
        if(accountList.at(i)->username->text().trimmed() != "" && accountList.at(i)->password->text().trimmed() != "")
        {
            QJsonObject obj;
            obj.insert("username",accountList.at(i)->username->text());
            obj.insert("password",accountList.at(i)->password->text());
            accounts.push_back(obj);
        }
    QJsonObject account = settings["account"].toObject();
    account.insert(currentOJ,accounts);
    settings["account"] = account;
}

void MainWindow::accountDelete()
{
    qDebug()<<"accountDelete"<<endl;

    for(int i=0;i<accountList.size();i++)
        if(sender()==accountList.at(i)->button)
        {
            accountList.at(i)->deleteLater();
            accountList.removeAt(i);
            accountChange();
            return;
        }
}

void MainWindow::initAccount()
{
    qDebug()<<"initAccount"<<endl;

    for(int i=0;i<accountList.size();i++)
        accountList.at(i)->deleteLater();
    accountList.clear();
    QJsonArray accounts = settings.value("account").toObject().value(currentOJ).toArray();
    for(int i=0;i<accounts.size();i++){
        QJsonObject account = accounts.at(i).toObject();
        AccountWidget *widget = new AccountWidget(this);
        accountList.push_back(widget);
        widget->username->setText(account.value("username").toString());
        widget->password->setText(account.value("password").toString());
        connect(widget->button,SIGNAL(clicked()),SLOT(accountDelete()));
        connect(widget->username,SIGNAL(editingFinished()),SLOT(accountChange()));
        connect(widget->password,SIGNAL(editingFinished()),SLOT(accountChange()));
        ui->accountLayout->addWidget(widget);
    }
    ui->ojLabel->setText(currentOJ);
    ui->addAccountToolButton->setEnabled(true);
}

void MainWindow::on_addToolButton_clicked()
{
    QString name = QInputDialog::getText(this,"请输入题库名称","请输入题库名称");
    if(name == "")return;
    QJsonObject account = settings["account"].toObject();
    account[name] = QJsonArray();
    settings["account"] = account;

    QRadioButton *button = new QRadioButton(name,this);
    ui->OJLayout->addWidget(button);
    connect(button,SIGNAL(clicked(bool)),SLOT(OJChange(bool)));
    button->setChecked(true);
    currentOJ = name;
    initAccount();
}

void MainWindow::on_addAccountToolButton_clicked()
{
    AccountWidget *widget = new AccountWidget(this);
    accountList.push_back(widget);
    connect(widget->button,SIGNAL(clicked()),SLOT(accountDelete()));
    connect(widget->username,SIGNAL(editingFinished()),SLOT(accountChange()));
    connect(widget->password,SIGNAL(editingFinished()),SLOT(accountChange()));
    ui->accountLayout->addWidget(widget);
}

void MainWindow::on_startAction_triggered()
{
    ui->startAction->setEnabled(!ui->startAction->isEnabled());
    ui->stopAction->setEnabled(!ui->stopAction->isEnabled());
    ui->settingsTab->setEnabled(false);
    ui->accountTab->setEnabled(false);

    QJsonArray missions = settings["mission"].toArray();
    for(int i=0;i<missions.size();i++)
        addMission(missions.at(i).toObject());
    settings["mission"]=QJsonArray();
    timer->start();
    timeout();
}

void MainWindow::on_stopAction_triggered()
{
    ui->startAction->setEnabled(!ui->startAction->isEnabled());
    ui->stopAction->setEnabled(!ui->stopAction->isEnabled());
    ui->settingsTab->setEnabled(true);
    ui->accountTab->setEnabled(true);

    timer->stop();
}

void MainWindow::timeout()
{
    qDebug()<<"timeout"<<endl;

    if(waitMissionList.size() == 0)
        sendRequest();
}

void MainWindow::replyFinished(QNetworkReply *reply)
{
    qDebug()<<"replyFinished"<<endl;
    if(reply->error() != QNetworkReply::NoError)
    {
        QByteArray buffer;
        buffer.append("replyFinished\n");
        buffer.append(reply->errorString().toUtf8());
        appendError(buffer);
        reply->deleteLater();
        return;
    }

    QByteArray data = reply->readAll();
    QJsonParseError error;
    QJsonDocument doc = QJsonDocument::fromJson(data,&error);
    if(error.error != QJsonParseError::NoError)
    {
        QByteArray buffer;
        buffer.append((reply->request().url().toString() + "\n").toUtf8());
        buffer.append(data);
        appendError(buffer);
        reply->deleteLater();
        return;
    }
    QJsonObject mission = doc.object();
    if(mission.contains("result") && mission["result"]!="none")
    {
        waitMissionList.push_back(mission);
        dealWaitMission();
        timeout();
    }
    reply->deleteLater();
}

void MainWindow::sendRequest()
{
    qDebug()<<"sendRequest"<<endl;

    if(ui->startAction->isEnabled())return;
    QNetworkRequest request;
    request.setUrl(QUrl(settings["getSubmissionAddress"].toString()+TOOL::getToken(settings["token"].toString())));
    manager->get(request)->ignoreSslErrors();
    request.setUrl(QUrl(settings["getTaskAddress"].toString()+TOOL::getToken(settings["token"].toString())));
    manager->get(request);
    request.setUrl(QUrl(settings["getTestAddress"].toString()+TOOL::getToken(settings["token"].toString())));
    manager->get(request);
}

void MainWindow::dealWaitMission()
{
    if(threadWidgetList.size() < settings["threadNum"].toInt() && waitMissionList.size() != 0)
    {
        QJsonObject mission = waitMissionList.first();
        waitMissionList.pop_front();
        addMission(mission);
        dealWaitMission();
    }
}

void MainWindow::addMission(QJsonObject mission)
{
    qDebug()<<"addMission"<<endl;

    qDebug()<<mission<<endl;

    ThreadWidget *widget = new ThreadWidget(mission,this);
    threadWidgetList.push_back(widget);
    connect(widget,SIGNAL(finishMission()),SLOT(finishMission()));
    connect(widget,SIGNAL(askForAccount(QString)),SLOT(askForAccount(QString)));
    connect(widget,SIGNAL(releaseAccount(QString,QString)),SLOT(releaseAccount(QString,QString)));
    connect(widget,SIGNAL(appendError(QByteArray)),SLOT(appendError(QByteArray)));
    ui->threadLayout->addWidget(widget);
    widget->setInfo(settings["token"].toString(),settings["uploadSubmissionAddress"].toString(),settings["uploadTaskAddress"].toString(),settings["uploadTestAddress"].toString(),settings["uploadAddress"].toString());
}

void MainWindow::finishMission()
{
    ThreadWidget *widget = static_cast<ThreadWidget*>(sender());
    if(widget == NULL)return;
    threadWidgetList.removeAll(widget);
    widget->deleteLater();
    dealWaitMission();
    timeout();
}

void MainWindow::appendError(QByteArray msg)
{
    QString filename = QDateTime::currentDateTime().toString("yyyy-MM-dd HH-mm-ss") + ".log";
    QFile file(filename);
    if(file.open(QFile::WriteOnly | QFile::Text))
    {
        file.write(msg);
        file.close();
        msgInfo += "出现错误，储存在：" + filename + "\n";
    }else{
        msgInfo +=  "无法储存错误信息\n";
    }

    ui->errorAction->setEnabled(true);
    setWindowTitle("出现错误，请点击“错误”按钮查看错误信息");
}

void MainWindow::askForAccount(QString OJ)
{
    qDebug()<<"askForAccount "<<OJ<<endl;
    ThreadWidget* widget = static_cast<ThreadWidget*>(sender());
    if(widget == NULL)return;
    waitAccountList.push_back(QPair<QString,ThreadWidget*>(OJ,widget));
    dealAccount();
}

void MainWindow::releaseAccount(QString OJ, QString username)
{
    usingAccountList.removeAll(QPair<QString,QString>(OJ,username));
    dealAccount();
}

void MainWindow::dealAccount()
{
    qDebug()<<"dealAccount"<<endl;
    for(int i=0;i<waitAccountList.size();i++)
    {
        QString username = "",password = "";
        if(settings["account"].toObject().contains(waitAccountList.at(i).first)){
            QJsonArray accounts = settings["account"].toObject()[waitAccountList.at(i).first].toArray();
            for(int j=0;j<accounts.size();j++){
                QJsonObject account = accounts.at(j).toObject();
                if(!usingAccountList.contains(QPair<QString,QString>(waitAccountList.at(i).first,account["username"].toString())))
                    {
                        username = account["username"].toString();
                        password = account["password"].toString();
                    }
            }
        }
        if(username != ""){
            usingAccountList.push_back(QPair<QString,QString>(waitAccountList.at(i).first,username));
            waitAccountList.at(i).second->setAccount(username,password);
            waitAccountList.removeAt(i);
            i--;
        }
    }
}

void MainWindow::trayActivated()
{
    show();
}

void MainWindow::on_trayAction_triggered()
{
    hide();
}

void MainWindow::on_exitAction_triggered()
{
    if(ui->stopAction->isEnabled())
        on_stopAction_triggered();
    close();
}


void MainWindow::on_errorAction_triggered()
{
    QMessageBox::information(this, "错误信息", msgInfo);
}
