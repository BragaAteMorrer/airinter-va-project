<?php

use Illuminate\Database\Migrations\Migration;
use Modules\Promethee\Services\BbrReferenceRepairService;

return new class extends Migration {
    public function up(): void
    {
        app(BbrReferenceRepairService::class)->repairBlankBulkReferences();
    }

    public function down(): void
    {
        // Data repair is intentionally not reversed.
    }
};
