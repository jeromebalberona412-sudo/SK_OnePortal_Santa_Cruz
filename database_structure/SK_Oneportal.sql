Database = SK_Oneportal



create table public.users (
  id bigserial not null,
  name character varying(255) not null,
  email character varying(255) not null,
  email_verified_at timestamp without time zone null,
  password character varying(255) not null,
  is_admin boolean not null default false,
  two_factor_secret text null,
  two_factor_recovery_codes text null,
  two_factor_confirmed_at timestamp without time zone null,
  remember_token character varying(100) null,
  created_at timestamp without time zone null,
  updated_at timestamp without time zone null,
  lockout_count integer not null default 0,
  lockout_until timestamp without time zone null,
  last_login_at timestamp without time zone null,
  last_login_ip character varying(45) null,
  constraint users_pkey primary key (id),
  constraint users_email_unique unique (email)
) TABLESPACE pg_default;

create index IF not exists users_lockout_until_index on public.users using btree (lockout_until) TABLESPACE pg_default;

create table public.sessions (
  id character varying(255) not null,
  user_id bigint null,
  ip_address character varying(45) null,
  user_agent text null,
  payload text not null,
  last_activity integer not null,
  constraint sessions_pkey primary key (id)
) TABLESPACE pg_default;

create index IF not exists sessions_user_id_index on public.sessions using btree (user_id) TABLESPACE pg_default;

create index IF not exists sessions_last_activity_index on public.sessions using btree (last_activity) TABLESPACE pg_default;

create table public.password_reset_tokens (
  email character varying(255) not null,
  token character varying(255) not null,
  created_at timestamp without time zone null,
  constraint password_reset_tokens_pkey primary key (email)
) TABLESPACE pg_default;

create table public.login_attempts (
  id bigserial not null,
  email character varying(255) not null,
  ip_address character varying(45) not null,
  successful boolean not null default false,
  user_agent text null,
  metadata json null,
  attempted_at timestamp without time zone not null default CURRENT_TIMESTAMP,
  constraint login_attempts_pkey primary key (id)
) TABLESPACE pg_default;

create index IF not exists login_attempts_email_successful_attempted_at_index on public.login_attempts using btree (email, successful, attempted_at) TABLESPACE pg_default;

create index IF not exists login_attempts_ip_address_attempted_at_index on public.login_attempts using btree (ip_address, attempted_at) TABLESPACE pg_default;

create index IF not exists login_attempts_email_index on public.login_attempts using btree (email) TABLESPACE pg_default;

create index IF not exists login_attempts_ip_address_index on public.login_attempts using btree (ip_address) TABLESPACE pg_default;

create table public.cache_locks (
  key character varying(255) not null,
  owner character varying(255) not null,
  expiration integer not null,
  constraint cache_locks_pkey primary key (key)
) TABLESPACE pg_default;

create index IF not exists cache_locks_expiration_index on public.cache_locks using btree (expiration) TABLESPACE pg_default;

create table public.admin_activity_logs (
  id uuid not null,
  user_id bigint null,
  event_type character varying(100) not null,
  ip_address character varying(45) null,
  user_agent text null,
  metadata json null,
  created_at timestamp without time zone not null default CURRENT_TIMESTAMP,
  constraint admin_activity_logs_pkey primary key (id),
  constraint admin_activity_logs_user_id_foreign foreign KEY (user_id) references users (id) on delete set null
) TABLESPACE pg_default;

create index IF not exists admin_activity_logs_user_id_created_at_index on public.admin_activity_logs using btree (user_id, created_at) TABLESPACE pg_default;

create index IF not exists admin_activity_logs_event_type_created_at_index on public.admin_activity_logs using btree (event_type, created_at) TABLESPACE pg_default;

create index IF not exists admin_activity_logs_event_type_index on public.admin_activity_logs using btree (event_type) TABLESPACE pg_default;

create table public.cache (
  key character varying(255) not null,
  value text not null,
  expiration integer not null,
  constraint cache_pkey primary key (key)
) TABLESPACE pg_default;

create index IF not exists cache_expiration_index on public.cache using btree (expiration) TABLESPACE pg_default;