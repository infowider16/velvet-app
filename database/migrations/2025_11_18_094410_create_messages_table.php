<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('sender_id');
            $table->unsignedBigInteger('receiver_id')->nullable(); // null for group messages   

            // Text message
            $table->text('message_text')->nullable();

            // Media message
            $table->string('media_url')->nullable();          // image/video/audio/document path
            $table->string('media_type')->nullable();         // image, video, audio, file, voice_note
            $table->string('link_url')->nullable();            // for link previews
            $table->string('document_url')->nullable();        // for document attachments
            // Additional fields (optional but useful)
            $table->string('thumbnail')->nullable();           // video thumbnail
            $table->integer('duration')->nullable();           // audio/video length in seconds
            $table->integer('file_size')->nullable();          // file size in KB
            $table->unsignedBigInteger('group_id')->nullable();
            $table->boolean('notification_status')->default(true); // true = on, false = off
            $table->string('status')->default('sent');         // sent, delivered, seen
            $table->timestamp('read_at')->nullable();          // seen time

            $table->timestamps();

            $table->foreign('sender_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('receiver_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('group_id')->references('id')->on('groups')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('messages');
    }
};
