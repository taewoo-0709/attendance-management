<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttendanceEditsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attendance_edits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attendance_id')->constrained('attendances')->onDelete('cascade');
            $table->foreignId('requested_id')->constrained('users')->onDelete('cascade');
            $table->dateTime('after_check_in')->nullable();
            $table->dateTime('after_check_out')->nullable();
            $table->text('reason');
            $table->unsignedTinyInteger('status')
                ->default(0);
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
        Schema::dropIfExists('attendance_edit_breaks');
        Schema::dropIfExists('attendance_edits');
    }
}