<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\StockLedger;
use App\Models\Unit;
use App\Models\UnitConversion;
use App\Models\Warehouse;
use App\Services\InventoryService;
use Illuminate\Database\Seeder;

class InventoryMasterSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Standard Units
        $unitsData = [
            ['name' => 'Piece', 'code' => 'PCS', 'symbol' => 'pcs'],
            ['name' => 'Kilogram', 'code' => 'KG', 'symbol' => 'kg'],
            ['name' => 'Gram', 'code' => 'G', 'symbol' => 'g'],
            ['name' => 'Metric Ton', 'code' => 'TON', 'symbol' => 'ton'],
            ['name' => 'Box', 'code' => 'BOX', 'symbol' => 'box'],
            ['name' => 'Bag', 'code' => 'BAG', 'symbol' => 'bag'],
            ['name' => 'Meter', 'code' => 'MTR', 'symbol' => 'm'],
            ['name' => 'Liter', 'code' => 'LTR', 'symbol' => 'l'],
            ['name' => 'Roll', 'code' => 'ROLL', 'symbol' => 'roll'],
            ['name' => 'Dozen', 'code' => 'DOZ', 'symbol' => 'doz'],
        ];

        $unitMap = [];
        foreach ($unitsData as $u) {
            $unit = Unit::firstOrCreate(['code' => $u['code']], $u);
            $unitMap[$u['code']] = $unit->id;
        }

        // 2. Standard Unit Conversions
        if (isset($unitMap['KG']) && isset($unitMap['G'])) {
            UnitConversion::firstOrCreate(
                ['from_unit_id' => $unitMap['KG'], 'to_unit_id' => $unitMap['G']],
                ['conversion_factor' => 1000.000000, 'operator' => '*']
            );
            UnitConversion::firstOrCreate(
                ['from_unit_id' => $unitMap['G'], 'to_unit_id' => $unitMap['KG']],
                ['conversion_factor' => 0.001000, 'operator' => '*']
            );
        }

        if (isset($unitMap['TON']) && isset($unitMap['KG'])) {
            UnitConversion::firstOrCreate(
                ['from_unit_id' => $unitMap['TON'], 'to_unit_id' => $unitMap['KG']],
                ['conversion_factor' => 1000.000000, 'operator' => '*']
            );
            UnitConversion::firstOrCreate(
                ['from_unit_id' => $unitMap['KG'], 'to_unit_id' => $unitMap['TON']],
                ['conversion_factor' => 0.001000, 'operator' => '*']
            );
        }

        if (isset($unitMap['DOZ']) && isset($unitMap['PCS'])) {
            UnitConversion::firstOrCreate(
                ['from_unit_id' => $unitMap['DOZ'], 'to_unit_id' => $unitMap['PCS']],
                ['conversion_factor' => 12.000000, 'operator' => '*']
            );
        }

        // 3. Product Categories for Plastic & Polymer Manufacturing
        $categories = [
            ['name' => 'HDPE Containers & Bottles', 'code' => 'HDPE-CONT', 'description' => 'Blow molded HDPE bottles and containers'],
            ['name' => 'PP Caps & Closures', 'code' => 'PP-CAPS', 'description' => 'Injection molded polypropylene caps, plugs and seals'],
            ['name' => 'PET Preforms & Bottles', 'code' => 'PET-PROD', 'description' => 'Polyethylene Terephthalate preforms and bottles'],
            ['name' => 'Polymer Raw Materials', 'code' => 'RAW-POLY', 'description' => 'Virgin and recycled polymer granules (HDPE, PP, LDPE, LLDPE)'],
            ['name' => 'Color Masterbatches & Additives', 'code' => 'ADD-MB', 'description' => 'Pigments, UV stabilizers, optical brighteners, processing aids'],
            ['name' => 'Packaging Materials', 'code' => 'PKG-MAT', 'description' => 'Corrugated boxes, liners, shrink films, tape'],
            ['name' => 'Custom Job Work Products', 'code' => 'JW-PROD', 'description' => 'Client specific molded items produced under job work contracts'],
        ];

        $categoryMap = [];
        foreach ($categories as $cat) {
            $created = ProductCategory::firstOrCreate(['code' => $cat['code']], $cat);
            $categoryMap[$cat['code']] = $created->id;
        }

        // 4. Default Primary Warehouse
        $warehouse = Warehouse::firstOrCreate(
            ['code' => 'WH-MAIN'],
            [
                'name'       => 'Main Plant Warehouse',
                'location'   => 'Plant 1, Ahmedabad',
                'address'    => 'Plot No. 42, GIDC Industrial Estate, Vatva, Ahmedabad, Gujarat',
                'is_primary' => true,
                'status'     => 'active',
            ]
        );

        // 5. Backfill existing products
        $inventoryService = app(InventoryService::class);
        foreach (Product::all() as $p) {
            if (empty($p->sku)) {
                $p->sku = Product::generateSku();
            }
            if (empty($p->warehouse_id) && $warehouse) {
                $p->warehouse_id = $warehouse->id;
            }
            if (empty($p->category_id) && isset($categoryMap['HDPE-CONT'])) {
                $p->category_id = $categoryMap['HDPE-CONT'];
            }
            if ((float) $p->average_cost <= 0) {
                $p->average_cost = (float) $p->price;
            }
            $p->save();

            if ((float) $p->stock_quantity > 0 && StockLedger::where('product_id', $p->id)->count() === 0) {
                $inventoryService->recordOpeningStock(
                    $p,
                    (float) $p->stock_quantity,
                    (float) $p->price,
                    now()->toDateString(),
                    'Migrated baseline opening stock'
                );
            }
        }
    }
}
