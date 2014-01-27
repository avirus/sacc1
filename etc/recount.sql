-- $Id: recount.sql 113 2012-11-20 11:42:29Z slavik $
update users set used=0;
truncate table site;
truncate table detail;
update sys_trf set offset=0;
