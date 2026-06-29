<?php

use App\Services\PreviousKabataanProfileMapper;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('previous_kabataan', function (Blueprint $table) {
            $table->string('age', 10)->nullable()->after('suffix');
            $table->string('birthday', 30)->nullable()->after('age');
            $table->string('sex', 20)->nullable()->after('birthday');
            $table->string('civil_status', 50)->nullable()->after('sex');
            $table->string('youth_classification', 100)->nullable()->after('civil_status');
            $table->string('youth_age_group', 100)->nullable()->after('youth_classification');
            $table->string('home_address', 255)->nullable()->after('contact_number');
            $table->string('education', 100)->nullable()->after('home_address');
            $table->string('work_status', 100)->nullable()->after('education');
            $table->string('registered_voter', 20)->nullable()->after('work_status');
            $table->string('voted_last_election', 20)->nullable()->after('registered_voter');
            $table->string('kk_assembly', 20)->nullable()->after('voted_last_election');
            $table->string('kk_assembly_count', 50)->nullable()->after('kk_assembly');
            $table->string('barangay_name', 150)->nullable()->after('kk_assembly_count');
            $table->string('region', 100)->nullable()->after('barangay_name');
            $table->string('province', 100)->nullable()->after('region');
            $table->string('city', 100)->nullable()->after('province');
        });

        if (! Schema::hasTable('previous_kabataan')) {
            return;
        }

        DB::table('previous_kabataan')
            ->orderBy('id')
            ->chunkById(100, function ($rows) {
                foreach ($rows as $row) {
                    $formData = json_decode($row->form_data ?? '[]', true);

                    if (! is_array($formData)) {
                        $formData = [];
                    }

                    $profile = PreviousKabataanProfileMapper::extractProfileColumns($formData);

                    DB::table('previous_kabataan')
                        ->where('id', $row->id)
                        ->update(array_merge($profile, [
                            'barangay_name' => $profile['barangay_name']
                                ?? DB::table('barangays')->where('id', $row->barangay_id)->value('name'),
                        ]));
                }
            });
    }

    public function down(): void
    {
        Schema::table('previous_kabataan', function (Blueprint $table) {
            $table->dropColumn([
                'age',
                'birthday',
                'sex',
                'civil_status',
                'youth_classification',
                'youth_age_group',
                'home_address',
                'education',
                'work_status',
                'registered_voter',
                'voted_last_election',
                'kk_assembly',
                'kk_assembly_count',
                'barangay_name',
                'region',
                'province',
                'city',
            ]);
        });
    }
};
