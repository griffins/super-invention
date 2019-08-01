<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('client_id')->index();
            $table->string('ticket')->index();
            $table->string("type");
            $table->string("item");
            $table->decimal("size", 14, 2)->default(0);
            $table->timestamp("opened_at")->index()->nullable();
            $table->timestamp("closed_at")->index()->nullable();
            $table->decimal('profit', 14, 2)->default(0);
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
        Schema::dropIfExists('transactions');
    }
}
