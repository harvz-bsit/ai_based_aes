<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('applications', function (Blueprint $table) {
            // Drop old fields
            $table->dropColumn(['resume', 'higher_education', 'major', 'otr']);

            // Add new fields
            $table->string('education')->after('job_id');
            $table->string('training')->nullable()->after('education');
            $table->string('eligibility')->nullable()->after('training');
            $table->text('work_experience')->nullable()->after('eligibility');

            $table->string('otr_diploma')->after('pds');
            $table->string('certificate_eligibility')->nullable()->after('otr_diploma');
            $table->json('certificates_training')->nullable()->after('certificate_eligibility');
        });
    }

    public function down()
    {
        Schema::table('applications', function (Blueprint $table) {
            // Rollback changes
            $table->string('resume')->after('application_letter');
            $table->string('higher_education')->after('job_id');
            $table->string('major')->after('higher_education');
            $table->string('otr')->after('pds');

            $table->dropColumn(['education', 'training', 'eligibility', 'work_experience', 'otr_diploma', 'certificate_eligibility', 'certificates_training']);
        });
    }
};
