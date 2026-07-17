<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSuratSakitToAbsensi extends Migration
{
    public function up()
    {
        Schema::table('absensi', function (Blueprint $table) {
            $table->string('surat_sakit_path')->nullable();
            $table->string('surat_sakit_original_name')->nullable();
            $table->string('surat_sakit_mime_type')->nullable();
            $table->bigInteger('surat_sakit_size')->nullable();
            $table->timestamp('surat_sakit_uploaded_at')->nullable();
        });
    }

    public function down()
    {
        Schema::table('absensi', function (Blueprint $table) {
            $table->dropColumn([
                'surat_sakit_path',
                'surat_sakit_original_name',
                'surat_sakit_mime_type',
                'surat_sakit_size',
                'surat_sakit_uploaded_at'
            ]);
        });
    }
}