Database = SK_Oneportal

create table public.users (
  id bigserial not null,
  name character varying(255) not null,
  email character varying(255) not null,
  email_verified_at timestamp without time zone null,
  password character varying(255) not null,
  role character varying(30) not null default 'user'::character varying,
  status character varying(30) not null default 'PENDING_APPROVAL'::character varying,
  must_change_password boolean not null default false,
  two_factor_secret text null,
  two_factor_recovery_codes text null,
  two_factor_confirmed_at timestamp without time zone null,
  remember_token character varying(100) null,
  created_at timestamp without time zone null,
  updated_at timestamp without time zone null,
  deleted_at timestamp without time zone null,
  lockout_count integer not null default 0,
  lockout_until timestamp without time zone null,
  last_login_at timestamp without time zone null,
  last_login_ip character varying(45) null,
  active_session_id character varying(255) null,
  last_seen timestamp without time zone null,
  active_device character varying(255) null,
  last_ip character varying(45) null,
  otp_code character varying(255) null,
  otp_expires_at timestamp without time zone null,
  otp_attempts smallint not null default 0,
  otp_last_sent_at timestamp without time zone null,
  tenant_id bigint null,
  barangay_id bigint null,
  constraint users_pkey primary key (id),
  constraint users_email_unique unique (email),
  constraint users_barangay_id_foreign foreign KEY (barangay_id) references barangays (id) on delete set null,
  constraint users_tenant_id_foreign foreign KEY (tenant_id) references tenants (id) on delete CASCADE
) TABLESPACE pg_default;

create index IF not exists users_lockout_until_index on public.users using btree (lockout_until) TABLESPACE pg_default;

create index IF not exists users_role_index on public.users using btree (role) TABLESPACE pg_default;

create index IF not exists users_status_index on public.users using btree (status) TABLESPACE pg_default;

create index IF not exists users_tenant_id_index on public.users using btree (tenant_id) TABLESPACE pg_default;

create index IF not exists users_barangay_id_index on public.users using btree (barangay_id) TABLESPACE pg_default;

create table public.tenants (
  id bigserial not null,
  name character varying(150) not null,
  code character varying(80) not null,
  municipality character varying(255) not null default 'Santa Cruz'::character varying,
  province character varying(255) not null default 'Laguna'::character varying,
  region character varying(255) not null default 'IV-A CALABARZON'::character varying,
  is_active boolean not null default true,
  created_at timestamp without time zone null,
  updated_at timestamp without time zone null,
  constraint tenants_pkey primary key (id),
  constraint tenants_code_unique unique (code)
) TABLESPACE pg_default;

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

create table public.official_terms (
  id bigserial not null,
  official_profile_id bigint not null,
  term_start date not null,
  term_end date not null,
  status character varying(255) not null default 'ACTIVE'::character varying,
  created_at timestamp without time zone null,
  updated_at timestamp without time zone null,
  constraint official_terms_pkey primary key (id),
  constraint official_terms_official_profile_id_foreign foreign KEY (official_profile_id) references official_profiles (id) on delete CASCADE,
  constraint official_terms_status_check check (
    (
      (status)::text = any (
        (
          array[
            'ACTIVE'::character varying,
            'INACTIVE'::character varying,
            'EXPIRED'::character varying,
            'REPLACED'::character varying
          ]
        )::text[]
      )
    )
  )
) TABLESPACE pg_default;

create index IF not exists official_terms_official_profile_id_term_end_index on public.official_terms using btree (official_profile_id, term_end) TABLESPACE pg_default;

create index IF not exists official_terms_status_index on public.official_terms using btree (status) TABLESPACE pg_default;

create unique index IF not exists official_terms_one_active_per_profile_idx on public.official_terms using btree (official_profile_id) TABLESPACE pg_default
where
  ((status)::text = 'ACTIVE'::text);

create table public.official_profiles (
  id bigserial not null,
  user_id bigint not null,
  first_name character varying(255) not null,
  last_name character varying(255) not null,
  middle_name character varying(100) null,
  suffix character varying(20) null,
  date_of_birth date null,
  age smallint null,
  contact_number character varying(20) null,
  position character varying(255) not null,
  municipality character varying(255) not null default 'Santa Cruz'::character varying,
  province character varying(255) not null default 'Laguna'::character varying,
  region character varying(255) not null default 'IV-A CALABARZON'::character varying,
  created_at timestamp without time zone not null default CURRENT_TIMESTAMP,
  tenant_id bigint null,
  updated_at timestamp without time zone null,
  constraint official_profiles_pkey primary key (id),
  constraint official_profiles_user_id_unique unique (user_id),
  constraint official_profiles_tenant_id_foreign foreign KEY (tenant_id) references tenants (id) on delete CASCADE,
  constraint official_profiles_user_id_foreign foreign KEY (user_id) references users (id) on delete CASCADE,
  constraint official_profiles_position_check check (
    (
      ("position")::text = any (
        (
          array[
            'Chairman'::character varying,
            'Councilor'::character varying,
            'Kagawad'::character varying,
            'Treasurer'::character varying,
            'Secretary'::character varying,
            'Auditor'::character varying,
            'PIO'::character varying
          ]
        )::text[]
      )
    )
  )
) TABLESPACE pg_default;

