#
#  Makefile for the Squid Object Cache server
#
#  $Id: Makefile.am 113 2012-11-20 11:42:29Z slavik $
#
#  Uncomment and customize the following to suit your needs:
#
sacc_statdir=${prefix}/sacc/bin
sacc_stat_PROGRAMS	= ncsa_auth htpasswd
ncsa_auth_SOURCES	= ncsa_auth.c crypt_md5.c crypt_md5.h
htpasswd_SOURCES 	= htpasswd.c
#LDADD			= -L$(top_builddir)/lib -lmiscutil $(CRYPTLIB) $(XTRA_LIBS)
#INCLUDES      = -I$(top_srcdir)/include
LDADD			= -lmiscutil $(all_libraries) -L../lib $(CRYPTLIB) $(XTRA_LIBS)
INCLUDES= $(all_includes) -I../include
