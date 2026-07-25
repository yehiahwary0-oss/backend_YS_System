<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Row-Level Security on audit_logs.
 *
 * This is defense layer #2 — layer #1 is the Eloquent model override.
 * Even if someone bypasses Eloquent (raw Query Builder, Tinker, etc.),
 * PostgreSQL RLS blocks UPDATE and DELETE at the DB engine level.
 *
 * The app DB user gets INSERT + SELECT only on audit_logs.
 * No UPDATE. No DELETE. Ever.
 */
return new class extends Migration
{
    public function up(): void
    {
        $appUser = config('database.connections.pgsql.username', 'postgres');

        // Enable RLS on the table
        DB::statement('ALTER TABLE audit_logs ENABLE ROW LEVEL SECURITY');

        // Force RLS even for the table owner (superuser bypass is intentional —
        // only the actual PostgreSQL superuser can bypass, not the app user)
        DB::statement('ALTER TABLE audit_logs FORCE ROW LEVEL SECURITY');

        // Policy: INSERT is allowed
        DB::statement("
            CREATE POLICY audit_insert_only ON audit_logs
            FOR INSERT TO \"{$appUser}\"
            WITH CHECK (true)
        ");

        // Policy: SELECT is allowed
        DB::statement("
            CREATE POLICY audit_select_all ON audit_logs
            FOR SELECT TO \"{$appUser}\"
            USING (true)
        ");

        // No UPDATE policy → UPDATE is implicitly denied
        // No DELETE policy → DELETE is implicitly denied

        // Also revoke explicit privileges to be safe
        DB::statement("REVOKE UPDATE, DELETE ON audit_logs FROM \"{$appUser}\"");
    }

    public function down(): void
    {
        $appUser = config('database.connections.pgsql.username', 'postgres');

        DB::statement('DROP POLICY IF EXISTS audit_insert_only ON audit_logs');
        DB::statement('DROP POLICY IF EXISTS audit_select_all ON audit_logs');
        DB::statement('ALTER TABLE audit_logs DISABLE ROW LEVEL SECURITY');
        DB::statement("GRANT UPDATE, DELETE ON audit_logs TO \"{$appUser}\"");
    }
};