create index IF not exists official_profiles_tenant_id_index on public.official_profiles using btree (tenant_id) TABLESPACE pg_default;

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

create table public.barangays (
  id bigserial not null,
  name character varying(255) not null,
  municipality character varying(255) not null default 'Santa Cruz'::character varying,
  province character varying(255) not null default 'Laguna'::character varying,
  region character varying(255) not null default 'IV-A CALABARZON'::character varying,
  created_at timestamp without time zone null,
  updated_at timestamp without time zone null,
  tenant_id bigint null,
  constraint barangays_pkey primary key (id),
  constraint barangays_tenant_name_unique unique (tenant_id, name),
  constraint barangays_tenant_id_foreign foreign KEY (tenant_id) references tenants (id) on delete CASCADE
) TABLESPACE pg_default;

create index IF not exists barangays_tenant_id_index on public.barangays using btree (tenant_id) TABLESPACE pg_default;

create table public.admin_activity_logs (
  id uuid not null,
  user_id bigint null,
  event_type character varying(100) not null,
  ip_address character varying(45) null,
  user_agent text null,
  metadata json null,
  created_at timestamp without time zone not null default CURRENT_TIMESTAMP,
  tenant_id bigint null,
  action character varying(120) null,
  entity_type character varying(120) null,
  entity_id character varying(120) null,
  constraint admin_activity_logs_pkey primary key (id),
  constraint admin_activity_logs_tenant_id_foreign foreign KEY (tenant_id) references tenants (id) on delete set null,
  constraint admin_activity_logs_user_id_foreign foreign KEY (user_id) references users (id) on delete set null
) TABLESPACE pg_default;

create index IF not exists admin_activity_logs_user_id_created_at_index on public.admin_activity_logs using btree (user_id, created_at) TABLESPACE pg_default;

create index IF not exists admin_activity_logs_event_type_created_at_index on public.admin_activity_logs using btree (event_type, created_at) TABLESPACE pg_default;

create index IF not exists admin_activity_logs_event_type_index on public.admin_activity_logs using btree (event_type) TABLESPACE pg_default;

create index IF not exists admin_activity_logs_tenant_id_index on public.admin_activity_logs using btree (tenant_id) TABLESPACE pg_default;

create index IF not exists admin_activity_logs_action_created_at_index on public.admin_activity_logs using btree (action, created_at) TABLESPACE pg_default;

create index IF not exists admin_activity_logs_entity_type_entity_id_index on public.admin_activity_logs using btree (entity_type, entity_id) TABLESPACE pg_default;

create table public.cache (
  key character varying(255) not null,
  value text not null,
  expiration integer not null,
  constraint cache_pkey primary key (key)
) TABLESPACE pg_default;

create index IF not exists cache_expiration_index on public.cache using btree (expiration) TABLESPACE pg_default;

create table public.cache_locks (
  key character varying(255) not null,
  owner character varying(255) not null,
  expiration integer not null,
  constraint cache_locks_pkey primary key (key)
) TABLESPACE pg_default;

create index IF not exists cache_locks_expiration_index on public.cache_locks using btree (expiration) TABLESPACE pg_default;

create table public.sk_fed_trusted_devices (
  id bigserial not null,
  user_id bigint not null,
  fingerprint character varying(128) not null,
  ip_address character varying(45) null,
  user_agent text null,
  last_used_at timestamp without time zone null,
  expires_at timestamp without time zone null,
  metadata json null,
  created_at timestamp without time zone null,
  updated_at timestamp without time zone null,
  constraint sk_fed_trusted_devices_pkey primary key (id),
  constraint sk_fed_trusted_devices_user_id_foreign foreign KEY (user_id) references users (id) on delete CASCADE,
  constraint sk_fed_trusted_device_unique unique (user_id, fingerprint)
) TABLESPACE pg_default;

