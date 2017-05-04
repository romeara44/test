#!/bin/bash

THESITE="hipaa-carosh"
THEDB="rockyhil_hipaa"
THEDBUSER="rockyhil_hipaa"
THEDBPW="dZe#8JO7#9gw"
THEDATE=`date +%d_%m_%y_%H_%M`
THEDAY=`date +%d`
THEMONTH=`date +%m`

mysqldump -u $THEDBUSER -p${THEDBPW} $THEDB > backup.sql
tar -pczf /home/rockyhil/backup/hipaa-carosh/files/dbbackup_${THEDB}_${THEDATE}.sql.tar.gz backup.sql
rm backup.sql

tar czf /home/rockyhil/backup/hipaa-carosh/files/sitebackup_${THESITE}_${THEDATE}.tar -C / /home/rockyhil/public_html/hipaa
gzip /home/rockyhil/backup/hipaa-carosh/files/sitebackup_${THESITE}_${THEDATE}.tar

if [ $THEDAY = 08 ] || [ $THEDAY = 15 ] || [ $THEDAY = 22 ]
then
	cp /home/rockyhil/backup/hipaa-carosh/files/dbbackup_${THEDB}_${THEDATE}.sql.tar.gz /home/rockyhil/backup/hipaa-carosh/files/weeks/dbbackup_${THEDB}_${THEDATE}.sql.tar.gz
	cp /home/rockyhil/backup/hipaa-carosh/files/sitebackup_${THESITE}_${THEDATE}.tar.gz /home/rockyhil/backup/hipaa-carosh/files/weeks/sitebackup_${THESITE}_${THEDATE}.tar
fi

if [ $THEDAY = 01 ]
then
	if [ $THEMONTH = 01 ] || [ $THEMONTH = 04 ] || [ $THEMONTH = 07 ] || [ $THEMONTH = 10 ]
	then
		cp /home/rockyhil/backup/hipaa-carosh/files/dbbackup_${THEDB}_${THEDATE}.sql.tar.gz /home/rockyhil/backup/hipaa-carosh/files/permanent/dbbackup_${THEDB}_${THEDATE}.sql.tar.gz
		cp /home/rockyhil/backup/hipaa-carosh/files/sitebackup_${THESITE}_${THEDATE}.tar.gz /home/rockyhil/backup/hipaa-carosh/files/permanent/sitebackup_${THESITE}_${THEDATE}.tar		
	else
		cp /home/rockyhil/backup/hipaa-carosh/files/dbbackup_${THEDB}_${THEDATE}.sql.tar.gz /home/rockyhil/backup/hipaa-carosh/files/months/dbbackup_${THEDB}_${THEDATE}.sql.tar.gz
		cp /home/rockyhil/backup/hipaa-carosh/files/sitebackup_${THESITE}_${THEDATE}.tar.gz /home/rockyhil/backup/hipaa-carosh/files/months/sitebackup_${THESITE}_${THEDATE}.tar
	fi
fi

find /home/rockyhil/backup/hipaa-carosh/files/site* -mtime +7 -exec rm {} \;
find /home/rockyhil/backup/hipaa-carosh/files/db* -mtime +7 -exec rm {} \;
find /home/rockyhil/backup/hipaa-carosh/files/weeks/site* -mtime +30 -exec rm {} \;
find /home/rockyhil/backup/hipaa-carosh/files/weeks/db* -mtime +30 -exec rm {} \;
find /home/rockyhil/backup/hipaa-carosh/files/months/site* -mtime +90 -exec rm {} \;
find /home/rockyhil/backup/hipaa-carosh/files/months/db* -mtime +90 -exec rm {} \;