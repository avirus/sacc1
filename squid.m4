# A local version of AC_CHECK_SIZEOF that includes sys/types.h
dnl NEED TO REALIZE AUTOFIND files
dnl
dnl $Author: slavik $ $Date: 2012-11-20 17:42:29 +0600 (Вт, 20 ноя 2012) $
dnl $Id: squid.m4 113 2012-11-20 11:42:29Z slavik $
dnl MYSQL_CHECK_SIZEOF(TYPE [, CROSS-SIZE])
AC_DEFUN([AC_CHECK_SIZEOF_SYSTYPE],
[changequote(<<, >>)dnl
dnl The name to #define.
define(<<AC_TYPE_NAME>>, translit(sizeof_$1, [a-z *], [A-Z_P]))dnl
dnl The cache variable name.
define(<<AC_CV_NAME>>, translit(ac_cv_sizeof_$1, [ *], [_p]))dnl
changequote([, ])dnl
AC_MSG_CHECKING(size of $1)
AC_CACHE_VAL(AC_CV_NAME,
[AC_TRY_RUN([#include <stdio.h>
#include <sys/types.h>
#if STDC_HEADERS
#include <stdlib.h>
#include <stddef.h>
#endif
main()
{
  FILE *f=fopen("conftestval", "w");
  if (!f) exit(1);
  fprintf(f, "%d\n", sizeof($1));
  exit(0);
}], AC_CV_NAME=`cat conftestval`, AC_CV_NAME=0, ifelse([$2], , , AC_CV_NAME=$2))])dnl
AC_MSG_RESULT($AC_CV_NAME)
AC_DEFINE_UNQUOTED(AC_TYPE_NAME, $AC_CV_NAME, [ ])
undefine([AC_TYPE_NAME])dnl
undefine([AC_CV_NAME])dnl
])

dnl AC_CHECK_SYSTYPE(TYPE, DEFAULT)
AC_DEFUN([AC_CHECK_SYSTYPE],
[AC_REQUIRE([AC_HEADER_STDC])dnl
AC_MSG_CHECKING(for $1)
AC_CACHE_VAL(ac_cv_type_$1,
[AC_EGREP_CPP(dnl
changequote(<<,>>)dnl
<<(^|[^a-zA-Z_0-9])$1[^a-zA-Z_0-9]>>dnl
changequote([,]), [
/* What a mess.. many systems have added the (now standard) bit types
 * in their own ways, so we need to scan a wide variety of headers to
 * find them..
 */
#include <sys/types.h>
#if STDC_HEADERS
#include <stdlib.h>
#include <stddef.h>
#endif
#if HAVE_INTTYPES_H
#include <inttypes.h>
#endif
#if HAVE_SYS_TYPES_H
#include <sys/types.h>
#endif
#if HAVE_SYS_BITYPES_H
#include <sys/bitypes.h>
#endif
], ac_cv_type_$1=yes, ac_cv_type_$1=no)])dnl
AC_MSG_RESULT($ac_cv_type_$1)
if test $ac_cv_type_$1 = no; then
  AC_DEFINE($1, $2)
fi
AC_DEFINE($1,$2,$1)
])

dnl FILE_FIND(VARIABLE, FORCE-LOCATION, FILE-NAME, DIRS)
AC_DEFUN([FILE_FIND], [
dnl echo "entering FIND_FILE, parameters: $1, $2, $3, $4"
unset file_find_out
  case "$2" in
    AUTO)
	echo "using autodetect for $3"
      for d in $4 ; do
dnl	echo "looking for $3 in $d"
       if test -f $d/$3  ; then
         file_find_out=$d/$3
       fi
      done
      ;;
    * )
      if test -f $2  ; then
	echo "looking for $3 in $2"
        file_find_out=$2
      fi
      ;;
  esac

 if test -z "$file_find_out" ; then
   echo "Could not find file, $3 not found."
  exit 1
 fi
$1=$file_find_out
echo "found $3: $file_find_out"
])


dnl same than FILE_FIND but returns only directory
AC_DEFUN([FILE_FIND2], [
dnl echo "entering FIND_FILE, parameters: $1, $2, $3, $4"
unset file_find_out
  case "$2" in
    AUTO)
	echo "using autodetect for $3"
      for d in $4 ; do
dnl	echo "looking for $3 in $d"
       if test -f $d/$3  ; then
         file_find_out=$d
       fi
      done
      ;;
    * )
      if test -f $2/$3  ; then
	echo "looking for $3 in $2"
        file_find_out=$2
      fi
      ;;
  esac

 if test -z "$file_find_out" ; then
   echo "Could not find file, $3 not found."
  exit 1
 fi
$1=$file_find_out
echo "found $3: $file_find_out"
])


