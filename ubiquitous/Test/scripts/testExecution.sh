#!/bin/sh
#####################
#Konfiguration <Start>
#Konfiguration der lokalen und externen Zugänge
localSudoPassword=test
localMysqlUser=test
localMysqlPassword=test
localMysqlDatabasePrefix=open3A_
localSoftwarePath=/var/www/
localSoftwarePrefix=test_
remoteMysqlHost=192.168.8.243
remoteMysqlDatabase=open3ADev
remoteMysqlUser=test
remoteMysqlPassword=teaTGWW4bU5j2WP
remoteGitHost=192.168.8.234
remoteGitUser=gitosis
remoteGit=phynx.git
softwareUser=max
softwarePassword=Max
#Konfiguration <Ende>
#####################
timestamp=`date +%s`
timestampReadable=`date +%Y%m%d`
timestampToDelete=`date +%s`
timestampToDelete=$(($timestampToDelete - 3600 * 24 * 14))
timestampToDeleteReadable=`date -d @$timestampToDelete +%Y%m%d`
#Datenbank erstellen
echo "Erstelle Datenbank"
touch createDatabase.sql
echo "CREATE DATABASE $localMysqlDatabasePrefix$timestampReadable;" >> createDatabase.sql
mysql --user=$localMysqlUser --password=$localMysqlPassword < createDatabase.sql
rm createDatabase.sql
echo "-Erledigt"
#Datenbank herunterladen
echo "Hole externe Daten"
ssh $remoteMysqlHost mysqldump --user=$remoteMysqlUser --password=$remoteMysqlPassword $remoteMysqlDatabase > $localMysqlDatabasePrefix$timestampReadable.sql
sleep 5
echo "-Erledigt"
#Datenbank einspielen
echo "Lese Daten ein"
mysql --user=$localMysqlUser --password=$localMysqlPassword $localMysqlDatabasePrefix$timestampReadable < $localMysqlDatabasePrefix$timestampReadable.sql
sleep 5
rm $localMysqlDatabasePrefix$timestampReadable.sql
echo "-Erledigt"
#14 Tage alte Datenbank löschen
echo "Lösche veraltete Datenbank"
touch deleteDatabase.sql
echo "DROP DATABASE IF EXISTS $localMysqlDatabasePrefix$timestampToDeleteReadable;" >> deleteDatabase.sql
mysql --user=$localMysqlUser --password=$localMysqlPassword < deleteDatabase.sql
rm deleteDatabase.sql
echo "-Erledigt"
#Klonen des Git-Repos
echo "Klone Git-Repository"
git clone $remoteGitUser@$remoteGitHost:$remoteGit $localSoftwarePath$localSoftwarePrefix$timestampReadable
sleep 5
echo "-Erledigt"
#Herstellen der Datenbankverbindung
echo "Stelle Datenbankanbindung her"
mv $localSoftwarePath$localSoftwarePrefix$timestampReadable/plugins/Cloud/ $localSoftwarePath$localSoftwarePrefix$timestampReadable/plugins/_xCloud/
echo $localSudoPassword | sudo -S rm $localSoftwarePath$localSoftwarePrefix$timestampReadable/system/DBData/Installation.pfdb.php
touch $localSoftwarePath$localSoftwarePrefix$timestampReadable/system/DBData/Installation.pfdb.php
echo "<?php echo \"This is a database-file.\"; /*
host&%%%&user&%%%&password&%%%&datab&%%%&httpHost
varchar(30)&%%%&varchar(20)&%%%&varchar(20)&%%%&varchar(20)&%%%&varchar(40)                                                                                                                                                       
localhost                     &%%%&$localMysqlUser                &%%%&$localMysqlPassword              &%%%&$localMysqlDatabasePrefix$timestampReadable            &%%%&*                                       %%&&&
localhost                     &%%%&$localMysqlUser                &%%%&$localMysqlPassword              &%%%&$localMysqlDatabasePrefix$timestampReadable            &%%%&cloudData                                       %%&&&
*/ ?>" > $localSoftwarePath$localSoftwarePrefix$timestampReadable/system/DBData/Installation.pfdb.php
echo "-Erledigt"
#14 Tage alte Software löschen
echo "Lösche veraltete Software"
echo $localSudoPassword | sudo -S rm -r $localSoftwarePath$localSoftwarePrefix$timestampToDeleteReadable
echo "-Erledigt"
#MozRepl starten und ausführen
echo "Teste Telnetverbindung"
if nc -z -w2 localhost 4242 2>/dev/null; then
	echo "Beende laufende Firefox-Prozesse"
	killall -9 firefox
	sleep 120
	echo "-Erledigt"
	echo "Starte Firefox"
	DISPLAY=:0 firefox&
	sleep 15
	echo "-Erledigt"
	echo "Teste Telnetverbindung"
	if nc -z -w2 localhost 4242 2>/dev/null; then
		echo "Führe MozRepl aus"
		expect ./testReplExecution http://localhost/$localSoftwarePrefix$timestampReadable $softwareUser $softwarePassword
		sleep 5
		echo "-Erledigt"
	else
		mutt -s "Automatisches Testsystem" rainer@furtmeier.it -i /home/test/testCommandlineMessage.txt <.
		sleep 5
	fi
else
	echo "Starte Firefox"
	DISPLAY=:0 firefox&
	sleep 15
	echo "-Erledig"
	echo "Teste Telnetverbindung erneut"
	if nc -z -w2 localhost 4242 2>/dev/null; then
		echo "Führe MozRepl aus"
		expect ./testReplExecution http://localhost/$localSoftwarePrefix$timestampReadable $softwareUser $softwarePassword
		sleep 5
		echo "-Erledigt"
	else
		mutt -s "Automatisches Testsystem" rainer@furtmeier.it -i /home/test/testCommandlineMessage.txt <.
		sleep 5
	fi
fi
#Ende
echo " "
echo "---Fertig---"

