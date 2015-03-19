#-------------------------------------------------
#
# Project created by QtCreator 2014-11-17T11:37:32
#
#-------------------------------------------------

QT       += core gui
QT       += network

greaterThan(QT_MAJOR_VERSION, 4): QT += widgets

TARGET = Background
TEMPLATE = app


SOURCES += main.cpp\
        mainwindow.cpp \
    threadwidget.cpp \
    basethread.cpp \
    mynetwork.cpp \
    spider/thuspider.cpp \
    spider/cfspider.cpp \
    submitter/cfsubmitter.cpp \
    submitter/thusubmitter.cpp \
    spider/pojspider.cpp \
    submitter/pojsubmitter.cpp \
    spider/hduspider.cpp \
    submitter/hdusubmitter.cpp \
    spider/bzojspider.cpp \
    submitter/bzojsubmitter.cpp \
    test/test.cpp \
    test/runner.cpp

HEADERS  += mainwindow.h \
    threadwidget.h \
    Config.h \
    basethread.h \
    mynetwork.h \
    spider/thuspider.h \
    spider/cfspider.h \
    submitter/cfsubmitter.h \
    submitter/thusubmitter.h \
    spider/pojspider.h \
    submitter/pojsubmitter.h \
    spider/hduspider.h \
    submitter/hdusubmitter.h \
    spider/bzojspider.h \
    submitter/bzojsubmitter.h \
    test/test.h \
    test/runner.h

FORMS    += mainwindow.ui

RESOURCES += \
    Resource.qrc

RC_ICONS = img/logo.ico
