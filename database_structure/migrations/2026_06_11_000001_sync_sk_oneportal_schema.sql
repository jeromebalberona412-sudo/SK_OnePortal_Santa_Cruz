-- SK OnePortal schema sync patch
-- Date: 2026-06-11

ALTER TABLE public.users DROP COLUMN IF EXISTS otp_code;
ALTER TABLE public.users DROP COLUMN IF EXISTS otp_expires_at;
ALTER TABLE public.users DROP COLUMN IF EXISTS otp_attempts;
ALTER TABLE public.users DROP COLUMN IF EXISTS otp_last_sent_at;

ALTER TABLE public.sk_fed_trusted_devices
    ADD COLUMN IF NOT EXISTS device_token_hash character varying(64) NULL;

CREATE INDEX IF NOT EXISTS sk_fed_trusted_device_token_idx
    ON public.sk_fed_trusted_devices USING btree (user_id, device_token_hash);

ALTER TABLE public.barangay_logos
    ADD COLUMN IF NOT EXISTS cloudinary_version character varying(32) NULL;

ALTER TABLE public.announcements
    ADD COLUMN IF NOT EXISTS is_archived boolean NOT NULL DEFAULT false;

ALTER TABLE public.announcements
    ADD COLUMN IF NOT EXISTS archived_at timestamp without time zone NULL;

ALTER TABLE public.announcements
    ADD COLUMN IF NOT EXISTS deleted_at timestamp without time zone NULL;

CREATE INDEX IF NOT EXISTS announcements_barangay_archive_idx
    ON public.announcements USING btree (barangay_id, is_archived, archived_at);

CREATE TABLE IF NOT EXISTS public.announcement_images (
    id bigserial NOT NULL,
    announcement_id bigint NOT NULL,
    image_url text NOT NULL,
    public_id character varying(255) NULL,
    sort_order smallint NOT NULL DEFAULT 0,
    created_at timestamp without time zone NULL,
    CONSTRAINT announcement_images_pkey PRIMARY KEY (id),
    CONSTRAINT announcement_images_announcement_id_foreign
        FOREIGN KEY (announcement_id) REFERENCES announcements (id) ON DELETE CASCADE
);

CREATE INDEX IF NOT EXISTS announcement_images_announcement_id_sort_order_index
    ON public.announcement_images USING btree (announcement_id, sort_order);

DO $$
BEGIN
    IF EXISTS (
        SELECT 1
        FROM information_schema.columns
        WHERE table_schema = 'public'
          AND table_name = 'announcements'
          AND column_name = 'image_url'
    ) THEN
        INSERT INTO public.announcement_images (announcement_id, image_url, public_id, sort_order, created_at)
        SELECT a.id, a.image_url, NULL, 0, COALESCE(a.updated_at, a.created_at, NOW())
        FROM public.announcements a
        WHERE a.image_url IS NOT NULL
          AND btrim(a.image_url) <> ''
          AND NOT EXISTS (
              SELECT 1
              FROM public.announcement_images ai
              WHERE ai.announcement_id = a.id
          );

        ALTER TABLE public.announcements DROP COLUMN IF EXISTS image_url;
    END IF;
END $$;

DROP TABLE IF EXISTS public.sk_officials_post_images;

CREATE TABLE IF NOT EXISTS public.jobs (
    id bigserial NOT NULL,
    queue character varying(255) NOT NULL,
    payload text NOT NULL,
    attempts smallint NOT NULL,
    reserved_at integer NULL,
    available_at integer NOT NULL,
    created_at integer NOT NULL,
    CONSTRAINT jobs_pkey PRIMARY KEY (id)
);

CREATE INDEX IF NOT EXISTS jobs_queue_index ON public.jobs USING btree (queue);

CREATE TABLE IF NOT EXISTS public.job_batches (
    id character varying(255) NOT NULL,
    name character varying(255) NOT NULL,
    total_jobs integer NOT NULL,
    pending_jobs integer NOT NULL,
    failed_jobs integer NOT NULL,
    failed_job_ids text NOT NULL,
    options text NULL,
    cancelled_at integer NULL,
    created_at integer NOT NULL,
    finished_at integer NULL,
    CONSTRAINT job_batches_pkey PRIMARY KEY (id)
);

CREATE TABLE IF NOT EXISTS public.failed_jobs (
    id bigserial NOT NULL,
    uuid character varying(255) NOT NULL,
    connection text NOT NULL,
    queue text NOT NULL,
    payload text NOT NULL,
    exception text NOT NULL,
    failed_at timestamp without time zone NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT failed_jobs_pkey PRIMARY KEY (id),
    CONSTRAINT failed_jobs_uuid_unique UNIQUE (uuid)
);
