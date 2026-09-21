<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MediaFile;
use App\Models\Incident;
use App\Models\User;

class MediaFileSeeder extends Seeder
{
    public function run(): void
    {
        $incidents = Incident::orderBy('id')->get();

        $admin = User::where('email', 'superadmin@sigma.id')->first();

        foreach ($incidents as $incident) {
            MediaFile::firstOrCreate(
                [
                    'model_type' => Incident::class,
                    'model_id' => $incident->id,
                    'file_name' => 'incident_' . $incident->id . '.jpg',
                ],
                [
                    'file_type' => 'image',
                    'category' => 'incident_documentation',
                    'file_path' => 'media/incidents/incident_' . $incident->id . '.jpg',
                    'file_extension' => 'jpg',
                    'file_size' => 250000,
                    'mime_type' => 'image/jpeg',
                    'thumbnail_path' => null,
                    'uploaded_by' => $admin?->id,
                    'upload_source' => 'system',
                    'status' => 'active',
                ]
            );
        }
    }
}
