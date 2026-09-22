<?php

namespace App\Http\Resources\Citizen;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class IncidentResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'latitude' => (float) $this->latitude,
            'longitude' => (float) $this->longitude,
            'location_description' => $this->location_description,
            'fire_status' => $this->fire_status,
            'severity_level' => $this->severity_level,
            'detected_at' => $this->detected_at?->toIso8601String(),
        ];
    }
}
