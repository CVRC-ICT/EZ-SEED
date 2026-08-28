<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Categorized reason so it can be charted (the free-text `reason`
        // column stays for narrative detail; this adds a fixed bucket).
        Schema::table('seed_preferences', function (Blueprint $table) {
            if (! Schema::hasColumn('seed_preferences', 'reason_category')) {
                $table->enum('reason_category', [
                    'High Yield',
                    'Pest Resistance',
                    'Adaptability',
                    'Grain Quality',
                    'Early Maturing',
                    'Others',
                ])->nullable()->after('reason');
            }
        });

        // Per-user preferences for the Settings page
        if (! Schema::hasTable('user_settings')) {
            Schema::create('user_settings', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();

                $table->boolean('email_alerts')->default(true);
                $table->boolean('push_notifications')->default(true);
                $table->boolean('portal_submission_alerts')->default(true);
                $table->boolean('sync_error_alerts')->default(false);

                $table->boolean('auto_sync_on_reconnect')->default(true);
                $table->integer('sync_interval_minutes')->default(5);
                $table->string('portal_api_endpoint')->nullable();

                $table->enum('default_export_format', ['PDF', 'EXCEL', 'CSV'])
                      ->default('CSV');

                $table->timestamps();
            });
        }

        // Where the farmer record originated — shown as a badge on the
        // Farmer Profiles page ("From Portal" vs "DA Staff")
        Schema::table('farmers', function (Blueprint $table) {
            if (! Schema::hasColumn('farmers', 'source')) {
                $table->enum('source', ['portal', 'staff'])
                      ->default('staff')
                      ->after('id');
            }
        });

        // Log of generated exports, powers the "Download History" list
        if (! Schema::hasTable('export_logs')) {
            Schema::create('export_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->string('file_name');
                $table->string('format', 10);
                $table->unsignedInteger('size_kb')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::table('seed_preferences', function (Blueprint $table) {
            if (Schema::hasColumn('seed_preferences', 'reason_category')) {
                $table->dropColumn('reason_category');
            }
        });

        Schema::dropIfExists('export_logs');
        Schema::dropIfExists('user_settings');

        Schema::table('farmers', function (Blueprint $table) {
            if (Schema::hasColumn('farmers', 'source')) {
                $table->dropColumn('source');
            }
        });
    }
};