create index IF not exists sk_fed_trusted_device_exp_idx on public.sk_fed_trusted_devices using btree (user_id, expires_at) TABLESPACE pg_default;

create table public.sk_fed_login_attempts (
  id bigserial not null,
  user_id bigint null,
  email character varying(255) not null,
  ip_address character varying(45) not null,
  successful boolean not null default false,
  user_agent text null,
  attempted_at timestamp without time zone not null default CURRENT_TIMESTAMP,
  metadata json null,
  constraint sk_fed_login_attempts_pkey primary key (id),
  constraint sk_fed_login_attempts_user_id_foreign foreign KEY (user_id) references users (id) on delete set null
) TABLESPACE pg_default;

create index IF not exists sk_fed_login_attempts_email_index on public.sk_fed_login_attempts using btree (email) TABLESPACE pg_default;
create index IF not exists sk_fed_login_attempts_ip_address_index on public.sk_fed_login_attempts using btree (ip_address) TABLESPACE pg_default;
create index IF not exists sk_fed_login_attempt_email_idx on public.sk_fed_login_attempts using btree (email, successful, attempted_at) TABLESPACE pg_default;
create index IF not exists sk_fed_login_attempt_ip_idx on public.sk_fed_login_attempts using btree (ip_address, successful, attempted_at) TABLESPACE pg_default;

create table public.sk_fed_auth_audit_logs (
  id bigserial not null,
  user_id bigint null,
  tenant_id bigint null,
  actor_email character varying(255) null,
  event character varying(120) not null,
  outcome character varying(20) null,
  resource_type character varying(120) null,
  resource_id character varying(120) null,
  ip_address character varying(45) null,
  user_agent text null,
  metadata json null,
  created_at timestamp without time zone not null default CURRENT_TIMESTAMP,
  constraint sk_fed_auth_audit_logs_pkey primary key (id),
  constraint sk_fed_auth_audit_logs_tenant_id_foreign foreign KEY (tenant_id) references tenants (id) on delete set null,
  constraint sk_fed_auth_audit_logs_user_id_foreign foreign KEY (user_id) references users (id) on delete set null
) TABLESPACE pg_default;

create index IF not exists sk_fed_auth_audit_event_idx on public.sk_fed_auth_audit_logs using btree (event, created_at) TABLESPACE pg_default;
create index IF not exists sk_fed_auth_audit_user_idx on public.sk_fed_auth_audit_logs using btree (user_id, created_at) TABLESPACE pg_default;
create index IF not exists sk_fed_auth_audit_tenant_idx on public.sk_fed_auth_audit_logs using btree (tenant_id, created_at) TABLESPACE pg_default;
create index IF not exists sk_fed_auth_audit_outcome_idx on public.sk_fed_auth_audit_logs using btree (outcome, created_at) TABLESPACE pg_default;
create index IF not exists sk_fed_auth_resource_idx on public.sk_fed_auth_audit_logs using btree (resource_type, resource_id) TABLESPACE pg_default;

create table public.sk_fed_feature_flags (
  id bigserial not null,
  flag_key character varying(190) not null,
  enabled boolean not null default false,
  description character varying(255) null,
  rollout_percentage smallint null,
  metadata json null,
  created_at timestamp without time zone null,
  updated_at timestamp without time zone null,
  constraint sk_fed_feature_flags_pkey primary key (id),
  constraint sk_fed_feature_flags_flag_key_unique unique (flag_key)
) TABLESPACE pg_default;

create table public.sk_fed_email_verified_devices (
  id bigserial not null,
  user_id bigint not null,
  fingerprint character varying(128) not null,
  verified_at timestamp without time zone null,
  ip_address character varying(45) null,
  user_agent text null,
  metadata json null,
  created_at timestamp without time zone null,
  updated_at timestamp without time zone null,
  constraint sk_fed_email_verified_devices_pkey primary key (id),
  constraint sk_fed_email_verified_devices_user_id_unique unique (user_id),
  constraint sk_fed_email_verified_devices_user_id_foreign foreign KEY (user_id) references users (id) on delete CASCADE
) TABLESPACE pg_default;

create index IF not exists sk_fed_verified_device_verified_at_idx on public.sk_fed_email_verified_devices using btree (verified_at) TABLESPACE pg_default;

