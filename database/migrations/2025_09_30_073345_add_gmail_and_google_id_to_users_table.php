<?php



use Illuminate\Database\Migrations\Migration;

use Illuminate\Database\Schema\Blueprint;

use Illuminate\Support\Facades\Schema;



class AddGmailAndGoogleIdToUsersTable extends Migration

{

    public function up()

    {

        Schema::table('users', function (Blueprint $table) {

            $table->string('gmail_id')->nullable()->after('otp');

            $table->string('google_id')->nullable()->after('gmail_id');
            $table->boolean('online_status')->default(false)->after('google_id');
            $table->timestamp('last_seen_at')->nullable()->after('online_status');

        });

    }



    public function down()

    {

        Schema::table('users', function (Blueprint $table) {

            $table->dropColumn(['gmail_id', 'google_id', 'online_status']);

        });

    }

}

