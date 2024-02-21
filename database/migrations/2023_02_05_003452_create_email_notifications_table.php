<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('email_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained('companies')->onDelete('cascade');
            $table->foreignId('sender_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->foreignId('recipient_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->unsignedBigInteger('referenceable_id')->nullable();
            $table->string('referenceable_type')->nullable();
            $table->string('recipient_email');
            $table->string('mailable')->nullable();
            $table->timestamps();

            $table->index('company_id');
            $table->index('recipient_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('email_notifications');
    }
};
