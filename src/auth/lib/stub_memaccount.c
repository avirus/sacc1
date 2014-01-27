/*
 * $Id: stub_memaccount.c 113 2012-11-20 11:42:29Z slavik $
 */

/* Stub function for programs not implementing statMemoryAccounted */
#include "config.h"
#include "util.h"
int
statMemoryAccounted(void)
{
    return -1;
}
