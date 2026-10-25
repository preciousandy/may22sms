<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ServiceConfiguration;
use App\Models\ServiceStatusAuditLog;
use App\Services\ServiceManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ServiceManagementController extends Controller
{
    public function index(ServiceManager $serviceManager)
    {
        $services = $serviceManager->allServices();
        $recentLogs = ServiceStatusAuditLog::latest()->take(25)->get();

        $totalCount = $services->count();
        $enabledCount = $services->where('is_enabled', true)->count();
        $disabledCount = $totalCount - $enabledCount;

        return view('admin.services.index', compact(
            'services',
            'recentLogs',
            'totalCount',
            'enabledCount',
            'disabledCount'
        ));
    }

    public function toggle(Request $request, string $key, ServiceManager $serviceManager)
    {
        $request->validate([
            'is_enabled' => 'required|boolean',
            'reason' => 'nullable|string|max:500',
            'maintenance_message' => 'nullable|string|max:1000',
        ]);

        $enabled = (bool) $request->input('is_enabled');
        $reason = $request->input('reason');
        $maintenanceMessage = $request->input('maintenance_message');

        try {
            $service = $serviceManager->updateStatus(
                $key,
                $enabled,
                $reason,
                $maintenanceMessage,
                auth()->user(),
                $request->ip()
            );

            $actionText = $enabled ? 'enabled' : 'disabled';
            $msg = "Service \"{$service->name}\" was successfully {$actionText}.";

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $msg,
                    'service' => [
                        'key' => $service->key,
                        'name' => $service->name,
                        'is_enabled' => (bool) $service->is_enabled,
                        'last_status_changed_at' => $service->last_status_changed_at?->format('M d, Y h:i A'),
                        'updated_by_name' => $service->updatedByAdmin?->name ?? 'Admin',
                    ],
                ]);
            }

            return redirect()->route('admin.services.index')->with('success', $msg);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => "Service [{$key}] not found."], 404);
            }
            return redirect()->route('admin.services.index')->with('error', "Service [{$key}] not found.");
        } catch (\Throwable $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
            return redirect()->route('admin.services.index')->with('error', $e->getMessage());
        }
    }
}
