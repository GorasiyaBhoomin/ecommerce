<?php

namespace Database\Seeders;

use App\Models\Language;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class LanguageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $jsonPath = database_path('data/languages.json');

        if (! File::exists($jsonPath)) {
            $this->command?->warn("Language JSON catalog not found at {$jsonPath}");

            return;
        }

        $catalog = json_decode(File::get($jsonPath), true);

        if (! is_array($catalog)) {
            $this->command?->error("Invalid JSON in {$jsonPath}");

            return;
        }

        // Clean up legacy non-standard 'guj' code if it exists and migrate to 'gu'
        $legacyGuj = Language::where('code', 'guj')->first();
        if ($legacyGuj) {
            $existingGu = Language::where('code', 'gu')->first();
            if ($existingGu) {
                $legacyGuj->delete();
            } else {
                $legacyGuj->update([
                    'code' => 'gu',
                    'name' => 'Gujarati',
                    'native_name' => 'ગુજરાતી',
                    'is_active' => true,
                ]);
            }
        }

        $activeCodes = ['en', 'gu', 'hi'];

        foreach ($catalog as $item) {
            $code = strtolower(trim($item['code'] ?? ''));
            $name = trim($item['name'] ?? '');
            $nativeName = trim($item['native_name'] ?? $name);

            if ($code === '' || $name === '') {
                continue;
            }

            $isDefault = ($code === 'en');
            $isActive = in_array($code, $activeCodes, true) || $isDefault;

            // Don't overwrite existing active status if record already exists
            $existing = Language::where('code', $code)->first();

            Language::updateOrCreate(
                ['code' => $code],
                [
                    'name' => $name,
                    'native_name' => $nativeName,
                    'is_default' => $existing ? $existing->is_default : $isDefault,
                    'is_active' => $existing ? $existing->is_active : $isActive,
                ]
            );
        }

        // Ensure at least English is default if none is set
        if (! Language::where('is_default', true)->exists()) {
            Language::where('code', 'en')->update(['is_default' => true, 'is_active' => true]);
        }
    }
}
