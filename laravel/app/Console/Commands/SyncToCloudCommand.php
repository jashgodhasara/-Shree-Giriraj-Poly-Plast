<?php

namespace App\Console\Commands;

use App\Services\CloudSyncService;
use Illuminate\Console\Command;

class SyncToCloudCommand extends Command
{
    protected $signature = 'erp:sync-cloud';
    protected $description = 'Synchronize local offline ERP data with Render Cloud';

    public function handle(CloudSyncService $syncService)
    {
        $this->info('Starting Shree Giriraj ERP Cloud Synchronization...');

        $status = $syncService->getSyncStatus();
        if (!$status['is_online']) {
            $this->warn('Cloud server is currently unreachable. Changes remain stored locally.');
            return 1;
        }

        $result = $syncService->sync();

        if ($result['success']) {
            $this->info('✅ ' . $result['message']);
            $this->table(
                ['Entity', 'Count Synced'],
                [
                    ['Customers', $result['report']['customers_synced'] ?? 0],
                    ['Products', $result['report']['products_synced'] ?? 0],
                    ['Suppliers', $result['report']['suppliers_synced'] ?? 0],
                    ['Invoices', $result['report']['invoices_synced'] ?? 0],
                    ['Payments', $result['report']['payments_synced'] ?? 0],
                    ['Production Logs', $result['report']['production_synced'] ?? 0],
                ]
            );
            return 0;
        } else {
            $this->error('❌ Sync failed: ' . $result['message']);
            return 1;
        }
    }
}
