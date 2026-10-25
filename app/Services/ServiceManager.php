<?php

namespace App\Services;

use App\Exceptions\ServiceUnavailableException;
use App\Models\ServiceConfiguration;
use App\Models\ServiceStatusAuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ServiceManager
{
    public const CACHE_TTL_SECONDS = 300;

    /**
     * Check if a service is currently available (enabled).
     */
    public function isAvailable(string $serviceKey): bool
    {
        $key = strtolower(trim($serviceKey));

        return (bool) Cache::remember("system_service:status:{$key}", self::CACHE_TTL_SECONDS, function () use ($key) {
            $service = ServiceConfiguration::where('key', $key)->first();
            // If service is not registered in configuration table, default to true
            return $service ? (bool) $service->is_enabled : true;
        });
    }

    /**
     * Ensure a service is available or throw ServiceUnavailableException.
     *
     * @throws ServiceUnavailableException
     */
    public function ensureAvailable(string $serviceKey): void
    {
        if (! $this->isAvailable($serviceKey)) {
            $message = $this->getMaintenanceMessage($serviceKey);
            throw new ServiceUnavailableException($serviceKey, $message);
        }
    }

    /**
     * Get maintenance or downtime explanation message for service.
     */
    public function getMaintenanceMessage(string $serviceKey): string
    {
        $key = strtolower(trim($serviceKey));

        $service = $this->getService($key);
        if ($service && !empty($service->maintenance_message)) {
            return $service->maintenance_message;
        }

        return 'This service is temporarily unavailable. Please try again later.';
    }

    /**
     * Fetch a service configuration model by key (cached).
     */
    public function getService(string $serviceKey): ?ServiceConfiguration
    {
        $key = strtolower(trim($serviceKey));

        return Cache::remember("system_service:model:{$key}", self::CACHE_TTL_SECONDS, function () use ($key) {
            return ServiceConfiguration::with('updatedByAdmin')->where('key', $key)->first();
        });
    }

    /**
     * Get an associative array of all service statuses ['airtime' => ['is_enabled' => bool, 'maintenance_message' => ?string], ...].
     */
    public function getAllStatuses(): array
    {
        return Cache::remember('system_service:all_statuses', self::CACHE_TTL_SECONDS, function () {
            return ServiceConfiguration::all()
                ->keyBy('key')
                ->map(fn ($svc) => [
                    'is_enabled' => (bool) $svc->is_enabled,
                    'maintenance_message' => $svc->maintenance_message,
                ])
                ->toArray();
        });
    }

    /**
     * Retrieve all service configuration models for admin viewing.
     */
    public function allServices(): Collection
    {
        return ServiceConfiguration::with('updatedByAdmin')
            ->orderBy('category')
            ->orderBy('name')
            ->get();
    }

    /**
     * Toggle or update service availability status with audit logging.
     */
    public function updateStatus(
        string $serviceKey,
        bool $enabled,
        ?string $reason = null,
        ?string $maintenanceMessage = null,
        ?User $admin = null,
        ?string $ipAddress = null
    ): ServiceConfiguration {
        $key = strtolower(trim($serviceKey));

        return DB::transaction(function () use ($key, $enabled, $reason, $maintenanceMessage, $admin, $ipAddress) {
            $service = ServiceConfiguration::lockForUpdate()->where('key', $key)->firstOrFail();

            $previousStatus = (bool) $service->is_enabled;
            $newStatus = (bool) $enabled;

            $service->is_enabled = $newStatus;
            $service->updated_by = $admin?->id;
            $service->last_status_changed_at = now();

            if ($maintenanceMessage !== null) {
                $service->maintenance_message = trim($maintenanceMessage) ?: null;
            }

            $service->save();

            // Record immutable audit log entry
            ServiceStatusAuditLog::create([
                'service_id' => $service->id,
                'service_key' => $service->key,
                'admin_id' => $admin?->id,
                'admin_name' => $admin?->name ?? 'System Admin',
                'admin_email' => $admin?->email,
                'previous_status' => $previousStatus,
                'new_status' => $newStatus,
                'reason' => $reason ? trim($reason) : null,
                'maintenance_message' => $service->maintenance_message,
                'ip_address' => $ipAddress,
                'created_at' => now(),
            ]);

            // Flush cache
            $this->clearCache($key);

            Log::info("Admin Service Management: Service [{$key}] toggled to [" . ($newStatus ? 'ENABLED' : 'DISABLED') . "] by Admin ID [{$admin?->id}]. Reason: {$reason}");

            return $service->fresh(['updatedByAdmin']);
        });
    }

    /**
     * Clear all cached service statuses.
     */
    public function clearCache(?string $serviceKey = null): void
    {
        if ($serviceKey) {
            $key = strtolower(trim($serviceKey));
            Cache::forget("system_service:status:{$key}");
            Cache::forget("system_service:model:{$key}");
        }

        Cache::forget('system_service:all_statuses');
    }
}
