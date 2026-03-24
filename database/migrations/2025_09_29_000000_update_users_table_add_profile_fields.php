<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateUsersTableAddProfileFields extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone_code')->nullable()->after('name');
            $table->string('phone_number')->nullable()->after('phone_code');
            $table->boolean('is_approve')->default(0)->after('phone_number');
            $table->boolean('is_active')
                ->default(0)
                ->after('is_approve')
                ->comment('0: pending, 1: approve, 2: decline, 3: verify, 4: profile, 5: 3photo upload, 6: location_consent, 7: location');
            $table->date('date_of_birth')->nullable()->after('is_active');
            $table->enum('gender', ['male', 'female', 'other'])->nullable()->after('date_of_birth');
            $table->json('interest_id')->nullable()->after('gender');
            $table->json('images')->nullable()->after('interest_id');
            $table->text('about_me')->nullable()->after('images');
            $table->string('location')->nullable()->after('about_me');
            $table->decimal('lat', 10, 7)->nullable()->after('location');
            $table->decimal('lng', 10, 7)->nullable()->after('lat');

            $table->boolean('location_consent')
                ->default(0)
                ->after('lng')
                ->comment('Please confirm that you share your location when using this app. The location will only be shown with an accuracy of 1 km.');
            $table->timestamp('expired_at')->nullable()->after('location_consent');
                        $table->string('otp', 10)->nullable()->after('expired_at');

        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'phone_code',
                'phone_number',
                'is_approve',
                'is_active',
                'date_of_birth',
                'gender',
                'interest_id',
                'about_me',
                'location',
                'lat',
                'images',
                'lng',
                'location_consent',
                'expired_at',
                'otp'
            ]);
        });
    }
}
