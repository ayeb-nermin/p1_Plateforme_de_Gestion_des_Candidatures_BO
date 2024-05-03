<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('emails', function (Blueprint $table) {
            $table->id();
            $table->text('name')->comment('The email template name.');
            $table->text('description')->nullable()->comment('The email template description.');
            $table->longText('template')->nullable()->comment('The email body.');
            $table->text('headers')->nullable()->comment('The email headers. EX: subject, cc, bcc, ...');
            $table->string('type')->nullable();
            // $table->text('help')->nullable()->comment('A help text for the email template variables.');
            // $table->text('is_deletable')->nullable()->default(true)->comment('Whether the email template can be deleted.');
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
        Schema::dropIfExists('emails');
    }
}
