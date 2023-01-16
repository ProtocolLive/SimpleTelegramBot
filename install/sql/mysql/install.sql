create table callbackshash(
  hash varchar(100) not null primary key,
  method varchar(255) not null
) engine=InnoDB default charset=utf8mb4 collate=utf8mb4_unicode_ci;

create table chats(
  chat_id bigint unsigned not null primary key,
  name varchar(50) default '-',
  name2 varchar(50),
  nick varchar(50),
  lang varchar(5),
  perms tinyint unsigned default 0 not null,
  created int unsigned,
  lastseen int unsigned
) engine=InnoDB default charset=utf8mb4 collate=utf8mb4_unicode_ci;

create table modules(
  module varchar(100) not null primary key,
  created int unsigned not null
) engine=InnoDB default charset=utf8mb4 collate=utf8mb4_unicode_ci;

insert into modules values
  ('ProtocolLive\\SimpleTelegramBot\\StbObjects\\StbAdmin', 0);

create table commands(
  command varchar(50) not null primary key,
  module varchar(100) not null,
  foreign key(module) references modules(module) on delete cascade on update cascade
) engine=InnoDB default charset=utf8mb4 collate=utf8mb4_unicode_ci;

insert into commands values
  ('admin', 'ProtocolLive\\SimpleTelegramBot\\StbObjects\\StbAdmin'),
  ('id','ProtocolLive\\SimpleTelegramBot\\StbObjects\\StbAdmin');

create table listeners(
  listener_id int unsigned not null primary key auto_increment,
  listener varchar(50) not null,
  chat_id bigint unsigned,
  module varchar(100) not null,
  foreign key(chat_id) references chats(chat_id) on delete cascade,
  foreign key(module) references modules(module) on delete cascade,
  unique(listener,chat_id)
) engine=InnoDB default charset=utf8mb4 collate=utf8mb4_unicode_ci;

create table sys_logs(
  log_id int unsigned not null primary key auto_increment,
  time int unsigned not null,
  chat_id bigint unsigned not null,
  event varchar(50) not null,
  additional varchar(50),
  foreign key(chat_id) references chats(chat_id) on delete cascade,
  unique(time,chat_id)
) engine=InnoDB default charset=utf8mb4 collate=utf8mb4_unicode_ci;

create table sys_params(
  name varchar(50) not null primary key,
  value varchar(50) not null
) engine=InnoDB default charset=utf8mb4 collate=utf8mb4_unicode_ci;

insert into sys_params values('DbVersion','1.0.0');

create table variables(
  var_id int unsigned primary key auto_increment,
  module varchar(100) references modules(module) on delete cascade on update cascade,
  chat_id bigint unsigned references chats(chat_id) on delete cascade on update cascade,
  name varchar(50) not null,
  value varchar(50),
  unique(module,chat_id,name)
) engine=InnoDB default charset=utf8mb4 collate=utf8mb4_unicode_ci;