#$Author: slavik $ $LastChangedDate: 2013-06-23 22:53:02 +0600 (Вс, 23 июн 2013) $ $Rev: 116 $
#$Id: Makefile.am 116 2013-06-23 16:53:02Z slavik $
SUBDIRS = src etc web

EXTRA_DIST = AUTHORS COPYING ChangeLog INSTALL .todo init.sql .bugs
DOC_DIST = ./doc/Install.notes.txt ./doc/about.en.txt ./doc/faq.txt ./doc/howto-ntlm.txt ./doc/Troubleshooting.txt ./doc/about.ru.txt ./doc/features.txt
MYSQL=$(CMYSQL) -f -B -h ${mysql_host} -u${mysql_root} --password=${mysql_rp} --default-character-set=koi8r
AUTOMAKE_OPTIONS = foreign

MAINTAINERCLEANFILES = subdirs acinclude.m4 configure.files libtool config.log config.cache config.h

install-data-local:
	$(INSTALL_DATA) -d -o root ${prefix}/sacc/addons ${prefix}/sacc/var/ ${prefix}/sacc/doc
	$(INSTALL_DATA) $(DOC_DIST)  ${prefix}/sacc/doc
	@$(ECHO) "Creating database, check ${prefix}/sacc/sql.log for errors."
	$(ECHO) "drop database if exists ${mysql_db};"| ${MYSQL} 2>${prefix}/sacc/sql.log
	$(ECHO) "create database ${mysql_db};"| ${MYSQL} 2>>${prefix}/sacc/sql.log
	$(CAT) ./init.sql|${MYSQL} --database="${mysql_db}" 2>>${prefix}/sacc/sql.log
	$(CAT) ./data.sql|${MYSQL} --database="${mysql_db}" 2>>${prefix}/sacc/sql.log
	$(ECHO) "GRANT ALL PRIVILEGES ON ${mysql_db}.* TO ${mysql_user}@${mysql_sh} IDENTIFIED BY '${mysql_passwd}';"|${MYSQL}  2>>${prefix}/sacc/sql.log
	@$(ECHO) "linking Web interface to ${www} directory"
	@if [ -h ${www} ]; then $(RM) ${www};fi
	$(TOUCH) ${ncsa_passwd}
	$(CHMOD) 664 ${ncsa_passwd}
	$(CHOWN) nobody:nobody ${ncsa_passwd}
	$(LN_S) ${prefix}/sacc/web/htdocs ${www}
	$(CHMOD) -R 644 ${prefix}/sacc/doc/*
	@$(ECHO) "Database created, check ${prefix}/sacc/sql.log for errors."
	@$(ECHO) "Check ${prefix}/sacc/etc/squid.orig.conf for your Squid configuration"
	@$(ECHO) "if you used NCSA, maybe you find useful addons/chpasswd"

upgrade-local:
	$(INSTALL) -d -o root ${prefix}/sacc/var ${prefix}/sacc/web ${prefix}/sacc/bin ${prefix}/sacc/etc ${prefix}/sacc/doc 
	$(CP) -Rf ./web/htdocs ${prefix}/sacc/web
	$(INSTALL_DATA) ./web/htdocs/inc/en.php ${prefix}/sacc/etc/en.php
	$(INSTALL_DATA) ./web/htdocs/inc/ru.php ${prefix}/sacc/etc/ru.php
	$(INSTALL_DATA) ./web/htdocs/inc/pref.php ${prefix}/sacc/etc/pref.php
	$(INSTALL_DATA) ./etc/recount.sql ${prefix}/sacc/etc/recount.sql
	$(INSTALL_DATA) ./etc/monthly.sql ${prefix}/sacc/etc/monthly.sql

uninstall-local:
	$(RM) -fr ${prefix}/sacc/
	@$(MYSQLDUMP) -f -c -h ${mysql_host} -u${mysql_root} --password=${mysql_rp} --default-character-set=koi8r ${mysql_db} >/tmp/sacc-dump.sql
	@$(ECHO) "drop database ${mysql_db}"|mysql -f -B
	@$(ECHO) "Database dropped, backup saved to /tmp/sacc-dump.sql"
	$(RM) ${www}

distclean-local:
	$(RM) -rf ./src/auth/lib/libTrie/autom4te.cache
	$(RM) -rf ./autom4te.cache
	find . -type d -name ".svn" -exec rm -rf {} \;;true
	find . -type d -name "CVS" -exec rm -rf {} \;;true

CXXFLAGS = -Wall -W -O3 -Wextra
