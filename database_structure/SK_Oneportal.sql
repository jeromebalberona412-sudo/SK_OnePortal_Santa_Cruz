Database = SK_Oneportal
Last Updated = 2026-06-16

-- Architecture note (2026-06-16):
-- SK Federation portal (role: sk_fed) is the system administrator.
-- Former Admin portal accounts/modules are owned by SK Federation.
-- Default bootstrap account: skoneportal@gmail.com (sk_fed only).

-- ============================================================
-- CORE / SHARED TABLES
-- ============================================================

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

create table public.users (
  id bigserial not null,
  name character varying(255) not null,
  email character varying(255) not null,
  email_verified_at timestamp without time zone null,
  password character varying(255) not null,
  pending_password character varying(255) null,
  password_change_token character varying(255) null,
  password_change_token_expires_at timestamp without time zone null,
  password_change_last_sent_at timestamp without time zone null,
  role character varying(30) not null default 'user'::character varying, -- sk_fed | sk_official | user (kabataan uses user)
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
  online_status character varying(20) not null default 'offline'::character varying,
  active_device character varying(255) null,
  last_ip character varying(45) null,
  pending_email character varying(255) null,
  email_change_token character varying(255) null,
  email_change_token_expires_at timestamp without time zone null,
  email_change_verified_at timestamp without time zone null,
  email_change_last_sent_at timestamp without time zone null,
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

create table public.jobs (
  id bigserial not null,
  queue character varying(255) not null,
  payload text not null,
  attempts smallint not null,
  reserved_at integer null,
  available_at integer not null,
  created_at integer not null,
  constraint jobs_pkey primary key (id)
) TABLESPACE pg_default;

create index IF not exists jobs_queue_index on public.jobs using btree (queue) TABLESPACE pg_default;

create table public.job_batches (
  id character varying(255) not null,
  name character varying(255) not null,
  total_jobs integer not null,
  pending_jobs integer not null,
  failed_jobs integer not null,
  failed_job_ids text not null,
  options text null,
  cancelled_at integer null,
  created_at integer not null,
  finished_at integer null,
  constraint job_batches_pkey primary key (id)
) TABLESPACE pg_default;

create table public.failed_jobs (
  id bigserial not null,
  uuid character varying(255) not null,
  connection text not null,
  queue text not null,
  payload text not null,
  exception text not null,
  failed_at timestamp without time zone not null default CURRENT_TIMESTAMP,
  constraint failed_jobs_pkey primary key (id),
  constraint failed_jobs_uuid_unique unique (uuid)
) TABLESPACE pg_default;


-- ============================================================
-- SK FEDERATION PORTAL TABLES (shared; formerly Admin app)
-- Accounts, audit logs, archive, barangay logos, official profiles
-- ============================================================

create table public.official_profiles (
  id bigserial not null,
  user_id bigint not null,
  first_name character varying(255) not null,
  last_name character varying(255) not null,
  middle_name character varying(100) null,
  suffix character varying(20) null,
  sex character varying(10) null,
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
            'President'::character varying,
            'Vice President'::character varying,
            'Secretary'::character varying,
            'Treasurer'::character varying,
            'PIO'::character varying,
            'Sergeant at Arms'::character varying,
            'Chairperson'::character varying,
            'Chairman'::character varying,
            'Kagawad'::character varying,
            'Councilor'::character varying,
            'Auditor'::character varying
          ]
        )::text[]
      )
    )
  )
) TABLESPACE pg_default;

create index IF not exists official_profiles_tenant_id_index on public.official_profiles using btree (tenant_id) TABLESPACE pg_default;

