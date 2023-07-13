#! /usr/bin/env bash

USER="atlas89"
PASSWORD="coredallas89"
LOCAL_PATH="~/web/telegram_bot_sing_up/app/"
REMOTE_PATH="/bastion.telegram-brestburger.by/"
REMOTE_HOST="telegram-brestburger.by"

if [[ -z USER && -z PASSWORD && -z LOCAL_PATH && -z REMOTE_HOST && -z REMOTE_PATH ]]
then
	echo "Не установлены все переменные для скрипта"
	exit
fi

# Проверить наличие команды в системе
# 2>&1 перенаправить вывод ошибок в стандартный вывод что сработало условие
if [[ -z $(hash  ncftp 2>&1) ]]
then
	ncftpput -R -v -u ${USER} -p${PASSWORD} ${REMOTE_HOST} ${REMOTE_PATH} ${LOCAL_PATH}  
	exit
else 
	echo "Для работы скрипта необходима утилита ncftp"
	exit
fi


