//$Author: slavik $ $Date: 2012-11-20 17:39:26 +0600 (Вт, 20 ноя 2012) $
//$Id: squid.h 112 2012-11-20 11:39:26Z slavik $
#ifndef SQUID_H_
#define SQUID_H_
struct uid_cache
	{
	int uid;	// ������������� uid
	char* uname;	// ��� ������������
	};
struct trf_cache
	{
	int tid;
        int tval;
	char* url;
	};

#endif /*SQUID_H_*/
