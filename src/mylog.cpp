/********************************************************************************
#           LogSystem                                            [SAcc system] 
#			   Copyright (C) 2003-2006  Vyacheslav Nikitin
#		$Author: slavik $ $Date: 2012-11-20 17:39:26 +0600 (Вт, 20 ноя 2012) $
#		$Id: mylog.cpp 112 2012-11-20 11:39:26Z slavik $
#           -----------------------------------------------------
#   			This file is part of SAcc system.  
#                    [http://sacc.cybersec.ru]
#
#   This program is free software; you can redistribute it and/or modify
#   it under the terms of the GNU General Public License as published by
#   the Free Software Foundation; either version 2 of the License, or
#   (at your option) any later version.
#
#   This program is distributed in the hope that it will be useful,
#   but WITHOUT ANY WARRANTY; without even the implied warranty of
#   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
#   GNU General Public License for more details.
#
#   You should have received a copy of the GNU General Public License
#   along with this program; if not, write to the Free Software
#
# Foundation, Inc., 675 Mass Ave, Cambridge, MA 02139, USA.
******************************************************************************/
#include "mylog.h"
#include <pthread.h>

clog::~clog()
	{
	if (connected) 
		{
		closelog();
		connected=false;
		free(log_buffer);
		}
	}
void clog::log(char* app_name, bool insyslog, int mask)
	{
	openlog(app_name, 0, LOG_USER);
	connected=true;
	logmask=mask;
	log_buffer=(char*)malloc(MAXLEN);
	syslg=insyslog;
	}
/* Log message */
void clog::msg(char* FileName, int Line, int severity, const char *fmt, ...)
{
	va_list ap;		
	if (severity>logmask) return;
	if (NULL==fmt) return;
	va_start(ap, fmt);	
    if (syslg)
    {
    //char* FileName, int Line
    	if (7==logmask) {
    		syslog(severity, "in %s:%d", FileName, Line);
    		};
        vsyslog(severity, fmt, ap);
    }
    else
    {
      	if (7==logmask) {
      		printf("(%u) in %s:%d ",pthread_self(), FileName, Line);
      		};
		vprintf(fmt, ap);
		printf("\n");
    };
	va_end(ap);
};

