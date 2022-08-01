pragma foreign_keys=off;
alter table chats rename to chats_old;
create table chats(
  chat_id integer not null primary key,
  name text default '-',
  name2 text,
  nick text,
  perms tinyint unsigned default 0 not null,
  created integer unsigned,
  lastseen integer unsigned
);
insert into chats select chat_id,name,name2,nick,perms,created,lastseen from chats_old;
drop table chats_old;
----------------------------------------------------------------------------------
alter table listeners rename to listeners_old;
create table listeners(
  listener text not null,
  chat_id integer,
  module text not null,
  foreign key(chat_id) references chats(chat_id),
  foreign key(module) references modules(module),
  unique(listener,chat_id)
);
insert into listeners select listener,chat_id,module from listeners_old;
drop table listeners_old;
----------------------------------------------------------------------------------
alter table sys_logs rename to sys_logs_old;
create table sys_logs(
  log_id integer not null primary key autoincrement,
  time integer unsigned not null,
  chat_id integer not null,
  event text not null,
  additional text,
  foreign key(chat_id) references chats(chat_id),
  unique(time,chat_id)
);
insert into sys_logs select log_id,time,chat_id,event,additional from sys_logs_old;
drop table sys_logs_old;
----------------------------------------------------------------------------------
alter table variables rename to variables_old;
create table variables(
  chat_id integer,
  name text not null,
  value text,
  unique(chat_id,name),
  foreign key(chat_id) references chats(chat_id)
);
insert into variables select chat_id,name,value from variables_old;
drop table variables_old;
----------------------------------------------------------------------------------
update sys_params set value='1.0.1' where name='DbVersion';
pragma foreign_keys=on;