create table public.sk_official_trusted_devices (
  id bigserial not null,
  user_id bigint not null,
  fingerprint character varying(128) not null,
  ip_address character varying(45) null,
  user_agent text null,
  last_used_at timestamp without time zone null,
  expires_at timestamp without time zone null,
  metadata json null,
  created_at timestamp without time zone null,
  updated_at timestamp without time zone null,
  constraint sk_official_trusted_devices_pkey primary key (id),
  constraint sk_official_trusted_devices_user_id_foreign foreign KEY (user_id) references users (id) on delete CASCADE,
  constraint sk_official_trusted_device_unique unique (user_id, fingerprint)
) TABLESPACE pg_default;

create index IF not exists sk_official_trusted_device_exp_idx on public.sk_official_trusted_devices using btree (user_id, expires_at) TABLESPACE pg_default;

create table public.sk_official_login_attempts (
  id bigserial not null,
  user_id bigint null,
  email character varying(255) not null,
  ip_address character varying(45) not null,
  successful boolean not null default false,
  user_agent text null,
  attempted_at timestamp without time zone not null default CURRENT_TIMESTAMP,
  metadata json null,
  constraint sk_official_login_attempts_pkey primary key (id),
  constraint sk_official_login_attempts_user_id_foreign foreign KEY (user_id) references users (id) on delete set null
) TABLESPACE pg_default;

create index IF not exists sk_official_login_attempts_email_index on public.sk_official_login_attempts using btree (email) TABLESPACE pg_default;
create index IF not exists sk_official_login_attempts_ip_address_index on public.sk_official_login_attempts using btree (ip_address) TABLESPACE pg_default;
create index IF not exists sk_official_login_attempt_email_idx on public.sk_official_login_attempts using btree (email, successful, attempted_at) TABLESPACE pg_default;
create index IF not exists sk_official_login_attempt_ip_idx on public.sk_official_login_attempts using btree (ip_address, successful, attempted_at) TABLESPACE pg_default;

create table public.sk_official_auth_audit_logs (
  id bigserial not null,
  user_id bigint null,
  tenant_id bigint null,
  actor_email character varying(255) null,
  event character varying(120) not null,
  outcome character varying(20) null,
  resource_type character varying(120) null,
  resource_id character varying(120) null,
  ip_address character varying(45) null,
  user_agent text null,
  metadata json null,
  created_at timestamp without time zone not null default CURRENT_TIMESTAMP,
  constraint sk_official_auth_audit_logs_pkey primary key (id),
  constraint sk_official_auth_audit_logs_tenant_id_foreign foreign KEY (tenant_id) references tenants (id) on delete set null,
  constraint sk_official_auth_audit_logs_user_id_foreign foreign KEY (user_id) references users (id) on delete set null
) TABLESPACE pg_default;

create index IF not exists sk_official_auth_audit_event_idx on public.sk_official_auth_audit_logs using btree (event, created_at) TABLESPACE pg_default;
create index IF not exists sk_official_auth_audit_user_idx on public.sk_official_auth_audit_logs using btree (user_id, created_at) TABLESPACE pg_default;
create index IF not exists sk_official_auth_audit_tenant_idx on public.sk_official_auth_audit_logs using btree (tenant_id, created_at) TABLESPACE pg_default;
create index IF not exists sk_official_auth_audit_outcome_idx on public.sk_official_auth_audit_logs using btree (outcome, created_at) TABLESPACE pg_default;
create index IF not exists sk_official_auth_resource_idx on public.sk_official_auth_audit_logs using btree (resource_type, resource_id) TABLESPACE pg_default;

create table public.sk_official_feature_flags (
  id bigserial not null,
  flag_key character varying(190) not null,
  enabled boolean not null default false,
  description character varying(255) null,
  rollout_percentage smallint null,
  metadata json null,
  created_at timestamp without time zone null,
  updated_at timestamp without time zone null,
  constraint sk_official_feature_flags_pkey primary key (id),
  constraint sk_official_feature_flags_flag_key_unique unique (flag_key)
) TABLESPACE pg_default;

create table public.sk_official_email_verified_devices (
  id bigserial not null,
  user_id bigint not null,
  fingerprint character varying(128) not null,
  verified_at timestamp without time zone null,
  ip_address character varying(45) null,
  user_agent text null,
  metadata json null,
  created_at timestamp without time zone null,
  updated_at timestamp without time zone null,
  constraint sk_official_email_verified_devices_pkey primary key (id),
  constraint sk_official_email_verified_devices_user_id_unique unique (user_id),
  constraint sk_official_email_verified_devices_user_id_foreign foreign KEY (user_id) references users (id) on delete CASCADE
) TABLESPACE pg_default;

create index IF not exists sk_official_verified_device_verified_at_idx on public.sk_official_email_verified_devices using btree (verified_at) TABLESPACE pg_default;
