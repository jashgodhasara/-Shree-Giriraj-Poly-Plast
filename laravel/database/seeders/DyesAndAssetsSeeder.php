<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\DyeAndMould;
use App\Models\DyeMaintenanceLog;
use App\Models\FactoryAsset;
use App\Models\AssetMaintenanceLog;
use App\Models\Product;
use App\Models\Supplier;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class DyesAndAssetsSeeder extends Seeder
{
    public function run(): void
    {
        $customer = Customer::first();
        $product  = Product::first();
        $supplier = Supplier::first();

        // 1. Dyes & Moulds
        $dyes = [
            [
                'code'                   => 'DIE-500ML-BOT',
                'name'                   => '500ml HDPE Bottle Blow Mould',
                'mould_type'             => 'Blow Mould',
                'cavities'               => 2,
                'ownership_type'         => 'Company Owned',
                'customer_id'            => null,
                'product_id'             => $product?->id,
                'compatible_machines'    => 'Auto Blow Moulding Machine (ABM-01 / ABM-02)',
                'rack_location'          => 'Tool Room Rack A - Bay 1',
                'status'                 => 'Mounted on Machine',
                'total_shots_count'      => 42500,
                'service_interval_shots' => 50000,
                'last_serviced_date'     => Carbon::today()->subMonths(1)->toDateString(),
                'next_service_due_date'  => Carbon::today()->addMonths(2)->toDateString(),
                'purchase_cost'          => 185000.00,
                'fabrication_date'       => Carbon::today()->subYears(2)->toDateString(),
                'specifications'         => [
                    'weight_kg'            => 145,
                    'core_steel'           => 'P20 / Aluminium Alloy',
                    'neck_size_mm'         => 28,
                    'cycle_time_sec'       => 14,
                    'cooling_type'         => 'Chilled Water Direct Channel',
                ],
                'notes'                  => 'Primary production mould for 500ml HDPE round bottles.',
            ],
            [
                'code'                   => 'DIE-1LTR-JAR',
                'name'                   => '1 Litre Wide Mouth Jar Mould',
                'mould_type'             => 'Blow Mould',
                'cavities'               => 1,
                'ownership_type'         => 'Company Owned',
                'customer_id'            => null,
                'product_id'             => null,
                'compatible_machines'    => 'ABM-02 (2.5L Capacity)',
                'rack_location'          => 'Tool Room Rack A - Bay 2',
                'status'                 => 'Ready / In Storage',
                'total_shots_count'      => 18200,
                'service_interval_shots' => 40000,
                'last_serviced_date'     => Carbon::today()->subMonths(3)->toDateString(),
                'next_service_due_date'  => Carbon::today()->addMonths(4)->toDateString(),
                'purchase_cost'          => 220000.00,
                'fabrication_date'       => Carbon::today()->subYear()->toDateString(),
                'specifications'         => [
                    'weight_kg'            => 180,
                    'core_steel'           => 'High Grade P20 Steel',
                    'neck_size_mm'         => 63,
                    'cycle_time_sec'       => 18,
                ],
                'notes'                  => 'Ghee and chemicals packaging jar mould.',
            ],
            [
                'code'                   => 'DIE-28MM-CAP',
                'name'                   => '28mm Screw Cap 8-Cavity Injection Mould',
                'mould_type'             => 'Injection Mould',
                'cavities'               => 8,
                'ownership_type'         => 'Company Owned',
                'customer_id'            => null,
                'product_id'             => null,
                'compatible_machines'    => '150T Windsor Injection Machine',
                'rack_location'          => 'Tool Room Rack B - Shelf 1',
                'status'                 => 'Mounted on Machine',
                'total_shots_count'      => 98000,
                'service_interval_shots' => 100000,
                'last_serviced_date'     => Carbon::today()->subMonths(2)->toDateString(),
                'next_service_due_date'  => Carbon::today()->addDays(10)->toDateString(),
                'purchase_cost'          => 350000.00,
                'fabrication_date'       => Carbon::today()->subYears(3)->toDateString(),
                'specifications'         => [
                    'weight_kg'            => 260,
                    'core_steel'           => 'H13 Hardened Steel (52 HRC)',
                    'runner_type'          => 'Hot Runner 8 Drops',
                    'cycle_time_sec'       => 9.5,
                ],
                'notes'                  => 'High-speed cap mould with hot runner manifold.',
            ],
            [
                'code'                   => 'DIE-CUSTOM-CL01',
                'name'                   => 'Custom Client Cosmetic Bottle Mould (200ml)',
                'mould_type'             => 'Blow Mould',
                'cavities'               => 2,
                'ownership_type'         => 'Client Owned',
                'customer_id'            => $customer?->id,
                'product_id'             => null,
                'compatible_machines'    => 'ABM-01',
                'rack_location'          => 'Tool Room Rack C - Client Vault',
                'status'                 => 'Ready / In Storage',
                'total_shots_count'      => 34000,
                'service_interval_shots' => 50000,
                'last_serviced_date'     => Carbon::today()->subMonths(2)->toDateString(),
                'next_service_due_date'  => Carbon::today()->addMonths(3)->toDateString(),
                'purchase_cost'          => 0.00,
                'fabrication_date'       => Carbon::today()->subMonths(8)->toDateString(),
                'specifications'         => [
                    'weight_kg'            => 110,
                    'ownership_contract'   => 'Job Work Dedicated Tool',
                ],
                'notes'                  => 'Client supplied mould for premium cosmetic lotion bottles.',
            ],
        ];

        foreach ($dyes as $dyeData) {
            $createdDye = DyeAndMould::firstOrCreate(['code' => $dyeData['code']], $dyeData);

            if ($createdDye->maintenanceLogs()->count() === 0) {
                DyeMaintenanceLog::create([
                    'dye_id'            => $createdDye->id,
                    'maintenance_date'  => Carbon::today()->subMonths(1)->toDateString(),
                    'maintenance_type'  => 'Preventive Cleaning & Pin Inspection',
                    'shots_at_service'  => $createdDye->total_shots_count - 5000,
                    'cost'              => 3500.00,
                    'performed_by'      => 'In-house Tool Room',
                    'vendor_name'       => null,
                    'work_description'  => 'Cavity ultrasonic cleaning, ejector guide lubrication, O-ring seal check.',
                    'next_due_date'     => Carbon::today()->addMonths(2)->toDateString(),
                ]);
            }
        }

        // 2. Factory Assets & Machinery
        $assets = [
            [
                'asset_code'            => 'MCH-INJ-150T',
                'name'                  => '150 Ton Microprocessor Injection Moulding Machine',
                'category'              => 'Moulding Machine',
                'make_brand'            => 'Windsor Machines Ltd.',
                'model_number'          => 'Sprint 150-V',
                'serial_number'         => 'WND-2022-7819',
                'tonnage_or_capacity'   => '150 Ton Clamping / 380g Shot Weight',
                'power_rating_kw'       => 28.5,
                'plant_location'        => 'Shop Floor Bay 1 - Machine Line 1',
                'purchase_date'         => Carbon::today()->subYears(3)->toDateString(),
                'purchase_cost'         => 1850000.00,
                'warranty_expiry'       => Carbon::today()->subYear()->toDateString(),
                'supplier_id'           => $supplier?->id,
                'status'                => 'Operational',
                'assigned_operator'     => 'Ramesh Patel (Shift A)',
                'last_service_date'     => Carbon::today()->subMonths(1)->toDateString(),
                'next_service_date'     => Carbon::today()->addMonths(2)->toDateString(),
                'service_interval_days' => 90,
                'notes'                 => 'Primary machine for cap and closure injection moulding.',
            ],
            [
                'asset_code'            => 'MCH-BLW-01',
                'name'                  => 'Automatic Double Station Blow Moulding Machine',
                'category'              => 'Moulding Machine',
                'make_brand'            => 'Jagmohan Blowpack',
                'model_number'          => 'JBM-5000D',
                'serial_number'         => 'JBM-2023-104',
                'tonnage_or_capacity'   => 'Up to 5 Litre Containers (Continuous Extrusion)',
                'power_rating_kw'       => 35.0,
                'plant_location'        => 'Shop Floor Bay 2 - Blow Line',
                'purchase_date'         => Carbon::today()->subYears(2)->toDateString(),
                'purchase_cost'         => 2400000.00,
                'warranty_expiry'       => Carbon::today()->subMonths(6)->toDateString(),
                'supplier_id'           => $supplier?->id,
                'status'                => 'Operational',
                'assigned_operator'     => 'Mahesh Parmar',
                'last_service_date'     => Carbon::today()->subMonths(2)->toDateString(),
                'next_service_date'     => Carbon::today()->addMonth()->toDateString(),
                'service_interval_days' => 90,
                'notes'                 => 'High production blow moulding for HDPE bottles and containers.',
            ],
            [
                'asset_code'            => 'CHILL-10TR',
                'name'                  => 'Industrial Air Cooled Water Chiller (10 TR)',
                'category'              => 'Compressor & Chiller',
                'make_brand'            => 'Blue Star / Shini',
                'model_number'          => 'SIC-10A',
                'serial_number'         => 'CHL-9941-2023',
                'tonnage_or_capacity'   => '10 TR Cooling Capacity (7°C Water Temp)',
                'power_rating_kw'       => 11.2,
                'plant_location'        => 'Utility Bay / Chiller Room',
                'purchase_date'         => Carbon::today()->subYears(2)->toDateString(),
                'purchase_cost'         => 420000.00,
                'warranty_expiry'       => Carbon::today()->addMonths(6)->toDateString(),
                'supplier_id'           => $supplier?->id,
                'status'                => 'Operational',
                'assigned_operator'     => 'Plant Electrician',
                'last_service_date'     => Carbon::today()->subMonths(1)->toDateString(),
                'next_service_date'     => Carbon::today()->addMonths(2)->toDateString(),
                'service_interval_days' => 60,
                'notes'                 => 'Supplies chilled water circulation to all mould cooling channels.',
            ],
            [
                'asset_code'            => 'COMP-SCRW-15HP',
                'name'                  => 'Rotary Screw Air Compressor with Refrigerant Dryer',
                'category'              => 'Compressor & Chiller',
                'make_brand'            => 'Atlas Copco / Elgi',
                'model_number'          => 'EG-11 VFD',
                'serial_number'         => 'ELG-2023-4512',
                'tonnage_or_capacity'   => '15 HP / 65 CFM @ 10 Bar',
                'power_rating_kw'       => 11.0,
                'plant_location'        => 'Compressor Utility Room',
                'purchase_date'         => Carbon::today()->subYears(2)->toDateString(),
                'purchase_cost'         => 380000.00,
                'warranty_expiry'       => Carbon::today()->subMonths(2)->toDateString(),
                'supplier_id'           => $supplier?->id,
                'status'                => 'Operational',
                'assigned_operator'     => 'Utility Supervisor',
                'last_service_date'     => Carbon::today()->subMonths(2)->toDateString(),
                'next_service_date'     => Carbon::today()->addMonth()->toDateString(),
                'service_interval_days' => 90,
                'notes'                 => 'High pressure oil-free dry air for blow pin expansion and pneumatic actuators.',
            ],
            [
                'asset_code'            => 'GRND-PLAST-01',
                'name'                  => 'Heavy Duty Plastic Scrap Granulator / Grinder',
                'category'              => 'Auxiliary Equipment',
                'make_brand'            => 'Prasad Koch / Techno',
                'model_number'          => 'GR-300',
                'serial_number'         => 'GR-2021-098',
                'tonnage_or_capacity'   => '150 Kg/Hr Re-grind Capacity',
                'power_rating_kw'       => 7.5,
                'plant_location'        => 'Recycling & Grinding Section',
                'purchase_date'         => Carbon::today()->subYears(3)->toDateString(),
                'purchase_cost'         => 160000.00,
                'warranty_expiry'       => Carbon::today()->subYears(2)->toDateString(),
                'supplier_id'           => $supplier?->id,
                'status'                => 'Standby',
                'assigned_operator'     => 'Recycling Operator',
                'last_service_date'     => Carbon::today()->subMonths(3)->toDateString(),
                'next_service_date'     => Carbon::today()->addMonth()->toDateString(),
                'service_interval_days' => 90,
                'notes'                 => 'Used for runner and flash re-grinding into reusable granules.',
            ],
            [
                'asset_code'            => 'DG-SET-125KVA',
                'name'                  => '125 kVA Silent Diesel Generator Set',
                'category'              => 'Electrical & Power',
                'make_brand'            => 'Kirloskar Oil Engines',
                'model_number'          => 'KG125WS',
                'serial_number'         => 'KIRL-2022-8812',
                'tonnage_or_capacity'   => '125 kVA / 100 kW Standby Power',
                'power_rating_kw'       => 100.0,
                'plant_location'        => 'Power Generator Yard',
                'purchase_date'         => Carbon::today()->subYears(3)->toDateString(),
                'purchase_cost'         => 920000.00,
                'warranty_expiry'       => Carbon::today()->subYear()->toDateString(),
                'supplier_id'           => $supplier?->id,
                'status'                => 'Standby',
                'assigned_operator'     => 'Electrical Team',
                'last_service_date'     => Carbon::today()->subMonths(1)->toDateString(),
                'next_service_date'     => Carbon::today()->addMonths(2)->toDateString(),
                'service_interval_days' => 90,
                'notes'                 => 'Emergency plant backup during power grid outage.',
            ],
        ];

        foreach ($assets as $assetData) {
            $createdAsset = FactoryAsset::firstOrCreate(['asset_code' => $assetData['asset_code']], $assetData);

            if ($createdAsset->maintenanceLogs()->count() === 0) {
                AssetMaintenanceLog::create([
                    'asset_id'             => $createdAsset->id,
                    'service_date'         => Carbon::today()->subMonths(1)->toDateString(),
                    'service_type'         => 'Quarterly Preventive Maintenance (PM)',
                    'cost'                 => 6500.00,
                    'technician_name'      => 'Vikram Solanki',
                    'vendor_name'          => 'Shreeji Industrial Services',
                    'parts_replaced'       => 'Hydraulic filter cartridge, high-temp grease refill, electrical terminal tightening.',
                    'problem_reported'     => 'Routine scheduled servicing.',
                    'action_taken'         => 'Serviced and load tested successfully.',
                    'status_after_service' => 'Operational',
                    'next_service_due'     => Carbon::today()->addMonths(2)->toDateString(),
                ]);
            }
        }
    }
}
