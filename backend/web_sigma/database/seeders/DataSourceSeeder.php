<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DataSource;

class DataSourceSeeder extends Seeder
{
    public function run(): void
    {
        DataSource::firstOrCreate(
            [
                'name' => 'Satelit Hotspot',
            ],
            [
                'provider' => 'NASA FIRMS',
                'type' => 'satellite',
                'api_endpoint' => null,
                'credentials_key' => null,
                'status' => 'active',
                'last_sync_at' => null,
            ]
        );

        DataSource::firstOrCreate(
            [
                'name' => 'Data Cuaca',
            ],
            [
                'provider' => 'OpenWeather',
                'type' => 'weather',
                'api_endpoint' => null,
                'credentials_key' => null,
                'status' => 'active',
                'last_sync_at' => null,
            ]
        );

        DataSource::firstOrCreate(
            [
                'name' => 'API Data SIGMA',
            ],
            [
                'provider' => 'SIGMA',
                'type' => 'api',
                'api_endpoint' => null,
                'credentials_key' => null,
                'status' => 'active',
                'last_sync_at' => null,
            ]
        );
    }
}
