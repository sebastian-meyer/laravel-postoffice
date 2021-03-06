<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (! Schema::hasTable('accounts')) {
            // There is no mail server setup present. We'll create all necessary tables.
            Schema::create('accounts', function (Blueprint $table) {
                $table->integer('id')->unsigned()->autoIncrement();
                $table->string('username', 64);
                $table->string('domain', 190);
                $table->string('email', 255)->virtualAs('CONCAT(`username`, "@", `domain`)')->nullable();
                $table->string('password', 60);
                $table->integer('quota')->unsigned()->default(0);
                $table->boolean('enabled')->default(false);
                $table->boolean('sendonly')->default(false);
                $table->enum('admin', ['global', 'domain'])->nullable();
                $table->rememberToken();
                $table->timestamps();
                $table->unique(['username', 'domain']);
                $table->foreign('domain')->references('domain')->on('domains');
                $table->engine = 'InnoDB';
            });
        } else {
            if (Schema::hasColumn('accounts', 'username')
                && Schema::hasColumn('accounts', 'domain')) {
                    // There is an existing mail server setup, so just add some columns and indexes.
                    Schema::table('accounts', function(Blueprint $table) {
                        $table->string('email', 255)->virtualAs('CONCAT(`username`, "@", `domain`)')->nullable();
                        $table->enum('admin', ['global', 'domain'])->nullable();
                        $table->rememberToken();
                        $table->timestamps();
                    });
            } else {
                // The database schema is not compatible with Post Office.
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Just drop the Post Office specific columns and indexes, but keep the basic mail server setup.
        Schema::table('accounts', function (Blueprint $table) {
            $table->dropColumn(['email', 'admin']);
            $table->dropRememberToken();
            $table->dropTimestamps();
        });
    }
}
