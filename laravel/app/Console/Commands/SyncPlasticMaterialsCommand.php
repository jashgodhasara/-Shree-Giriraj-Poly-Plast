<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\PlasticPricingService;
use App\Models\Material;

class SyncPlasticMaterialsCommand extends Command
{
    protected $signature = 'materials:sync-api {--force : Force refresh cache from API}';
    protected $description = 'Import and synchronize plastic materials from 3MinAPI live feed into the materials table';

    public function handle(PlasticPricingService $pricingService): int
    {
        $this->info('Fetching plastic materials from 3MinAPI live feed...');
        $data = $pricingService->getPrices($this->option('force'));

        if (empty($data['items'])) {
            $this->error('No material records received from API.');
            return Command::FAILURE;
        }

        $created = 0;
        $existing = 0;

        foreach ($data['items'] as $item) {
            $name = trim($item['material_name']);
            $category = $item['category'] ?? 'Raw Material';
            $unit = $item['unit'] ?? 'Kg';

            // Determine Material Type
            $type = 'Raw Material';
            if (stripos($category, 'Additive') !== false || stripos($name, 'Masterbatch') !== false) {
                $type = 'Additive';
            }

            $material = Material::where('name', $name)->first();

            if (!$material) {
                Material::create([
                    'type'            => $type,
                    'name'            => $name,
                    'unit'            => $unit,
                    'grade_variation' => $category,
                    'stock_quantity'  => 0,
                    'stock_kg'        => 0,
                    'stock_pcs'       => 0,
                ]);
                $created++;
                $this->line("  <info>+ Created:</info> {$name} ({$type})");
            } else {
                $existing++;
                $this->line("  <comment>• Exists:</comment> {$name}");
            }
        }

        $this->info("Successfully synced! Created: {$created}, Existing: {$existing}.");
        return Command::SUCCESS;
    }
}
