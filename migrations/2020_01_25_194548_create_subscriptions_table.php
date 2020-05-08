<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSubscriptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();
            $table->string('slug');
            $table->string('name');
            $table->float('price');
            $table->string('currency');
            $table->string('period');
            $table->boolean('prolongable');
            $table->json('features')->nullable();
            $table->json('texts')->nullable();
            $table->bigInteger('user_id');
            $table->bigInteger('tariff_id');
            $table->bigInteger('remote_id')->nullable();
            $table->string('status');
            $table->datetime('last_transaction_date')->nullable();
            $table->datetime('next_transaction_date')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('subscriptions');
    }
}
