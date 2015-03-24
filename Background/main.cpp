#include "mainwindow.h"
#include <QApplication>
#include <QDebug>
#include <QFile>
#include <QTextStream>
#include <QMessageBox>

int main(int argc, char *argv[])
{
    QApplication a(argc, argv);

    QFile file("begin");
    if(file.open(QFile::WriteOnly | QFile::Text))
    {
        QTextStream fout(&file);
        fout << a.applicationPid() << endl;
        file.close();
    }else{
        QMessageBox::warning(0, "出错", "无法打开begin文件");
        return 0;
    }

    MainWindow w;
    w.show();
    if(a.arguments().contains("s") || a.arguments().contains("start") || a.arguments().contains("-s") || a.arguments().contains("-start"))
        w.start();

    return a.exec();
}
