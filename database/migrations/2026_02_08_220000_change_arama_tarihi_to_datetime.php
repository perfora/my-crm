<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE ziyaretler MODIFY arama_tarihi DATETIME NULL');
            return;
        }

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE ziyaretler ALTER COLUMN arama_tarihi TYPE TIMESTAMP WITHOUT TIME ZONE');
            return;
        }
    }

    public function down(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE ziyaretler MODIFY arama_tarihi DATE NULL');
            return;
        }

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE ziyaretler ALTER COLUMN arama_tarihi TYPE DATE USING arama_tarihi::date');
            return;
        }
    }
};
