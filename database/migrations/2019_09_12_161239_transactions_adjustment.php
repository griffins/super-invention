<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TransactionsAdjustment extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('transactions',function(Blueprint $table){
           $table->dropColumn('size') ;
           $table->dropColumn('opened_at') ;
           $table->dropColumn('closed_at') ;
           $table->dropColumn('profit') ;
            $table->decimal("amount", 15, 8)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('transactions',function(Blueprint $table){
            $table->decimal("size", 14, 2)->default(0);
            $table->timestamp("opened_at")->index()->nullable();
            $table->timestamp("closed_at")->index()->nullable();
            $table->decimal('profit', 14, 2)->default(0);
        });
    }
}
