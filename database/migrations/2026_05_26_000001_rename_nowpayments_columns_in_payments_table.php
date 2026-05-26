<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->renameColumn('nowpayments_invoice_id', 'provider_invoice_id');
            $table->renameColumn('nowpayments_payment_id', 'provider_payment_id');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->renameColumn('provider_invoice_id', 'nowpayments_invoice_id');
            $table->renameColumn('provider_payment_id', 'nowpayments_payment_id');
        });
    }
};
