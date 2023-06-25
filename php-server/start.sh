#!/usr/bin/env bash

tz=$TZ;
if [[ -n $tz ]];
then 
	ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone
fi

# Проверяем режим запуска. Проверяем переменную окружения
if [[ ! -v ${MODE} ]] && [[ ! -z ${MODE} ]];
then
		if [[ ${MODE} == "webhooks" ]]
			then
				php-fpm8.2 -F
			elif [[ ${MODE} == "polling" ]]
			then
				php index.php
			fi
fi