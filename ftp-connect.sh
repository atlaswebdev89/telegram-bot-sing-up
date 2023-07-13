#!/usr/bin/env bash

USER="atlas89"
PASSWORD="coredallas89"
HOST="bastion.telegram-brestburger.by"
REMOTE_PATH="bastion.telegram-brestburger.by"

if [[ -z USER && -z PASSWORD && -z LOCAL_PATH && -z REMOTE_HOST && -z REMOTE_PATH ]]
then
	echo "Не установлены все переменные для скрипта"
	exit
fi

if [[ -z $(hash ftp 2>&1) ]];
then
	ftp $USER@$HOST:/$REMOTE_PATH/
else 
	echo "Not found command ftp";
fi