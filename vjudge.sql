DROP TABLE IF EXISTS `contests`;CREATE TABLE `contests` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`title` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
`startTime` datetime NOT NULL,
`endTime` datetime NOT NULL,
`duration` int(11) NOT NULL,
`signUp` int(11) NOT NULL DEFAULT 0,
`mode` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
`managerID` int(11) NOT NULL,
PRIMARY KEY (`id`) 
)
ENGINE=InnoDB
DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci;
DROP TABLE IF EXISTS `cproblem`;
CREATE TABLE `cproblem` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`pid` int(11) NOT NULL,
`cid` int(11) NOT NULL,
`number` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
`acceptedCount` int(11) NOT NULL DEFAULT 0,
`submitedCount` int(11) NOT NULL DEFAULT 0,
PRIMARY KEY (`id`) 
)
ENGINE=InnoDB
DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci;
DROP TABLE IF EXISTS `cuser`;
CREATE TABLE `cuser` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`cid` int(11) NOT NULL,
`uid` int(11) NOT NULL,
`totalScore` int(11) NOT NULL DEFAULT 0,
`solveLog` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
PRIMARY KEY (`id`) 
)
ENGINE=InnoDB
DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci;
DROP TABLE IF EXISTS `problems`;
CREATE TABLE `problems` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`title` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
`description` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL,
`input` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL,
`output` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL,
`sample` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL,
`datarange` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL,
`hint` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL,
`translate` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL,
`timelimit` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '0S',
`memorylimit` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '0MB',
`url` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
`statusUrl` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
`originOJ` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
`originID` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
`source` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
`acceptedCount` int(11) NOT NULL DEFAULT 0,
`submitedCount` int(11) NOT NULL DEFAULT 0,
`LLFormat` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
`cid` int(11) NOT NULL DEFAULT 0,
PRIMARY KEY (`id`) 
)
ENGINE=InnoDB
DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci;
DROP TABLE IF EXISTS `submissions`;
CREATE TABLE `submissions` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`pid` int(11) NOT NULL,
`cid` int(11) NOT NULL,
`uid` int(11) NOT NULL,
`result` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
`score` int(11) NULL DEFAULT 0,
`accepted` int(11) NOT NULL DEFAULT 0,
`time` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '0ms',
`memory` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '0KB',
`language` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
`length` int(11) NOT NULL DEFAULT 0,
`submitDatetime` datetime NOT NULL,
`source` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
`judgeLog` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL,
`finish` int(11) NOT NULL DEFAULT 0,
`task` int(11) NOT NULL DEFAULT 0,
`afresh` int(11) NOT NULL DEFAULT 0,
PRIMARY KEY (`id`) 
)
ENGINE=InnoDB
DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci;
DROP TABLE IF EXISTS `tasks`;
CREATE TABLE `tasks` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`uid` int(11) NOT NULL,
`OJ` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
`number` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
`buildDatetime` datetime NOT NULL,
`finishDatetime` datetime NULL DEFAULT NULL,
`finish` int(11) NOT NULL DEFAULT 0,
`task` int(11) NOT NULL DEFAULT 0,
PRIMARY KEY (`id`) 
)
ENGINE=InnoDB
DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci;
DROP TABLE IF EXISTS `tests`;
CREATE TABLE `tests` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`ip` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
`language` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
`source` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
`input` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
`error` int(11) NOT NULL DEFAULT 0,
`result` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL,
`datetime` datetime NOT NULL,
`task` int(11) NOT NULL DEFAULT 0,
`finish` int(11) NOT NULL DEFAULT 0,
PRIMARY KEY (`id`) 
)
ENGINE=InnoDB
DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci;
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`username` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
`password` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
`realname` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
`nickname` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
`registerDatetime` datetime NOT NULL,
`lastLoginDatetime` datetime NULL DEFAULT NULL,
`acceptedCount` int(11) NOT NULL DEFAULT 0,
`submitedCount` int(11) NOT NULL DEFAULT 0,
`isAdmin` int(11) NOT NULL DEFAULT 0,
`totalScore` int(11) NOT NULL DEFAULT 0,
PRIMARY KEY (`id`) 
)
ENGINE=InnoDB
DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci;

