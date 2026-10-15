<?php

namespace Database\Seeders;

use App\Models\Region;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

/** Explicit local-only fixtures for exercising administrative warning coverage. */
class EarlyWarningDevelopmentCitizensSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing']) || ! filter_var(env('SIGMA_EW_SEED_DEMO_CITIZENS', false), FILTER_VALIDATE_BOOL)) {
            throw new RuntimeException('This seeder requires APP_ENV=local/testing and SIGMA_EW_SEED_DEMO_CITIZENS=true.');
        }

        $password = env('SIGMA_EW_DEMO_PASSWORD');
        if (! is_string($password) || strlen($password) < 12) {
            throw new RuntimeException('Set SIGMA_EW_DEMO_PASSWORD to a development-only password of at least 12 characters.');
        }

        $target = Region::query()->where('code', '321201')->where('level', 'district')->firstOrFail();
        $sameRegencyDistrict = Region::query()->where('code', '321202')->where('level', 'district')->firstOrFail();
        if ($target->parent_id !== $sameRegencyDistrict->parent_id) {
            throw new RuntimeException('The configured test districts are not children of the same regency.');
        }
        $otherDistrict = Region::query()->where('level', 'district')->where('parent_id', '!=', $target->parent_id)->orderBy('id')->firstOrFail();
        $otherRegency = Region::query()->findOrFail($otherDistrict->parent_id);
        if ($otherRegency->level !== 'regency' || $otherRegency->parent_id === $target->parent_id) {
            throw new RuntimeException('The cross-regency test district does not have a valid regency parent.');
        }

        $fixtures = [
            ['early-warning-target@sigma.test', 'SIGMA EW Demo — Kecamatan target', $target],
            ['early-warning-neighbor@sigma.test', 'SIGMA EW Demo — Kecamatan tetangga', $sameRegencyDistrict],
            ['early-warning-other-regency@sigma.test', 'SIGMA EW Demo — Kabupaten lain', $otherDistrict],
            ['early-warning-no-region@sigma.test', 'SIGMA EW Demo — Tanpa wilayah', null],
        ];

        foreach ($fixtures as [$email, $name, $region]) {
            $user = User::query()->firstOrCreate(['email' => $email], [
                'name' => $name,
                'password' => Hash::make($password),
                'email_verified_at' => now(),
            ]);

            if ($user->wasRecentlyCreated) {
                $user->assignRole('citizen');
            } elseif (! $user->hasRole('citizen')) {
                throw new RuntimeException("Existing fixture {$email} is not a citizen; no role was changed.");
            }

            // These dedicated .test fixture accounts are the only existing users this seeder changes.
            $user->forceFill(['region_id' => $region?->id])->save();
        }

        $this->command?->info(sprintf(
            'SIGMA EW dev fixtures ready: target district %s (#%d), same-regency district %s (#%d), other-regency district %s (#%d), and one unassigned citizen.',
            $target->name, $target->id, $sameRegencyDistrict->name, $sameRegencyDistrict->id, $otherDistrict->name, $otherDistrict->id,
        ));
    }
}
