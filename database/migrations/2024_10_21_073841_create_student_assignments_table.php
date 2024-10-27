<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStudentAssignmentsTable extends Migration
{
    public function up()
    {
        Schema::create('student_assignments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('faculty_id');
            $table->date('assigned_date');
            $table->string('hk_type');
            $table->enum('hk_duty_type', ['Internal Facilitator', 'External Facilitator']);
            $table->timestamps();

            $table->foreign('student_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('faculty_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('student_assignments');
    }
}
