-- Indexes to speed up the Clients page (/clients) and related lookups.
-- Safe to run more than once (IF NOT EXISTS, MariaDB). Adds indexes only; no data changes.
-- Run in phpMyAdmin on the app database (local: u665086166_pwc, then live).

-- clients: parent list filter + branch (child) lookups by parent_id
CREATE INDEX IF NOT EXISTS idx_clients_is_child_parent ON clients (is_child, parent_id);
CREATE INDEX IF NOT EXISTS idx_clients_parent_id      ON clients (parent_id);
CREATE INDEX IF NOT EXISTS idx_clients_staff_id       ON clients (staff_id);
CREATE INDEX IF NOT EXISTS idx_clients_user_id        ON clients (user_id);

-- profiles: client profile (city) lookup
CREATE INDEX IF NOT EXISTS idx_profiles_client_id ON profiles (client_id);

-- client_schedules: MAX(updated_at) per client, answered from the index alone
CREATE INDEX IF NOT EXISTS idx_cs_client_updated ON client_schedules (client_id, updated_at);

-- client_routes: route join (client_id already indexed)
CREATE INDEX IF NOT EXISTS idx_cr_route ON client_routes (route_id);
