<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $table = 'article_type';
    protected $primaryKey = 'type_id';

    //protected $connection = 'sqlite';
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('article_type', function (Blueprint $table) {
            $table->engine('InnoDB');
            $table->charset('utf8');
            $table->collation('utf8_unicode_ci');

            $table->bigIncrements("type_id");
            $table->string("type_name", 145);
            $table->text("type_summary");
            $table->string("type_image", 500)->nullable();
            $table->smallInteger("width")->nullable();
            $table->smallInteger("height")->nullable();
            $table->enum("type_status", [1,2,40])->default(1);
            $table->tinyInteger("rank")->default(20);
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('article_type');
    }
};
