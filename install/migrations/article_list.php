<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $table = 'article_list';
    protected $primaryKey = 'article_id';

    //protected $connection = 'sqlite';
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        //
        Schema::create('article_list', function (Blueprint $table) {
            $table->engine('InnoDB');
            $table->charset('utf8');
            
            $table->id("article_id");
            $table->string("title");
            $table->bigInteger("typeid");
            $table->mediumText("summary");
            $table->longText("details");
            $table->string("photo")->nullable(true);
            $table->tinyText("caption")->nullable(true);
            $table->string("link", 255)->nullable(true);
            $table->string("author", 100)->nullable(true);
            $table->smallInteger("rank")->default('20');
            $table->dateTime("pub_date")->nullable(false);
            $table->dateTime("unpub_date", 20)->nullable(true);
            $table->tinyInteger("status")->nullable(false)->default('1');
            $table->dateTime("input_date", 0);

            $table->foreignId('typeid')->constrained('article_type', 'type_id')->onUpdate('cascade')->noActionOnDelete();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('article_list');
    }
};
