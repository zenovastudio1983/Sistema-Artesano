-- PostgreSQL 17 initialization script for Artisan ERP

-- Extensions
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";
CREATE EXTENSION IF NOT EXISTS "pg_trgm";
CREATE EXTENSION IF NOT EXISTS "unaccent";
CREATE EXTENSION IF NOT EXISTS "btree_gin";
CREATE EXTENSION IF NOT EXISTS "pg_stat_statements";

-- Create Spanish full-text search configuration with unaccent support
-- CREATE TEXT SEARCH CONFIGURATION does not support IF NOT EXISTS, so we check manually
DO $$
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM pg_ts_config WHERE cfgname = 'spanish_unaccent'
    ) THEN
        EXECUTE 'CREATE TEXT SEARCH CONFIGURATION spanish_unaccent (COPY = spanish)';
    END IF;
END
$$;

ALTER TEXT SEARCH CONFIGURATION spanish_unaccent
    ALTER MAPPING FOR hword, hword_part, word
    WITH unaccent, spanish_stem;

-- Grant privileges
GRANT ALL PRIVILEGES ON DATABASE artisan_erp TO artisan_user;
GRANT ALL ON SCHEMA public TO artisan_user;

-- Performance settings
ALTER SYSTEM SET work_mem = '16MB';
ALTER SYSTEM SET maintenance_work_mem = '256MB';
ALTER SYSTEM SET effective_cache_size = '1GB';
ALTER SYSTEM SET random_page_cost = 1.1;
ALTER SYSTEM SET effective_io_concurrency = 200;

SELECT pg_reload_conf();