create table public.committees (
  id bigserial not null,
  committee_name character varying(255) not null,
  committee_head_id bigint not null,
  description text null,
  created_at timestamp without time zone null default CURRENT_TIMESTAMP,
  updated_at timestamp without time zone null default CURRENT_TIMESTAMP,
  constraint committees_pkey primary key (id),
  constraint committees_committee_head_id_foreign foreign KEY (committee_head_id) references users (id) on delete CASCADE,
  constraint committees_committee_head_id_unique unique (committee_head_id)
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
where ((status)::text = 'ACTIVE'::text);

-- Completed-term archive snapshots (SK Officials)
create table public.archived_sk_official_records (
  id bigserial not null,
  user_id bigint null,
  official_profile_id bigint null,
  official_term_id bigint null,
  tenant_id bigint not null,
  barangay_id bigint null,
  first_name character varying(255) not null,
  last_name character varying(255) not null,
  middle_name character varying(100) null,
  suffix character varying(20) null,
  sex character varying(10) null,
  date_of_birth date null,
  age smallint null,
  contact_number character varying(20) null,
  position character varying(255) not null,
  municipality character varying(255) not null default 'Santa Cruz'::character varying,
  province character varying(255) not null default 'Laguna'::character varying,
  region character varying(255) not null default 'IV-A CALABARZON'::character varying,
  email character varying(255) null,
  term_start date not null,
  term_end date not null,
  term_status character varying(30) not null,
  archived_at timestamp without time zone not null,
  archived_by bigint null,
  created_at timestamp without time zone null,
  updated_at timestamp without time zone null,
  constraint archived_sk_official_records_pkey primary key (id),
  constraint archived_sk_official_records_user_id_foreign foreign KEY (user_id) references users (id) on delete set null,
  constraint archived_sk_official_records_tenant_id_foreign foreign KEY (tenant_id) references tenants (id) on delete CASCADE,
  constraint archived_sk_official_records_barangay_id_foreign foreign KEY (barangay_id) references barangays (id) on delete set null,
  constraint archived_sk_official_records_archived_by_foreign foreign KEY (archived_by) references users (id) on delete set null,
  constraint archived_sk_official_records_official_term_id_unique unique (official_term_id)
) TABLESPACE pg_default;

create index IF not exists archived_sk_official_records_tenant_term_end_idx on public.archived_sk_official_records using btree (tenant_id, term_end) TABLESPACE pg_default;
create index IF not exists archived_sk_official_records_barangay_term_idx on public.archived_sk_official_records using btree (barangay_id, term_start, term_end) TABLESPACE pg_default;

-- Completed-term archive snapshots (SK Federation)
create table public.archived_sk_federation_records (
  id bigserial not null,
  user_id bigint null,
  official_profile_id bigint null,
  official_term_id bigint null,
  tenant_id bigint not null,
  first_name character varying(255) not null,
  last_name character varying(255) not null,
  middle_name character varying(100) null,
  suffix character varying(20) null,
  sex character varying(10) null,
  date_of_birth date null,
  age smallint null,
  contact_number character varying(20) null,
  position character varying(255) not null,
  municipality character varying(255) not null default 'Santa Cruz'::character varying,
  province character varying(255) not null default 'Laguna'::character varying,
  region character varying(255) not null default 'IV-A CALABARZON'::character varying,
  email character varying(255) null,
  term_start date not null,
  term_end date not null,
  term_status character varying(30) not null,
  archived_at timestamp without time zone not null,
  archived_by bigint null,
  created_at timestamp without time zone null,
  updated_at timestamp without time zone null,
  constraint archived_sk_federation_records_pkey primary key (id),
  constraint archived_sk_federation_records_user_id_foreign foreign KEY (user_id) references users (id) on delete set null,
  constraint archived_sk_federation_records_tenant_id_foreign foreign KEY (tenant_id) references tenants (id) on delete CASCADE,
  constraint archived_sk_federation_records_archived_by_foreign foreign KEY (archived_by) references users (id) on delete set null,
  constraint archived_sk_federation_records_official_term_id_unique unique (official_term_id)
) TABLESPACE pg_default;

create index IF not exists archived_sk_federation_records_tenant_term_end_idx on public.archived_sk_federation_records using btree (tenant_id, term_end) TABLESPACE pg_default;

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

-- Audit trail used by SK Federation portal (admin_activity_logs name kept for compatibility)
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

-- Added: 2026-05-03 (managed by SK Federation portal)
create table public.barangay_logos (
  id bigserial not null,
  barangay_id bigint not null,
  tenant_id bigint not null,
  uploaded_by bigint not null,
  cloudinary_public_id character varying(255) not null,
  cloudinary_version character varying(32) null,
  url character varying(255) not null,
  created_at timestamp without time zone null,
  updated_at timestamp without time zone null,
  constraint barangay_logos_pkey primary key (id),
  constraint barangay_logos_barangay_id_tenant_id_unique unique (barangay_id, tenant_id),
  constraint barangay_logos_barangay_id_foreign foreign KEY (barangay_id) references barangays (id) on delete CASCADE,
  constraint barangay_logos_tenant_id_foreign foreign KEY (tenant_id) references tenants (id) on delete CASCADE,
  constraint barangay_logos_uploaded_by_foreign foreign KEY (uploaded_by) references users (id) on delete CASCADE
) TABLESPACE pg_default;


-- ============================================================
-- SK_FEDERATIONS AUTH TABLES
-- ============================================================

create table public.sk_fed_trusted_devices (
  id bigserial not null,
  user_id bigint not null,
  fingerprint character varying(128) not null,
  device_token_hash character varying(64) null,
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
create index IF not exists sk_fed_trusted_device_token_idx on public.sk_fed_trusted_devices using btree (user_id, device_token_hash) TABLESPACE pg_default;

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


-- ============================================================
-- SK_OFFICIALS AUTH TABLES
-- ============================================================

create table public.sk_official_trusted_devices (
  id bigserial not null,
  user_id bigint not null,
  fingerprint character varying(128) not null,
  device_token_hash character varying(64) null,
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

create index IF not exists sk_official_trusted_device_token_idx on public.sk_official_trusted_devices using btree (user_id, device_token_hash) TABLESPACE pg_default;

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

-- ============================================================
-- SK_OFFICIALS FEATURE TABLES (Added: 2026-05-02 / 2026-05-03)
-- ============================================================

-- Announcements (created by SK Officials, visible to Kabataan via community feed)
-- Note: barangay_id is nullable to support federation-wide posts (is_federation_wide = true)
-- Note: images live in announcement_images (legacy image_url removed)
create table public.announcements (
  id bigserial not null,
  user_id bigint not null,
  barangay_id bigint null,
  type character varying(20) not null default 'announcement'::character varying,
  title character varying(255) null,
  body text not null,
  link_url text null,
  is_federation_wide boolean not null default false,
  is_archived boolean not null default false,
  archived_at timestamp without time zone null,
  deleted_at timestamp without time zone null,
  created_at timestamp without time zone null,
  updated_at timestamp without time zone null,
  constraint announcements_pkey primary key (id),
  constraint announcements_user_id_foreign foreign KEY (user_id) references users (id) on delete CASCADE,
  constraint announcements_barangay_id_foreign foreign KEY (barangay_id) references barangays (id) on delete CASCADE,
  constraint announcements_type_check check (
    (type)::text = any (
      array[
        'announcement'::text,
        'event'::text,
        'activity'::text,
        'program'::text,
        'update'::text
      ]
    )
  )
) TABLESPACE pg_default;

create index IF not exists announcements_barangay_id_created_at_index on public.announcements using btree (barangay_id, created_at) TABLESPACE pg_default;
create index IF not exists announcements_barangay_archive_idx on public.announcements using btree (barangay_id, is_archived, archived_at) TABLESPACE pg_default;

create table public.announcement_images (
  id bigserial not null,
  announcement_id bigint not null,
  image_url text not null,
  public_id character varying(255) null,
  sort_order smallint not null default 0,
  created_at timestamp without time zone null,
  constraint announcement_images_pkey primary key (id),
  constraint announcement_images_announcement_id_foreign foreign KEY (announcement_id) references announcements (id) on delete CASCADE
) TABLESPACE pg_default;

create index IF not exists announcement_images_announcement_id_sort_order_index on public.announcement_images using btree (announcement_id, sort_order) TABLESPACE pg_default;

-- Polymorphic reactions on announcements (user_type: 'sk_official' | 'kabataan')
create table public.announcement_reactions (
  id bigserial not null,
  announcement_id bigint not null,
  user_id bigint not null,
  user_type character varying(20) not null,
  created_at timestamp without time zone null,
  updated_at timestamp without time zone null,
  constraint announcement_reactions_pkey primary key (id),
  constraint announcement_reactions_announcement_id_foreign foreign KEY (announcement_id) references announcements (id) on delete CASCADE,
  constraint announcement_reactions_unique unique (announcement_id, user_id, user_type)
) TABLESPACE pg_default;

-- Polymorphic comments on announcements (user_type: 'sk_official' | 'kabataan')
create table public.announcement_comments (
  id bigserial not null,
  announcement_id bigint not null,
  user_id bigint not null,
  user_type character varying(20) not null,
  author_name character varying(255) not null,
  body text not null,
  created_at timestamp without time zone null,
  updated_at timestamp without time zone null,
  constraint announcement_comments_pkey primary key (id),
  constraint announcement_comments_announcement_id_foreign foreign KEY (announcement_id) references announcements (id) on delete CASCADE
) TABLESPACE pg_default;

create index IF not exists announcement_comments_announcement_id_created_at_index on public.announcement_comments using btree (announcement_id, created_at) TABLESPACE pg_default;

-- KK Profiling schedules set by SK Officials per barangay
create table public.kk_profiling_schedules (
  id bigserial not null,
  tenant_id bigint not null,
  barangay_id bigint not null,
  created_by bigint not null,
  date_start date not null,
  date_expiry date not null,
  link character varying(300) null,
  status character varying(20) not null default 'Upcoming'::character varying,
  created_at timestamp without time zone null,
  updated_at timestamp without time zone null,
  constraint kk_profiling_schedules_pkey primary key (id),
  constraint kk_profiling_schedules_tenant_id_foreign foreign KEY (tenant_id) references tenants (id) on delete CASCADE,
  constraint kk_profiling_schedules_barangay_id_foreign foreign KEY (barangay_id) references barangays (id) on delete CASCADE,
  constraint kk_profiling_schedules_created_by_foreign foreign KEY (created_by) references users (id) on delete CASCADE,
  constraint kk_profiling_schedules_status_check check (
    (status)::text = any (
      array[
        'Upcoming'::text,
        'Ongoing'::text,
        'Completed'::text,
        'Cancelled'::text,
        'Rescheduled'::text
      ]
    )
  )
) TABLESPACE pg_default;

create index IF not exists kk_profiling_schedules_barangay_id_status_index on public.kk_profiling_schedules using btree (barangay_id, status) TABLESPACE pg_default;
create index IF not exists kk_profiling_schedules_date_start_date_expiry_index on public.kk_profiling_schedules using btree (date_start, date_expiry) TABLESPACE pg_default;

-- ============================================================
-- KABATAAN APP TABLES (Added: 2026-05-02)
-- ============================================================

-- Kabataan registration with multi-step state machine
-- evaluation_status / evaluation_notes added by SK Officials for review workflow
create table public.kabataan_registrations (
  id bigserial not null,
  tenant_id bigint not null,
  barangay_id bigint not null,
  user_id bigint null,
  reviewed_by_user_id bigint null,
  last_name character varying(100) not null,
  first_name character varying(100) not null,
  middle_name character varying(100) null,
  suffix character varying(10) null,
  email character varying(150) null,
  contact_number character varying(15) null,
  respondent_number character varying(32) null,
  respondent_sequence integer null,
  form_data json not null,
  status character varying(30) not null default 'pending_verification'::character varying,
  evaluation_status character varying(30) null,
  evaluation_notes json null,
  submitted_at timestamp without time zone null,
  email_verified_at timestamp without time zone null,
  password_set_at timestamp without time zone null,
  reviewed_at timestamp without time zone null,
  review_notes text null,
  created_at timestamp without time zone null,
  updated_at timestamp without time zone null,
  deleted_at timestamp without time zone null,
  constraint kabataan_registrations_pkey primary key (id),
  constraint kabataan_registrations_tenant_id_foreign foreign KEY (tenant_id) references tenants (id) on delete CASCADE,
  constraint kabataan_registrations_barangay_id_foreign foreign KEY (barangay_id) references barangays (id) on delete CASCADE,
  constraint kabataan_registrations_user_id_foreign foreign KEY (user_id) references users (id) on delete set null,
  constraint kabataan_registrations_reviewed_by_user_id_foreign foreign KEY (reviewed_by_user_id) references users (id) on delete set null,
  constraint kabataan_registrations_status_check check (
    (status)::text = any (
      array[
        'pending_verification'::text,
        'email_verified'::text,
        'password_set'::text,
        'active'::text,
        'rejected'::text
      ]
    )
  )
) TABLESPACE pg_default;

create index IF not exists kabataan_registrations_tenant_id_barangay_id_index on public.kabataan_registrations using btree (tenant_id, barangay_id) TABLESPACE pg_default;
create index IF not exists kabataan_registrations_status_index on public.kabataan_registrations using btree (status) TABLESPACE pg_default;
create index IF not exists kabataan_registrations_email_index on public.kabataan_registrations using btree (email) TABLESPACE pg_default;
create index IF not exists kabataan_registrations_submitted_at_index on public.kabataan_registrations using btree (submitted_at) TABLESPACE pg_default;
create index IF not exists kabataan_registrations_deleted_at_index on public.kabataan_registrations using btree (deleted_at) TABLESPACE pg_default;
create unique index IF not exists kabataan_registrations_unique_respondent on public.kabataan_registrations using btree (tenant_id, barangay_id, respondent_number) TABLESPACE pg_default
where (respondent_number is not null);

-- Archive of kabataan records moved out of active profiling
create table public.previous_kabataan (
  id bigserial not null,
  kabataan_registration_id bigint null,
  tenant_id bigint not null,
  barangay_id bigint not null,
  moved_by_user_id bigint null,
  last_name character varying(100) not null,
  first_name character varying(100) not null,
  middle_name character varying(100) null,
  suffix character varying(10) null,
  email character varying(150) null,
  contact_number character varying(15) null,
  form_data json not null,
  profiling_year smallint not null,
  moved_at timestamp without time zone null,
  created_at timestamp without time zone null,
  updated_at timestamp without time zone null,
  constraint previous_kabataan_pkey primary key (id),
  constraint previous_kabataan_kabataan_registration_id_foreign foreign KEY (kabataan_registration_id) references kabataan_registrations (id) on delete set null,
  constraint previous_kabataan_tenant_id_foreign foreign KEY (tenant_id) references tenants (id) on delete CASCADE,
  constraint previous_kabataan_barangay_id_foreign foreign KEY (barangay_id) references barangays (id) on delete CASCADE,
  constraint previous_kabataan_moved_by_user_id_foreign foreign KEY (moved_by_user_id) references users (id) on delete set null
) TABLESPACE pg_default;

create index IF not exists previous_kabataan_tenant_id_barangay_id_index on public.previous_kabataan using btree (tenant_id, barangay_id) TABLESPACE pg_default;
create index IF not exists previous_kabataan_profiling_year_index on public.previous_kabataan using btree (profiling_year) TABLESPACE pg_default;

-- Kabataan account settings (change email / change password verification)
ALTER TABLE users
ADD COLUMN IF NOT EXISTS pending_email VARCHAR(255) NULL,
ADD COLUMN IF NOT EXISTS email_change_token VARCHAR(255) NULL,
ADD COLUMN IF NOT EXISTS email_change_token_expires_at TIMESTAMP NULL,
ADD COLUMN IF NOT EXISTS email_change_verified_at TIMESTAMP NULL,
ADD COLUMN IF NOT EXISTS email_change_last_sent_at TIMESTAMP NULL,
ADD COLUMN IF NOT EXISTS pending_password VARCHAR(255) NULL,
ADD COLUMN IF NOT EXISTS password_change_token VARCHAR(255) NULL,
ADD COLUMN IF NOT EXISTS password_change_token_expires_at TIMESTAMP NULL,
ADD COLUMN IF NOT EXISTS password_change_last_sent_at TIMESTAMP NULL,
ADD COLUMN IF NOT EXISTS must_change_password BOOLEAN NOT NULL DEFAULT false;

ALTER TABLE kabataan_registrations
ADD COLUMN IF NOT EXISTS respondent_number VARCHAR(32) NULL,
ADD COLUMN IF NOT EXISTS respondent_sequence INTEGER NULL,
ADD COLUMN IF NOT EXISTS deleted_at TIMESTAMP NULL;

-- Rejected KK profiling archive (one row per rejected registration)
CREATE TABLE IF NOT EXISTS rejected_kk_profiling (
  id BIGSERIAL NOT NULL,
  kabataan_registration_id BIGINT NOT NULL,
  tenant_id BIGINT NULL,
  barangay_id BIGINT NOT NULL,
  rejected_by_user_id BIGINT NULL,
  rejection_reason TEXT NOT NULL,
  rejected_at TIMESTAMP NOT NULL,
  restored_at TIMESTAMP NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  CONSTRAINT rejected_kk_profiling_pkey PRIMARY KEY (id),
  CONSTRAINT rejected_kk_profiling_kabataan_registration_id_unique UNIQUE (kabataan_registration_id),
  CONSTRAINT rejected_kk_profiling_kabataan_registration_id_foreign FOREIGN KEY (kabataan_registration_id) REFERENCES kabataan_registrations (id) ON DELETE CASCADE,
  CONSTRAINT rejected_kk_profiling_tenant_id_foreign FOREIGN KEY (tenant_id) REFERENCES tenants (id) ON DELETE SET NULL,
  CONSTRAINT rejected_kk_profiling_barangay_id_foreign FOREIGN KEY (barangay_id) REFERENCES barangays (id) ON DELETE CASCADE,
  CONSTRAINT rejected_kk_profiling_rejected_by_user_id_foreign FOREIGN KEY (rejected_by_user_id) REFERENCES users (id) ON DELETE SET NULL
);

CREATE INDEX IF NOT EXISTS rejected_kk_profiling_barangay_id_rejected_at_index ON rejected_kk_profiling (barangay_id, rejected_at);
CREATE INDEX IF NOT EXISTS rejected_kk_profiling_barangay_id_restored_at_index ON rejected_kk_profiling (barangay_id, restored_at);

ALTER TABLE rejected_kk_profiling
ADD COLUMN IF NOT EXISTS previous_registration_status VARCHAR(50) NULL,
ADD COLUMN IF NOT EXISTS previous_evaluation_status VARCHAR(50) NULL,
ADD COLUMN IF NOT EXISTS previous_user_status VARCHAR(50) NULL;

-- KK survey responses (analytics source; status: pending | rejected | approved)
CREATE TABLE IF NOT EXISTS kk_survey_responses (
  id BIGSERIAL NOT NULL,
  tenant_id BIGINT NOT NULL,
  barangay_id BIGINT NOT NULL,
  kabataan_registration_id BIGINT NOT NULL,
  respondent_number VARCHAR(50) NULL,
  survey_date DATE NULL,
  last_name VARCHAR(100) NOT NULL,
  first_name VARCHAR(100) NOT NULL,
  middle_name VARCHAR(100) NULL,
  suffix VARCHAR(50) NULL,
  region VARCHAR(100) NULL,
  province VARCHAR(100) NULL,
  municipality VARCHAR(100) NULL,
  barangay VARCHAR(100) NULL,
  purok_zone VARCHAR(100) NULL,
  sex_assigned_at_birth VARCHAR(20) NULL,
  age INTEGER NULL,
  birthdate DATE NULL,
  email VARCHAR(255) NULL,
  contact_number VARCHAR(20) NULL,
  civil_status VARCHAR(50) NULL,
  youth_age_group VARCHAR(50) NULL,
  educational_background VARCHAR(100) NULL,
  youth_classification VARCHAR(100) NULL,
  work_status VARCHAR(100) NULL,
  registered_sk_voter BOOLEAN NOT NULL DEFAULT false,
  registered_national_voter BOOLEAN NOT NULL DEFAULT false,
  attended_kk_assembly BOOLEAN NOT NULL DEFAULT false,
  voted_last_sk BOOLEAN NOT NULL DEFAULT false,
  kk_assembly_attendance_count VARCHAR(255) NULL,
  kk_assembly_non_attendance_reason TEXT NULL,
  facebook_account VARCHAR(255) NULL,
  willing_to_join_group_chat BOOLEAN NOT NULL DEFAULT false,
  participant_signature TEXT NULL,
  consent_given BOOLEAN NOT NULL DEFAULT true,
  status VARCHAR(30) NOT NULL DEFAULT 'pending',
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  CONSTRAINT kk_survey_responses_pkey PRIMARY KEY (id),
  CONSTRAINT kk_survey_responses_kabataan_registration_id_unique UNIQUE (kabataan_registration_id),
  CONSTRAINT kk_survey_responses_tenant_id_foreign FOREIGN KEY (tenant_id) REFERENCES tenants (id) ON DELETE CASCADE,
  CONSTRAINT kk_survey_responses_barangay_id_foreign FOREIGN KEY (barangay_id) REFERENCES barangays (id) ON DELETE CASCADE,
  CONSTRAINT kk_survey_responses_kabataan_registration_id_foreign FOREIGN KEY (kabataan_registration_id) REFERENCES kabataan_registrations (id) ON DELETE CASCADE
);

CREATE INDEX IF NOT EXISTS kk_survey_responses_barangay_id_status_index ON kk_survey_responses (barangay_id, status);
CREATE INDEX IF NOT EXISTS kk_survey_responses_barangay_id_survey_date_index ON kk_survey_responses (barangay_id, survey_date);

-- ABYIP unified schema (document header + line items in one table)
DROP TABLE IF EXISTS abyip_program_activities;
DROP TABLE IF EXISTS abyip_programs;
DROP TABLE IF EXISTS abyip_detected_programs;
DROP TABLE IF EXISTS abyips;

CREATE TABLE IF NOT EXISTS calendar_notes (
  id BIGSERIAL NOT NULL,
  barangay_id BIGINT NOT NULL,
  user_id BIGINT NULL,
  note_date DATE NOT NULL,
  title VARCHAR(255) NOT NULL,
  content VARCHAR(500) NOT NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  CONSTRAINT calendar_notes_pkey PRIMARY KEY (id),
  CONSTRAINT calendar_notes_barangay_id_note_date_unique UNIQUE (barangay_id, note_date),
  CONSTRAINT calendar_notes_barangay_id_foreign FOREIGN KEY (barangay_id) REFERENCES barangays (id) ON DELETE CASCADE,
  CONSTRAINT calendar_notes_user_id_foreign FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE SET NULL
);

CREATE INDEX IF NOT EXISTS calendar_notes_barangay_id_note_date_index ON calendar_notes (barangay_id, note_date);

ALTER TABLE official_profiles
ADD COLUMN IF NOT EXISTS sex VARCHAR(10) NULL;

CREATE TABLE IF NOT EXISTS committees (
    id BIGSERIAL PRIMARY KEY,
    committee_name VARCHAR(255) NOT NULL,
    committee_head_id BIGINT NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT committees_committee_head_id_foreign FOREIGN KEY (committee_head_id) REFERENCES users (id) ON DELETE CASCADE,
    CONSTRAINT committees_committee_head_id_unique UNIQUE (committee_head_id)
);

-- ABYIP: unified document header + expenditure/youth program/activity rows
create table public.abyip (
  id bigserial not null,
  document_id bigint null,
  tenant_id bigint null,
  barangay_id bigint not null,
  created_by bigint null,
  fiscal_year smallint not null,
  country character varying(100) not null default 'Republic of the Philippines'::character varying,
  region character varying(100) null,
  province character varying(100) null,
  municipality character varying(100) null,
  barangay_name character varying(255) null,
  document_title character varying(255) not null default ''::character varying,
  sk_council_name character varying(255) null,
  barangay_estimated_budget numeric(15, 2) not null default 0,
  sk_fund_percentage numeric(5, 2) not null default 10.00,
  sk_fund_amount numeric(15, 2) not null default 0,
  total_budget numeric(15, 2) null,
  prepared_by character varying(255) null,
  prepared_position character varying(255) null,
  prepared_by_user_id bigint null,
  approved_by character varying(255) null,
  approved_position character varying(255) null,
  approved_by_user_id bigint null,
  status character varying(30) null,
  reviewed_at timestamp(0) without time zone null,
  reviewed_by_user_id bigint null,
  rejection_reason text null,
  prepared_by_name character varying(255) null,
  prepared_by_position character varying(255) null,
  approved_by_name character varying(255) null,
  approved_by_position character varying(255) null,
  source_type character varying(20) not null default 'word'::character varying,
  document_html text null,
  pdf_data text null,
  row_type character varying(30) not null default 'document'::character varying,
  parent_id bigint null,
  code character varying(20) null,
  program_name character varying(255) null,
  description text null,
  expected_result text null,
  performance_indicator text null,
  implementation_period character varying(255) null,
  person_responsible character varying(255) null,
  mooe numeric(15, 2) null,
  co numeric(15, 2) null,
  total numeric(15, 2) null,
  budget numeric(15, 2) null,
  sort_order integer null,
  created_at timestamp without time zone null,
  updated_at timestamp without time zone null,
  constraint abyip_pkey primary key (id),
  constraint abyip_barangay_id_foreign foreign KEY (barangay_id) references barangays (id) on delete CASCADE,
  constraint abyip_created_by_foreign foreign KEY (created_by) references users (id) on delete set null,
  constraint abyip_tenant_id_foreign foreign KEY (tenant_id) references tenants (id) on delete set null,
  constraint abyip_document_id_foreign foreign KEY (document_id) references abyip (id) on delete CASCADE,
  constraint abyip_parent_id_foreign foreign KEY (parent_id) references abyip (id) on delete CASCADE,
  constraint fk_abyip_prepared_by foreign KEY (prepared_by_user_id) references users (id) on delete set null,
  constraint fk_abyip_approved_by foreign KEY (approved_by_user_id) references users (id) on delete set null
) TABLESPACE pg_default;

create unique index IF not exists abyip_barangay_fiscal_year_document_idx on public.abyip using btree (barangay_id, fiscal_year) where (row_type = 'document'::character varying);
create index IF not exists abyip_barangay_id_fiscal_year_index on public.abyip using btree (barangay_id, fiscal_year) TABLESPACE pg_default;
create index IF not exists abyip_document_id_index on public.abyip using btree (document_id) TABLESPACE pg_default;
create index IF not exists abyip_document_id_row_type_index on public.abyip using btree (document_id, row_type) TABLESPACE pg_default;
create index IF not exists abyip_parent_id_index on public.abyip using btree (parent_id) TABLESPACE pg_default;

create table public.calendar_notes (
  id bigserial not null,
  barangay_id bigint not null,
  user_id bigint null,
  note_date date not null,
  title character varying(255) not null,
  content character varying(500) not null,
  created_at timestamp without time zone null,
  updated_at timestamp without time zone null,
  constraint calendar_notes_pkey primary key (id),
  constraint calendar_notes_barangay_id_note_date_unique unique (barangay_id, note_date),
  constraint calendar_notes_barangay_id_foreign foreign KEY (barangay_id) references barangays (id) on delete CASCADE,
  constraint calendar_notes_user_id_foreign foreign KEY (user_id) references users (id) on delete set null
) TABLESPACE pg_default;

create index IF not exists calendar_notes_barangay_id_note_date_index on public.calendar_notes using btree (barangay_id, note_date) TABLESPACE pg_default;

-- SK Officials online presence + activity audit log
ALTER TABLE users
ADD COLUMN IF NOT EXISTS online_status VARCHAR(20) NOT NULL DEFAULT 'offline';

CREATE TABLE IF NOT EXISTS sk_official_activities (
  id BIGSERIAL NOT NULL,
  tenant_id BIGINT NULL,
  barangay_id BIGINT NOT NULL,
  user_id BIGINT NOT NULL,
  action VARCHAR(80) NOT NULL,
  description VARCHAR(500) NOT NULL,
  metadata JSON NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  CONSTRAINT sk_official_activities_pkey PRIMARY KEY (id),
  CONSTRAINT sk_official_activities_tenant_id_foreign FOREIGN KEY (tenant_id) REFERENCES tenants (id) ON DELETE SET NULL,
  CONSTRAINT sk_official_activities_barangay_id_foreign FOREIGN KEY (barangay_id) REFERENCES barangays (id) ON DELETE CASCADE,
  CONSTRAINT sk_official_activities_user_id_foreign FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) TABLESPACE pg_default;

CREATE INDEX IF NOT EXISTS sk_official_activities_barangay_id_created_at_index
  ON public.sk_official_activities USING btree (barangay_id, created_at) TABLESPACE pg_default;

CREATE INDEX IF NOT EXISTS sk_official_activities_user_id_index
  ON public.sk_official_activities USING btree (user_id) TABLESPACE pg_default;

-- Scholarship / program schedule programs (Equitable Access to Quality Education)
CREATE TABLE IF NOT EXISTS schedule_programs (
  id BIGSERIAL NOT NULL,
  tenant_id BIGINT NULL,
  barangay_id BIGINT NOT NULL,
  created_by BIGINT NULL,
  program_type VARCHAR(255) NOT NULL,
  committee VARCHAR(255) NOT NULL,
  program_name VARCHAR(255) NOT NULL,
  program_letter VARCHAR(1) NULL,
  participation_quantity INTEGER NULL,
  start_date DATE NOT NULL,
  end_date DATE NOT NULL,
  status VARCHAR(20) NOT NULL DEFAULT 'open',
  is_archived BOOLEAN NOT NULL DEFAULT FALSE,
  archived_at TIMESTAMP NULL,
  archived_by BIGINT NULL,
  deleted_reason TEXT NULL,
  restored_at TIMESTAMP NULL,
  restored_by BIGINT NULL,
  announcement TEXT NULL,
  kk_profiling_fields JSON NULL,
  custom_questions JSON NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  CONSTRAINT schedule_programs_pkey PRIMARY KEY (id),
  CONSTRAINT schedule_programs_tenant_id_foreign FOREIGN KEY (tenant_id) REFERENCES tenants (id) ON DELETE SET NULL,
  CONSTRAINT schedule_programs_barangay_id_foreign FOREIGN KEY (barangay_id) REFERENCES barangays (id) ON DELETE CASCADE,
  CONSTRAINT schedule_programs_created_by_foreign FOREIGN KEY (created_by) REFERENCES users (id) ON DELETE SET NULL,
  CONSTRAINT schedule_programs_archived_by_foreign FOREIGN KEY (archived_by) REFERENCES users (id) ON DELETE SET NULL,
  CONSTRAINT schedule_programs_restored_by_foreign FOREIGN KEY (restored_by) REFERENCES users (id) ON DELETE SET NULL
);

CREATE INDEX IF NOT EXISTS schedule_programs_barangay_id_status_index ON schedule_programs (barangay_id, status);
CREATE INDEX IF NOT EXISTS schedule_programs_barangay_id_dates_index ON schedule_programs (barangay_id, start_date, end_date);
CREATE INDEX IF NOT EXISTS schedule_programs_barangay_id_program_letter_index ON schedule_programs (barangay_id, program_letter);
CREATE INDEX IF NOT EXISTS schedule_programs_barangay_letter_archived_index ON schedule_programs (barangay_id, program_letter, is_archived);
CREATE INDEX IF NOT EXISTS schedule_programs_archived_at_index ON schedule_programs (is_archived, archived_at);

CREATE TABLE IF NOT EXISTS program_applications (
  id BIGSERIAL NOT NULL,
  program_id BIGINT NULL,
  kabataan_id BIGINT NULL,
  first_name VARCHAR(255) NULL,
  middle_name VARCHAR(255) NULL,
  last_name VARCHAR(255) NULL,
  suffix VARCHAR(50) NULL,
  birthdate DATE NULL,
  age INTEGER NULL,
  sex VARCHAR(50) NULL,
  civil_status VARCHAR(50) NULL,
  purok VARCHAR(255) NULL,
  barangay VARCHAR(255) NULL,
  email VARCHAR(255) NULL,
  contact_number VARCHAR(50) NULL,
  parent_guardian_name VARCHAR(255) NULL,
  parent_occupation VARCHAR(255) NULL,
  parent_income NUMERIC(12, 2) NULL,
  school_name VARCHAR(255) NULL,
  grade_level VARCHAR(100) NULL,
  course VARCHAR(255) NULL,
  gwa NUMERIC(5, 2) NULL,
  custom_answers JSON NULL,
  required_documents JSON NULL,
  purpose TEXT NULL,
  remarks TEXT NULL,
  status VARCHAR(20) NULL DEFAULT 'pending',
  cancel_reason TEXT NULL,
  payment_status VARCHAR(20) NULL,
  rejection_reason TEXT NULL,
  rejection_reasons JSON NULL,
  reviewed_by BIGINT NULL,
  reviewed_at TIMESTAMP NULL,
  deleted_at TIMESTAMP NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  CONSTRAINT program_applications_pkey PRIMARY KEY (id)
);

CREATE UNIQUE INDEX IF NOT EXISTS program_applications_kabataan_program_unique
  ON program_applications (kabataan_id, program_id);

CREATE TABLE IF NOT EXISTS rejected_scholarships (
  id BIGSERIAL NOT NULL,
  program_application_id BIGINT NOT NULL,
  tenant_id BIGINT NULL,
  barangay_id BIGINT NOT NULL,
  rejected_by_user_id BIGINT NULL,
  rejection_reason TEXT NULL,
  rejection_reasons JSON NULL,
  rejected_at TIMESTAMP NOT NULL,
  restored_at TIMESTAMP NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  CONSTRAINT rejected_scholarships_pkey PRIMARY KEY (id),
  CONSTRAINT rejected_scholarships_program_application_id_unique UNIQUE (program_application_id),
  CONSTRAINT rejected_scholarships_program_application_id_foreign FOREIGN KEY (program_application_id) REFERENCES program_applications (id) ON DELETE CASCADE,
  CONSTRAINT rejected_scholarships_tenant_id_foreign FOREIGN KEY (tenant_id) REFERENCES tenants (id) ON DELETE SET NULL,
  CONSTRAINT rejected_scholarships_barangay_id_foreign FOREIGN KEY (barangay_id) REFERENCES barangays (id) ON DELETE CASCADE,
  CONSTRAINT rejected_scholarships_rejected_by_user_id_foreign FOREIGN KEY (rejected_by_user_id) REFERENCES users (id) ON DELETE SET NULL
);

CREATE INDEX IF NOT EXISTS rejected_scholarships_barangay_id_rejected_at_index ON rejected_scholarships (barangay_id, rejected_at);
CREATE INDEX IF NOT EXISTS rejected_scholarships_barangay_id_restored_at_index ON rejected_scholarships (barangay_id, restored_at);

CREATE TABLE IF NOT EXISTS rejected_sports (
  id BIGSERIAL NOT NULL,
  program_application_id BIGINT NOT NULL,
  tenant_id BIGINT NULL,
  barangay_id BIGINT NOT NULL,
  rejected_by_user_id BIGINT NULL,
  rejection_reason TEXT NULL,
  rejection_reasons JSON NULL,
  rejected_at TIMESTAMP NOT NULL,
  restored_at TIMESTAMP NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  CONSTRAINT rejected_sports_pkey PRIMARY KEY (id),
  CONSTRAINT rejected_sports_program_application_id_unique UNIQUE (program_application_id),
  CONSTRAINT rejected_sports_program_application_id_foreign FOREIGN KEY (program_application_id) REFERENCES program_applications (id) ON DELETE CASCADE,
  CONSTRAINT rejected_sports_tenant_id_foreign FOREIGN KEY (tenant_id) REFERENCES tenants (id) ON DELETE SET NULL,
  CONSTRAINT rejected_sports_barangay_id_foreign FOREIGN KEY (barangay_id) REFERENCES barangays (id) ON DELETE CASCADE,
  CONSTRAINT rejected_sports_rejected_by_user_id_foreign FOREIGN KEY (rejected_by_user_id) REFERENCES users (id) ON DELETE SET NULL
);

CREATE INDEX IF NOT EXISTS rejected_sports_barangay_id_rejected_at_index ON rejected_sports (barangay_id, rejected_at);
CREATE INDEX IF NOT EXISTS rejected_sports_barangay_id_restored_at_index ON rejected_sports (barangay_id, restored_at);

-- Program surveys (SK Officials create; Kabataan responds)
CREATE TABLE IF NOT EXISTS program_surveys (
  id BIGSERIAL NOT NULL,
  tenant_id BIGINT NOT NULL,
  barangay_id BIGINT NOT NULL,
  abyip_id BIGINT NOT NULL,
  abyip_program_id BIGINT NOT NULL,
  announcement TEXT NOT NULL,
  instructions TEXT NOT NULL,
  open_date DATE NOT NULL,
  close_date DATE NOT NULL,
  status VARCHAR(30) NOT NULL,
  created_by BIGINT NOT NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  CONSTRAINT program_surveys_pkey PRIMARY KEY (id),
  CONSTRAINT program_surveys_tenant_id_foreign FOREIGN KEY (tenant_id) REFERENCES tenants (id) ON DELETE CASCADE,
  CONSTRAINT program_surveys_barangay_id_foreign FOREIGN KEY (barangay_id) REFERENCES barangays (id) ON DELETE CASCADE,
  CONSTRAINT program_surveys_abyip_id_foreign FOREIGN KEY (abyip_id) REFERENCES abyip (id) ON DELETE CASCADE,
  CONSTRAINT program_surveys_abyip_program_id_foreign FOREIGN KEY (abyip_program_id) REFERENCES abyip (id) ON DELETE CASCADE,
  CONSTRAINT program_surveys_created_by_foreign FOREIGN KEY (created_by) REFERENCES users (id) ON DELETE CASCADE
);

CREATE INDEX IF NOT EXISTS program_surveys_barangay_id_status_index
  ON program_surveys (barangay_id, status);
CREATE INDEX IF NOT EXISTS program_surveys_barangay_id_abyip_program_id_index
  ON program_surveys (barangay_id, abyip_program_id);
CREATE INDEX IF NOT EXISTS program_surveys_barangay_id_open_date_close_date_index
  ON program_surveys (barangay_id, open_date, close_date);

CREATE UNIQUE INDEX IF NOT EXISTS program_surveys_barangay_program_year_unique
  ON program_surveys (barangay_id, abyip_program_id, (EXTRACT(YEAR FROM open_date)));

CREATE TABLE IF NOT EXISTS program_survey_questions (
  id BIGSERIAL NOT NULL,
  survey_id BIGINT NOT NULL,
  question_label TEXT NOT NULL,
  input_type VARCHAR(50) NOT NULL,
  is_required BOOLEAN NOT NULL DEFAULT TRUE,
  options JSON NULL,
  sort_order INTEGER NOT NULL DEFAULT 0,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  CONSTRAINT program_survey_questions_pkey PRIMARY KEY (id),
  CONSTRAINT program_survey_questions_survey_id_foreign FOREIGN KEY (survey_id) REFERENCES program_surveys (id) ON DELETE CASCADE
);

CREATE INDEX IF NOT EXISTS program_survey_questions_survey_id_sort_order_index
  ON program_survey_questions (survey_id, sort_order);

CREATE TABLE IF NOT EXISTS program_survey_responses (
  id BIGSERIAL NOT NULL,
  survey_id BIGINT NOT NULL,
  registration_id BIGINT NOT NULL,
  submitted_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT program_survey_responses_pkey PRIMARY KEY (id),
  CONSTRAINT program_survey_responses_survey_id_foreign FOREIGN KEY (survey_id) REFERENCES program_surveys (id) ON DELETE CASCADE,
  CONSTRAINT program_survey_responses_registration_id_foreign FOREIGN KEY (registration_id) REFERENCES kabataan_registrations (id) ON DELETE CASCADE
);

CREATE INDEX IF NOT EXISTS program_survey_responses_survey_id_submitted_at_index
  ON program_survey_responses (survey_id, submitted_at);

CREATE TABLE IF NOT EXISTS program_survey_response_answers (
  id BIGSERIAL NOT NULL,
  response_id BIGINT NOT NULL,
  question_id BIGINT NOT NULL,
  answer TEXT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT program_survey_response_answers_pkey PRIMARY KEY (id),
  CONSTRAINT program_survey_response_answers_response_id_foreign FOREIGN KEY (response_id) REFERENCES program_survey_responses (id) ON DELETE CASCADE,
  CONSTRAINT program_survey_response_answers_question_id_foreign FOREIGN KEY (question_id) REFERENCES program_survey_questions (id) ON DELETE CASCADE
);

CREATE INDEX IF NOT EXISTS program_survey_response_answers_response_id_question_id_index
  ON program_survey_response_answers (response_id, question_id);

CREATE OR REPLACE FUNCTION generate_respondent_number(
  p_tenant_id BIGINT,
  p_barangay_id BIGINT
)
RETURNS TEXT
LANGUAGE plpgsql
AS $$
DECLARE
  current_year TEXT;
  next_seq INTEGER;
  barangay_prefix TEXT;
BEGIN
  current_year := EXTRACT(YEAR FROM CURRENT_DATE)::TEXT;

  SELECT UPPER(LEFT(REGEXP_REPLACE(name, '[^a-zA-Z0-9]', '', 'g'), 8))
  INTO barangay_prefix
  FROM barangays
  WHERE id = p_barangay_id;

  IF barangay_prefix IS NULL OR barangay_prefix = '' THEN
    barangay_prefix := 'BRGY';
  END IF;

  SELECT COALESCE(MAX(respondent_sequence), 0) + 1
  INTO next_seq
  FROM kabataan_registrations
  WHERE tenant_id = p_tenant_id
    AND barangay_id = p_barangay_id
    AND EXTRACT(YEAR FROM COALESCE(submitted_at, created_at)) = EXTRACT(YEAR FROM CURRENT_DATE);

  RETURN barangay_prefix || '-' || current_year || '-' || LPAD(next_seq::TEXT, 4, '0');
END;
$$;

-- Kabataan profile image columns (Cloudinary: kabataan/profile-images)
ALTER TABLE public.users ADD COLUMN IF NOT EXISTS profile_image_url TEXT NULL;
ALTER TABLE public.users ADD COLUMN IF NOT EXISTS profile_image_public_id VARCHAR(255) NULL;
ALTER TABLE public.users ADD COLUMN IF NOT EXISTS profile_image_uploaded_at TIMESTAMP NULL;
ALTER TABLE public.users ADD COLUMN IF NOT EXISTS profile_image_change_available_at TIMESTAMP NULL;

-- Verified selfie from KK Profiling registration (storage/app/public/kabataan_photos)
ALTER TABLE public.kabataan_registrations ADD COLUMN IF NOT EXISTS profile_photo_path VARCHAR(500) NULL;
ALTER TABLE public.kabataan_registrations ADD COLUMN IF NOT EXISTS facial_verification_completed_at TIMESTAMP NULL;

-- ============================================================
-- REPORT MANAGEMENT (Added: 2026-06-14)
-- Barangay SK Officials upload PDF program/activity reports for SK Federation review
-- ============================================================

create table if not exists public.report_management (
  id bigserial not null,
  tenant_id bigint not null,
  barangay_id bigint not null,
  user_id bigint not null,
  program_code character varying(10) not null,
  program_name character varying(255) not null,
  activity_name character varying(255) not null,
  file_name character varying(255) not null,
  file_path character varying(500) not null,
  file_mime character varying(100) not null default 'application/pdf'::character varying,
  file_size bigint null,
  status character varying(30) not null default 'pending'::character varying,
  reviewed_by_user_id bigint null,
  reviewed_at timestamp without time zone null,
  created_at timestamp without time zone null,
  updated_at timestamp without time zone null,
  deleted_at timestamp without time zone null,
  constraint report_management_pkey primary key (id),
  constraint report_management_tenant_id_foreign foreign KEY (tenant_id) references tenants (id) on delete CASCADE,
  constraint report_management_barangay_id_foreign foreign KEY (barangay_id) references barangays (id) on delete CASCADE,
  constraint report_management_user_id_foreign foreign KEY (user_id) references users (id) on delete CASCADE,
  constraint report_management_reviewed_by_user_id_foreign foreign KEY (reviewed_by_user_id) references users (id) on delete set null
) TABLESPACE pg_default;

create index IF not exists report_management_barangay_id_status_index on public.report_management using btree (barangay_id, status) TABLESPACE pg_default;
create index IF not exists report_management_program_code_created_at_index on public.report_management using btree (program_code, created_at) TABLESPACE pg_default;

-- ============================================================
-- CALENDAR EVENTS (Added: 2026-06-14)
-- Shared calendar events visible to SK Federation (target_audience = SK Fed)
-- ============================================================

create table if not exists public.calendar_events (
  id bigserial not null,
  barangay_id bigint null,
  user_id bigint null,
  event_date date not null,
  start_time time without time zone not null,
  end_time time without time zone not null,
  title character varying(255) not null,
  description text not null,
  task_type character varying(30) not null,
  status character varying(20) not null default 'Pending'::character varying,
  target_audience character varying(50) not null default 'SK Fed'::character varying,
  created_at timestamp without time zone null,
  updated_at timestamp without time zone null,
  constraint calendar_events_pkey primary key (id),
  constraint calendar_events_barangay_id_foreign foreign KEY (barangay_id) references barangays (id) on delete set null,
  constraint calendar_events_user_id_foreign foreign KEY (user_id) references users (id) on delete set null
) TABLESPACE pg_default;

create index IF not exists calendar_events_event_date_target_audience_index on public.calendar_events using btree (event_date, target_audience) TABLESPACE pg_default;
create index IF not exists calendar_events_barangay_id_event_date_index on public.calendar_events using btree (barangay_id, event_date) TABLESPACE pg_default;

-- ============================================================
-- DEFAULT SK FEDERATION ADMINISTRATOR (portal bootstrap)
-- Replaces former Admin SUPER_ADMIN account.
-- Single default account only: skoneportal@gmail.com / role sk_fed
-- must_change_password = true on first login
-- ============================================================

INSERT INTO public.tenants (
    name,
    code,
    municipality,
    province,
    region,
    is_active,
    created_at,
    updated_at
)
SELECT
    'Santa Cruz Federation',
    'santa-cruz-federation',
    'Santa Cruz',
    'Laguna',
    'IV-A CALABARZON',
    true,
    NOW(),
    NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM public.tenants WHERE code = 'santa-cruz-federation'
);

-- Promote legacy Admin bootstrap account to SK Federation admin
UPDATE public.users
SET
    role = 'sk_fed',
    name = 'SK Federation Administrator',
    status = 'ACTIVE',
    updated_at = NOW()
WHERE email = 'skoneportal@gmail.com'
  AND role IN ('SUPER_ADMIN', 'super_admin', 'admin', 'ADMIN');

INSERT INTO public.users (
    name,
    email,
    password,
    role,
    status,
    must_change_password,
    email_verified_at,
    tenant_id,
    created_at,
    updated_at
)
SELECT
    'SK Federation Administrator',
    'skoneportal@gmail.com',
    '$2y$12$vurYtZcT/tlW4Sz0HyZgeOB9HBTDfllm6epcYO7EF8zvZsgO1o45a',
    'sk_fed',
    'ACTIVE',
    true,
    NOW(),
    t.id,
    NOW(),
    NOW()
FROM public.tenants t
WHERE t.code = 'santa-cruz-federation'
  AND NOT EXISTS (
    SELECT 1 FROM public.users WHERE email = 'skoneportal@gmail.com'
);

UPDATE public.users u
SET tenant_id = t.id, updated_at = NOW()
FROM public.tenants t
WHERE u.email = 'skoneportal@gmail.com'
  AND t.code = 'santa-cruz-federation'
  AND u.tenant_id IS NULL;

INSERT INTO public.official_profiles (
    user_id,
    first_name,
    last_name,
    position,
    municipality,
    province,
    region,
    tenant_id,
    created_at,
    updated_at
)
SELECT
    u.id,
    'SK Federation',
    'Administrator',
    'President',
    'Santa Cruz',
    'Laguna',
    'IV-A CALABARZON',
    u.tenant_id,
    NOW(),
    NOW()
FROM public.users u
WHERE u.email = 'skoneportal@gmail.com'
  AND NOT EXISTS (
    SELECT 1 FROM public.official_profiles op WHERE op.user_id = u.id
);

INSERT INTO public.official_terms (
    official_profile_id,
    term_start,
    term_end,
    status,
    created_at,
    updated_at
)
SELECT
    op.id,
    DATE_TRUNC('year', CURRENT_DATE)::date,
    (DATE_TRUNC('year', CURRENT_DATE) + INTERVAL '3 years' - INTERVAL '1 day')::date,
    'ACTIVE',
    NOW(),
    NOW()
FROM public.official_profiles op
INNER JOIN public.users u ON u.id = op.user_id
WHERE u.email = 'skoneportal@gmail.com'
  AND NOT EXISTS (
    SELECT 1 FROM public.official_terms ot WHERE ot.official_profile_id = op.id
);
