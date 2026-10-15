<?php

namespace App\Http\Resources\Citizen;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProfileResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'email_verified' => $this->hasVerifiedEmail(),
            'region_id' => $this->region_id,
            'region' => $this->region
                ? collect([$this->region->name, $this->region->parent?->name, $this->region->parent?->parent?->name])->filter()->implode(', ')
                : null,
            'region_hierarchy' => $this->region ? [
                'level' => $this->region->level,
                'district_id' => $this->region->level === 'district' ? $this->region->id : null,
                'district' => $this->region->level === 'district' ? $this->region->name : null,
                'regency_id' => $this->region->level === 'district'
                    ? $this->region->parent?->id
                    : ($this->region->level === 'regency' ? $this->region->id : null),
                'regency' => $this->region->level === 'district'
                    ? $this->region->parent?->name
                    : ($this->region->level === 'regency' ? $this->region->name : null),
                'province_id' => match ($this->region->level) {
                    'province' => $this->region->id,
                    'regency' => $this->region->parent?->id,
                    default => $this->region->parent?->parent?->id,
                },
                'province' => match ($this->region->level) {
                    'province' => $this->region->name,
                    'regency' => $this->region->parent?->name,
                    default => $this->region->parent?->parent?->name,
                },
            ] : null,
            'organization' => null,
        ];
    }
}
