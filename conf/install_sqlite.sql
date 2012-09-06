begin;

create table elefant_webpage (
	id char(72) not null primary key,
	title char(72) not null,
	menu_title char(72) not null,
	window_title char(72) not null,
	access char(12) not null,
	layout char(48) not null,
	description text,
	keywords text,
	body text
);

create index elefant_webpage_access on elefant_webpage (access);

insert into elefant_webpage (id, title, menu_title, window_title, access, layout, description, keywords, body) values ('index', 'Welcome to Elefant', 'Home', '', 'public', 'default', '', '', '<table><tbody><tr><td><h3>Congratulations!</h3>You have successfully installed Elefant, the refreshingly simple new PHP web framework and CMS.</td><td><h3>Getting Started</h3>To log in as an administrator and edit pages, write a blog post, or upload files, go to <a href="/admin">/admin</a>.</td><td><h3>Developers</h3>Documentation, source code and issue tracking can be found at <a href="http://github.com/jbroadway/elefant">github.com/jbroadway/elefant</a></td></tr></tbody></table>');

create table elefant_block (
	id char(72) not null primary key,
	title char(72) not null,
	body text,
	access char(12) not null,
	show_title char(3) not null
);

create index elefant_block_access on elefant_block (id, access);

insert into elefant_block (id, title, access, body, show_title) values ('members', 'Members', 'public', '{! user/sidebar !}', 'no');

create table elefant_user (
	id integer primary key,
	email char(72) unique not null,
	password char(128) not null,
	session_id char(32) unique,
	expires datetime not null,
	name char(72) not null,
	type char(32) not null,
	signed_up datetime not null,
	updated datetime not null,
	userdata text not null
);

create index elefant_user_email_password on elefant_user (email, password);
create index elefant_user_session_id on elefant_user (session_id);

insert into elefant_user (id, email, password, session_id, expires, name, type, signed_up, updated, userdata) values (1, 'you@example.com', '$2a$07$1QeR9mu2doQxY0uBcpFlrOIfDxq0BwpR8FsImCgWvAL4Fz9jDByxi', null, (DATETIME('now')), 'Admin User', 'admin', (DATETIME('now')), (DATETIME('now')), '[]');

create table elefant_user_openid (
	token char(200) primary key,
	user_id int not null
);

create table elefant_versions (
	id integer primary key,
	class char(72) not null,
	pkey char(72) not null,
	user int not null,
	ts datetime not null,
	serialized text not null
);

create index elefant_versions_class on elefant_versions (class, pkey, ts);
create index elefant_versions_user on elefant_versions (user, ts);

create table elefant_api (
	token char(35) not null primary key,
	api_key char(35) not null,
	user_id int not null
);

create index elefant_api_token on elefant_api (token, api_key);
create index elefant_api_user on elefant_api (user_id);

create table elefant_blog_post (
	id integer primary key,
	title char(72) not null,
	ts datetime not null,
	author char(32) not null,
	published char(3) not null,
	body text not null,
	tags text not null,
	extra text not null
);

create index elefant_blog_post_ts on elefant_blog_post (ts);
create index elefant_blog_post_pts on elefant_blog_post (ts, published);

create table elefant_blog_tag (
	id char(24) not null,
	primary key (id)
);

create table elefant_blog_post_tag (
	tag_id char(24) not null,
	post_id int not null,
	primary key (tag_id, post_id)
);

create table elefant_lock (
	id integer primary key,
	user int not null,
	resource varchar(72) not null,
	resource_id varchar(72) not null,
	expires datetime not null,
	created datetime not null,
	modified datetime not null
);

create index elefant_lock_resource on elefant_lock (resource, resource_id, expires);
create index elefant_lock_user on elefant_lock (user);

create table elefant_filemanager_prop (
	file char(128) not null primary key,
	prop char(32) not null,
	value char(255) not null
);

create index elefant_filemanager_prop_name on elefant_filemanager_prop (prop);

create table elefant_apps (
	name char(48) not null primary key,
	version char(16) not null
);

insert into elefant_apps (name, version) values ('blog', '1.1.3-stable');
insert into elefant_apps (name, version) values ('user', '1.1.3-stable');
insert into elefant_apps (name, version) values ('filemanager', '1.3.0-beta');

commit;
