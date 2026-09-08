<?php

namespace App\Http\Controllers;

use App\Services\CloudSyncService;
use Illuminate\Http\Request;

class SyncController extends Controller
{
    protected CloudSyncService $syncService;

    public function __construct(CloudSyncService $syncService)
    {
        $this->syncService = $syncService;
    }

    /**
     * Display Sync Management Center
     */
    public function index()
    {
        $status = $this->syncService->getSyncStatus();
        return view('sync.index', compact('status'));
    }

    /**
     * Get Sync Status JSON (for UI navbar polling)
     */
    public function status()
    {
        return response()->json($this->syncService->getSyncStatus());
    }

    /**
     * Trigger Manual / Auto Cloud Sync
     */
    public function trigger(Request $request)
    {
        $result = $this->syncService->sync();
        return response()->json($result);
    }
}
