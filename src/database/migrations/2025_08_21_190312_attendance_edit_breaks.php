<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AttendanceEditBreaks extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attendance_edit_breaks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attendance_edit_id')->constrained('attendance_edits')->onDelete('cascade');
            $table->dateTime('after_break_start_time')->nullable();
            $table->dateTime('after_break_end_time')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
