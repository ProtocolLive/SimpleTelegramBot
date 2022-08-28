create table callbackshash(
  hash varchar(100) not null primary key,
  data varchar(100) not null
);
create table chats(
  chat_id bigint not null primary key,
  name varchar(50) default '-',
  name2 varchar(50),
  nick varchar(50),
  lang varchar(5),
  perms tinyint unsigned default 0 not null,
  created int unsigned,
  lastseen int unsigned
);
create table modules(
  module varchar(50) not null primary key,
  created int unsigned not null
);
insert into modules values
  ('ProtocolLive\\SimpleTetelegramBot\\StbObjects\\StbAdmin', 0);
create table commands(
  command varchar(50) not null primary key,
  module varchar(50) not null,
  foreign key(module) references modules(module) on delete cascade
);
insert into commands values
  ('admin', 'ProtocolLive\\SimpleTetelegramBot\\StbObjects\\StbAdmin'),
  ('id','ProtocolLive\\SimpleTetelegramBot\\StbObjects\\StbAdmin');
create table listeners(
  listener varchar(50) not null,
  chat_id bigint,
  module varchar(50) not null,
  foreign key(chat_id) references chats(chat_id) on delete cascade,
  foreign key(module) references modules(module) on delete cascade,
  unique(listener,chat_id)
);
create table sys_logs(
  log_id int not null primary key auto_increment,
  time int unsigned not null,
  chat_id bigint not null,
  event varchar(50) not null,
  additional varchar(50),
  foreign key(chat_id) references chats(chat_id) on delete cascade,
  unique(time,chat_id)
);
create table sys_params(
  name varchar(50) not null primary key,
  value varchar(50) not null
);
insert into sys_params values('DbVersion','1.0.0');
create table variables(
  chat_id bigint,
  name varchar(50) not null,
  value varchar(50),
  unique(chat_id,name),
  foreign key(chat_id) references chats(chat_id) on delete cascade
);