<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('ai_models', function (Blueprint $table) {


            $table->id();


            /*
            |--------------------------------------------------------------------------
            | Informasi model
            |--------------------------------------------------------------------------
            */

            $table->string('name');

            $table->string('code',100)
                ->unique();

            $table->string('version',50)
                ->nullable();

            $table->text('description')
                ->nullable();



            /*
            |--------------------------------------------------------------------------
            | Jenis AI
            |--------------------------------------------------------------------------
            */

            $table->enum('type',[
                'classification',
                'prediction',
                'detection',
                'recommendation'
            ]);



            /*
            |--------------------------------------------------------------------------
            | Input & output model
            |--------------------------------------------------------------------------
            */

            $table->enum('input_type',[
                'image',
                'satellite',
                'weather',
                'text',
                'geo'
            ]);


            $table->string('output_type',100)
                ->nullable();



            /*
            |--------------------------------------------------------------------------
            | Teknologi AI
            |--------------------------------------------------------------------------
            */

            $table->string('framework',100)
                ->nullable();


            $table->string('algorithm',100)
                ->nullable();



            /*
            |--------------------------------------------------------------------------
            | File model
            |--------------------------------------------------------------------------
            */

            $table->string('model_file')
                ->nullable();


            $table->decimal('model_size',10,2)
                ->nullable();



            /*
            |--------------------------------------------------------------------------
            | Evaluasi model
            |--------------------------------------------------------------------------
            */

            $table->decimal('accuracy',5,2)
                ->nullable();


            $table->decimal('precision_score',5,2)
                ->nullable();


            $table->decimal('recall_score',5,2)
                ->nullable();


            $table->decimal('f1_score',5,2)
                ->nullable();



            /*
            |--------------------------------------------------------------------------
            | Dataset dan deployment
            |--------------------------------------------------------------------------
            */

            $table->string('training_dataset')
                ->nullable();


            $table->timestamp('trained_at')
                ->nullable();


            $table->timestamp('deployed_at')
                ->nullable();


            $table->string('endpoint_url')
                ->nullable();



            /*
            |--------------------------------------------------------------------------
            | Status model
            |--------------------------------------------------------------------------
            */

            $table->enum('status',[
                'active',
                'inactive',
                'testing',
                'deprecated'
            ])
            ->default('testing');



            /*
            |--------------------------------------------------------------------------
            | User pembuat model
            |--------------------------------------------------------------------------
            */

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();



            $table->timestamps();

        });
    }


    public function down(): void
    {
        Schema::dropIfExists('ai_models');
    }

};
