#ifndef MAINWINDOW_H
#define MAINWINDOW_H

#include "threadwidget.h"
#include "Config.h"
#include "mynetwork.h"

#include <QMainWindow>
#include <QJsonObject>

#include <QList>
#include <QLineEdit>
#include <QToolButton>
#include <QHBoxLayout>
#include <QLabel>
#include <QSystemTrayIcon>

#include <QTimer>
#include <QNetworkAccessManager>
#include <QNetworkReply>
#include <QPair>

class AccountWidget : public QWidget{
public:
    AccountWidget(QWidget *parent = 0):QWidget(parent){
        username = new QLineEdit(this);
        password = new QLineEdit(this);
        button = new QToolButton(this);
        password->setEchoMode(QLineEdit::Password);
        button->setIcon(QIcon("://img/remove.png"));

        QHBoxLayout *layout = new QHBoxLayout(this);
        layout->addWidget(button);
        layout->addWidget(new QLabel("账号：",this));
        layout->addWidget(username);
        layout->addWidget(new QLabel("密码：",this));
        layout->addWidget(password);
    }

    QToolButton *button;
    QLineEdit *username,*password;
};

namespace Ui {
class MainWindow;
}

class MainWindow : public QMainWindow
{
    Q_OBJECT

public:
    explicit MainWindow(QWidget *parent = 0);
    ~MainWindow();

    void start();

protected:
    void closeEvent(QCloseEvent *);
private slots:
    void OJChange(bool checked);

    void accountChange();

    void settingsChange();

    void accountDelete();

    void timeout();

    void replyFinished(QNetworkReply *reply);

    void finishMission();

    void askForAccount(QString OJ);

    void releaseAccount(QString OJ,QString username);

    void appendError(QByteArray msg);

    void trayActivated();

    void on_addToolButton_clicked();

    void on_addAccountToolButton_clicked();

    void on_startAction_triggered();

    void on_stopAction_triggered();

    void on_trayAction_triggered();

    void on_exitAction_triggered();

    void on_errorAction_triggered();

private:
    Ui::MainWindow *ui;
    QJsonObject settings;
    QList<AccountWidget*> accountList;
    QString currentOJ;
    QTimer *timer;
    QNetworkAccessManager *manager;
    QList<ThreadWidget*> threadWidgetList;
    QList<QPair<QString,ThreadWidget*> > waitAccountList;
    QList<QPair<QString,QString> > usingAccountList;
    QList<QJsonObject> waitMissionList;
    QSystemTrayIcon *trayIcon;
    QString msgInfo;

    void readSettings();

    void writeSettings();

    void initUI();

    void initAccount();

    void sendRequest();

    void addMission(QJsonObject mission);

    void dealAccount();

    void dealWaitMission();
};

#endif // MAINWINDOW_H
