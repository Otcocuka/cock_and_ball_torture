<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Schema::create('tasks', function (Blueprint $table) {
        //     $table->id();
        //     $table->timestamps();
        // });

        Schema::create('habits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->string('title');
            $table->text('description')->nullable();
            $table->integer('current_streak')->default(0);
            $table->integer('max_streak')->default(0);
            $table->timestamps();
        });

        Schema::create('habit_subtasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('habit_id');
            $table->string('title');
            $table->boolean('completed')->default(false);
            $table->timestamps();
        });

        Schema::create('habit_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('habit_id')->constrained()->onDelete('cascade');
            $table->date('date');
            $table->boolean('completed');
            $table->timestamps();
        });

        
        Schema::create('habit_history_subtasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('habit_history_id')->constrained()->onDelete('cascade');
            $table->foreignId('subtask_id')->constrained('habit_subtasks')->onDelete('cascade');
            $table->timestamps();
        });

        // Schema::create('sessions', function (Blueprint $table) {
        //     $table->id();
        //     $table->foreignId('user_id')->constrained()->onDelete('cascade');
        //     $table->foreignId('habit_id')->nullable()->constrained()->onDelete('cascade');
        //     $table->integer('duration');
        //     $table->timestamp('started_at');
        //     $table->timestamp('ended_at')->nullable();
        //     $table->timestamps();
        // });

        Schema::create('achievements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->timestamp('earned_at')->nullable();
            $table->timestamps();
        });

        
        
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
