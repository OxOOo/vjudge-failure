#include "mainwindow.h"
#include <QApplication>

int main(int argc, char *argv[])
{
    QApplication a(argc, argv);

    MainWindow w;
    w.show();
    if(a.arguments().contains("s") || a.arguments().contains("start") || a.arguments().contains("-s") || a.arguments().contains("-start"))
        w.start();

    return a.exec();
}
