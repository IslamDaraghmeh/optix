<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('exams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->onDelete('cascade');
            $table->date('exam_date');
            $table->decimal('right_eye_sphere', 5, 2)->nullable();
            $table->decimal('right_eye_cylinder', 5, 2)->nullable();
            $table->integer('right_eye_axis')->nullable();
            $table->decimal('left_eye_sphere', 5, 2)->nullable();
            $table->decimal('left_eye_cylinder', 5, 2)->nullable();
            $table->integer('left_eye_axis')->nullable();
            $table->text('notes')->nullable();
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
        Schema::dropIfExists('exams');
    }
};
