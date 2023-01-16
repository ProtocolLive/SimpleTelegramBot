create table callbackshash(
  hash text not null primary key,
  method text not null
);
create table chats(
  chat_id integer not null primary key,
  name text default '-',
  name2 text,
  nick text,
  lang text,
  perms integer unsigned default 0 not null,
  created integer unsigned,
  lastseen integer unsigned
);
create table modules(
  module text not null primary key,
  created integer unsigned not null
);
insert into modules values(
  'ProtocolLive\SimpleTelegramBot\StbObjects\StbAdmin', 0
);
create table commands(
  command text not null primary key,
  module text not null,
  foreign key(module) references modules(module) on delete cascade
);
insert into commands values
('admin', 'ProtocolLive\SimpleTelegramBot\StbObjects\StbAdmin'),
('id', 'ProtocolLive\SimpleTelegramBot\StbObjects\StbAdmin');
create table listeners(
  listener_id integer not null primary key autoincrement,
  listener text not null,
  chat_id integer,
  module text not null,
  foreign key(chat_id) references chats(chat_id) on delete cascade,
  foreign key(module) references modules(module) on delete cascade,
  unique(listener,chat_id)
);
create table sys_logs(
  log_id integer not null primary key autoincrement,
  time integer unsigned not null,
  chat_id integer not null,
  event text not null,
  additional text,
  foreign key(chat_id) references chats(chat_id) on delete cascade,
  unique(time,chat_id)
);
create table sys_params(
  name text not null primary key,
  value text not null
);
insert into sys_params values('DbVersion','1.0.0');

create table variables(
  chat_id integer,
  module text,
  name text not null,
  value text,
  unique(module,chat_id,name),
  foreign key(chat_id) references chats(chat_id) on delete cascade
);