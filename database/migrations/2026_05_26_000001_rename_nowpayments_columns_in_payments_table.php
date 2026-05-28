<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Use SHOW COLUMNS + raw ALTER to avoid Laravel's column introspection
        // which fails on older MySQL/MariaDB (no generation_expression in information_schema)
        if (!empty(DB::select("SHOW COLUMNS FROM payments LIKE 'nowpayments_invoice_id'"))) {
            DB::statement('ALTER TABLE payments CHANGE COLUMN `nowpayments_invoice_id` `provider_invoice_id` VARCHAR(255) NULL');
        }
        if (!empty(DB::select("SHOW COLUMNS FROM payments LIKE 'nowpayments_payment_id'"))) {
            DB::statement('ALTER TABLE payments CHANGE COLUMN `nowpayments_payment_id` `provider_payment_id` VARCHAR(255) NULL');
        }
    }

    public function down(): void
    {
        if (!empty(DB::select("SHOW COLUMNS FROM payments LIKE 'provider_invoice_id'"))) {
            DB::statement('ALTER TABLE payments CHANGE COLUMN `provider_invoice_id` `nowpayments_invoice_id` VARCHAR(255) NULL');
        }
        if (!empty(DB::select("SHOW COLUMNS FROM payments LIKE 'provider_payment_id'"))) {
            DB::statement('ALTER TABLE payments CHANGE COLUMN `provider_payment_id` `nowpayments_payment_id` VARCHAR(255) NULL');
        }
    }
};
