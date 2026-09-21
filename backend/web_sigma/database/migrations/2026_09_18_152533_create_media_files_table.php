<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('media_files', function (Blueprint $table) {


            $table->id();


            /*
            |--------------------------------------------------------------------------
            | Polymorphic relation
            |--------------------------------------------------------------------------
            */

            $table->string('model_type',100);

            $table->unsignedBigInteger('model_id');


            $table->index([
                'model_type',
                'model_id'
            ]);



            /*
            |--------------------------------------------------------------------------
            | Informasi file
            |--------------------------------------------------------------------------
            */

            $table->enum('file_type',[
                'image',
                'video',
                'audio',
                'document'
            ]);


            $table->string('category',100)
                ->nullable();


            $table->string('file_name');


            $table->string('file_path');



            /*
            |--------------------------------------------------------------------------
            | Metadata file
            |--------------------------------------------------------------------------
            */

            $table->string('file_extension',20)
                ->nullable();


            $table->unsignedBigInteger('file_size')
                ->nullable();


            $table->string('mime_type',100)
                ->nullable();


            $table->string('thumbnail_path')
                ->nullable();



            /*
            |--------------------------------------------------------------------------
            | User upload
            |--------------------------------------------------------------------------
            */

            $table->foreignId('uploaded_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();



            /*
            |--------------------------------------------------------------------------
            | Sumber upload
            |--------------------------------------------------------------------------
            */

            $table->enum('upload_source',[
                'mobile',
                'web',
                'system'
            ])
            ->default('web');



            /*
            |--------------------------------------------------------------------------
            | Status file
            |--------------------------------------------------------------------------
            */

            $table->enum('status',[
                'active',
                'deleted'
            ])
            ->default('active');



            $table->timestamps();

        });
    }


    public function down(): void
    {
        Schema::dropIfExists('media_files');
    }

};
