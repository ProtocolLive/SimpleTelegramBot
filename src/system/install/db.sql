create table callbackshash(
  hash varchar(40) not null primary key,
  data varchar(100) not null
);
create table chats(
  chat_id bigint unsigned not null primary key,
  type varchar(10) not null,
  name varchar(100),
  name2 varchar(100),
  nick varchar(100),
  perms tinyint unsigned default 0 not null,
  created int unsigned,
  lastseen int unsigned
);
create table modules(
  module varchar(50) not null primary key,
  created int unsigned not null
);
insert into modules values('StbAdmin',0);
create table commands(
  command varchar(100) not null primary key,
  module varchar(50) not null,
  foreign key(module) references modules(module) on delete cascade
);
insert into commands values('admin','StbAdmin'),('id','StbAdmin');
create table listeners(
  listener varchar(100) not null,
  chat_id bigint unsigned,
  module varchar(50) not null,
  foreign key(chat_id) references chats(chat_id),
  foreign key(module) references modules(module),
  unique(listener,chat_id)
);
create table sys_logs(
  log_id integer not null primary key autoincrement,
  time int unsigned not null,
  chat_id bigint unsigned not null,
  event varchar(50) not null,
  additional varchar(50),
  foreign key(chat_id) references chats(chat_id),
  unique(time,chat_id)
);
create table sys_params(
  name varchar(50) not null primary key,
  value varchar(100) not null
);
insert into sys_params values('DbVersion','1.0.0');
create table variables(
  chat_id bigint unsigned,
  name varchar(50) not null,
  value varchar(100),
  unique(chat_id,name),
  foreign key(chat_id) references chats(chat_id)
);