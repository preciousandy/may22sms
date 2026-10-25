<?php

namespace Tests\Feature;

use App\Models\BoostCategory;
use App\Models\BoostService;
use App\Models\ServiceConfiguration;
use App\Models\ServiceStatusAuditLog;
use App\Models\Setting;
use App\Models\User;
use App\Models\UtilityTransaction;
use App\Services\ServiceManager;
use App\Services\UtilityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class AdminServiceManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $staffWithSettings;
    protected User $regularUser;
    protected ServiceManager $serviceManager;

    protected function setUp(): void
    {
        parent::setUp();

        // General settings
        Setting::updateOrCreate(['key' => 'ngn_per_usd'], ['value' => '1500']);
        Setting::updateOrCreate(['key' => 'bigisub_simulate'], ['value' => '1']);
        Setting::updateOrCreate(['key' => 'utility_airtime_discount_percent'], ['value' => '2.0']);

        // Admin user
        $this->admin = User::factory()->create([
            'name' => 'Super Admin',
            'email' => 'superadmin@example.com',
            'is_admin' => true,
            'account_status' => 'active',
            'walletBalance' => 500.00,
        ]);

        // Staff user with settings permission
        $this->staffWithSettings = User::factory()->create([
            'name' => 'Settings Staff',
            'email' => 'settingsstaff@example.com',
            'is_admin' => false,
            'is_support' => true,
            'staff_permissions' => ['settings'],
            'account_status' => 'active',
            'walletBalance' => 100.00,
        ]);

        // Regular user
        $this->regularUser = User::factory()->create([
            'name' => 'Regular User',
            'email' => 'user@example.com',
            'is_admin' => false,
            'account_status' => 'active',
            'walletBalance' => 200.00,
        ]);

        $this->serviceManager = app(ServiceManager::class);
    }

    /**
     * 1. An authorized administrator can access service management dashboard.
     */
    public function test_authorized_admin_can_access_service_management_dashboard(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.services.index'));

        $response->assertStatus(200);
        $response->assertSee('Service Controls &amp; Availability', false);
        $response->assertSee('Airtime Recharge');
        $response->assertSee('Data Bundles');
        $response->assertSee('Electricity Payments');
        $response->assertSee('SMS Verification');
        $response->assertSee('Social Boosting');
        $response->assertSee('Crypto to Naira Conversion');
    }

    /**
     * Staff with settings permission can also access.
     */
    public function test_staff_with_settings_permission_can_access_dashboard(): void
    {
        $response = $this->actingAs($this->staffWithSettings)->get(route('admin.services.index'));
        $response->assertStatus(200);
    }

    /**
     * 2. An authorized administrator can disable a service.
     */
    public function test_authorized_admin_can_disable_a_service(): void
    {
        $this->assertTrue($this->serviceManager->isAvailable('airtime'));

        $response = $this->actingAs($this->admin)->post(route('admin.services.toggle', 'airtime'), [
            'is_enabled' => 0,
            'reason' => 'Emergency maintenance by provider',
            'maintenance_message' => 'Airtime is temporarily paused for upstream maintenance.',
        ]);

        $response->assertRedirect(route('admin.services.index'));
        $response->assertSessionHas('success');

        $this->assertFalse($this->serviceManager->isAvailable('airtime'));

        $service = ServiceConfiguration::where('key', 'airtime')->firstOrFail();
        $this->assertFalse((bool) $service->is_enabled);
        $this->assertEquals($this->admin->id, $service->updated_by);
        $this->assertEquals('Airtime is temporarily paused for upstream maintenance.', $service->maintenance_message);
        $this->assertNotNull($service->last_status_changed_at);
    }

    /**
     * 3. An authorized administrator can re-enable a service.
     */
    public function test_authorized_admin_can_re_enable_a_service(): void
    {
        // First disable it
        $this->serviceManager->updateStatus('data', false, 'Disabling data', null, $this->admin);
        $this->assertFalse($this->serviceManager->isAvailable('data'));

        // Now re-enable via HTTP POST
        $response = $this->actingAs($this->admin)->post(route('admin.services.toggle', 'data'), [
            'is_enabled' => 1,
            'reason' => 'Maintenance completed successfully',
        ]);

        $response->assertRedirect(route('admin.services.index'));
        $this->assertTrue($this->serviceManager->isAvailable('data'));

        $service = ServiceConfiguration::where('key', 'data')->firstOrFail();
        $this->assertTrue((bool) $service->is_enabled);
        $this->assertEquals($this->admin->id, $service->updated_by);
    }

    /**
     * 4. Unauthorized users cannot change service settings.
     */
    public function test_unauthorized_user_cannot_change_service_settings(): void
    {
        // Guest user is redirected to admin login or login
        $response = $this->post(route('admin.services.toggle', 'airtime'), [
            'is_enabled' => 0,
        ]);
        $response->assertRedirect('/admin/login');

        // Regular non-admin user
        $response = $this->actingAs($this->regularUser)->post(route('admin.services.toggle', 'airtime'), [
            'is_enabled' => 0,
        ]);
        $response->assertRedirect('/dashboard');

        // Airtime status remains untouched (true)
        $this->assertTrue($this->serviceManager->isAvailable('airtime'));
    }

    /**
     * 5. Disabled services reject new purchase requests on the backend.
     */
    public function test_disabled_services_reject_new_purchase_requests_on_backend(): void
    {
        // 5a. Airtime disabled
        $this->serviceManager->updateStatus(
            'airtime',
            false,
            'Upstream down',
            'Airtime is undergoing scheduled maintenance.',
            $this->admin
        );

        $resAirtime = $this->actingAs($this->regularUser)->post(route('utilities.purchase.airtime'), [
            'network' => 'mtn',
            'phone_number' => '08031234567',
            'amount' => 500,
        ], ['Accept' => 'application/json']);

        $resAirtime->assertStatus(403);
        $resAirtime->assertJson([
            'success' => false,
            'message' => 'Airtime is undergoing scheduled maintenance.',
        ]);

        // 5b. Data disabled
        $this->serviceManager->updateStatus('data', false, 'Network issues', null, $this->admin);

        $resData = $this->actingAs($this->regularUser)->post(route('utilities.purchase.data'), [
            'network' => 'mtn',
            'phone_number' => '08031234567',
            'plan_id' => 'mtn-1gb-sme',
        ], ['Accept' => 'application/json']);

        $resData->assertStatus(403);
        $this->assertStringContainsString('temporarily', strtolower($resData->json('message')));

        // 5c. Electricity disabled
        $this->serviceManager->updateStatus('electricity', false, 'DisCo API down', null, $this->admin);

        $resElec = $this->actingAs($this->regularUser)->post(route('utilities.pay.electricity'), [
            'disco' => 'ekedc',
            'meter_number' => '01428394829',
            'meter_type' => 'prepaid',
            'amount' => 2000,
        ], ['Accept' => 'application/json']);

        $resElec->assertStatus(403);
        $this->assertStringContainsString('temporarily unavailable', $resElec->json('message'));

        // 5d. Electricity meter verification rejected when electricity is disabled
        $resVerifyMeter = $this->actingAs($this->regularUser)->postJson(route('utilities.api.verify-meter'), [
            'disco' => 'ekedc',
            'meter_number' => '01428394829',
            'meter_type' => 'prepaid',
        ]);
        $resVerifyMeter->assertStatus(403);

        // 5e. SMS verification disabled
        $this->serviceManager->updateStatus('sms_verify', false, 'SMS Provider Maintenance', 'Virtual SMS is temporarily paused.', $this->admin);

        $resSms = $this->actingAs($this->regularUser)->postJson('/api/sms/buy', [
            'country' => 'usa',
            'product' => 'telegram',
            'operator' => 'any',
            'price' => 1.50,
        ]);

        $resSms->assertStatus(403);
        $resSms->assertJson([
            'success' => false,
            'message' => 'Virtual SMS is temporarily paused.',
        ]);

        // 5f. Social boosting disabled
        $this->serviceManager->updateStatus('boosting', false, 'JAP Down', null, $this->admin);

        // Create a dummy boost service
        $boostService = BoostService::create([
            'jap_service_id' => 9999,
            'name' => 'Instagram Likes',
            'type' => 'Default',
            'category' => 'Instagram',
            'rate' => 1.20,
            'min' => 10,
            'max' => 10000,
            'is_active' => true,
        ]);

        $resBoost = $this->actingAs($this->regularUser)->postJson('/social-boosting/order', [
            'boost_service_id' => $boostService->id,
            'link' => 'https://instagram.com/p/test1234',
            'quantity' => 100,
        ]);

        $resBoost->assertStatus(403);
        $this->assertStringContainsString('temporarily unavailable', $resBoost->json('message'));

        // Ensure user wallet balance was NOT touched
        $this->assertEquals(200.00, (float) $this->regularUser->fresh()->walletBalance);
    }

    /**
     * 6. Enabled services accept valid purchase requests.
     */
    public function test_enabled_services_accept_valid_purchase_requests(): void
    {
        // Ensure airtime is enabled
        $this->serviceManager->updateStatus('airtime', true, 'Online', null, $this->admin);

        $initialBalance = (float) $this->regularUser->walletBalance;

        $response = $this->actingAs($this->regularUser)->post(route('utilities.purchase.airtime'), [
            'network' => 'mtn',
            'phone_number' => '08031234567',
            'amount' => 1000,
        ], ['Accept' => 'application/json']);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        // Wallet was debited appropriately
        $this->assertLessThan($initialBalance, (float) $this->regularUser->fresh()->walletBalance);

        // Transaction record was created
        $this->assertDatabaseHas('utility_transactions', [
            'user_id' => $this->regularUser->id,
            'service_type' => 'airtime',
            'customer_target' => '08031234567',
        ]);
    }

    /**
     * 7. Existing pending transactions continue to be reconciled and refunded correctly when service is disabled.
     */
    public function test_existing_pending_transactions_continue_to_be_reconciled_and_refunded_when_service_is_disabled(): void
    {
        $utilityService = app(UtilityService::class);

        // 1. Create a pending transaction while airtime was active
        $tx = UtilityTransaction::create([
            'user_id' => $this->regularUser->id,
            'reference' => 'UTL-AIR-TEST123456',
            'service_type' => 'airtime',
            'provider' => 'bigisub',
            'network_or_disco' => 'mtn',
            'product_name' => 'MTN Airtime Top-Up',
            'customer_target' => '08031234567',
            'amount_ngn' => 1000.00,
            'amount_usd' => 0.6533,
            'total_charged_usd' => 0.6533,
            'exchange_rate' => 1500,
            'status' => 'uncertain',
            'error_message' => 'Pending upstream provider confirmation',
        ]);

        // Deduct wallet for transaction
        $this->regularUser->decrement('walletBalance', 0.6533);
        $balanceBeforeRefund = (float) $this->regularUser->fresh()->walletBalance;

        // 2. Administrator disables Airtime service
        $this->serviceManager->updateStatus('airtime', false, 'Service halted', null, $this->admin);
        $this->assertFalse($this->serviceManager->isAvailable('airtime'));

        // 3. Admin or system performs legitimate refund on existing transaction
        $refundedTx = $utilityService->refundTransaction($tx, $this->admin->id, 'Reconciliation confirmed timeout failure');

        $this->assertEquals('refunded', $refundedTx->status);
        $this->assertNotNull($refundedTx->refunded_at);

        // User was credited back their wallet funds despite airtime being disabled
        $this->assertEquals(
            round($balanceBeforeRefund + 0.6533, 4),
            round((float) $this->regularUser->fresh()->walletBalance, 4)
        );
    }

    /**
     * 8. Status changes persist across application restarts (in database).
     */
    public function test_status_changes_persist_in_database(): void
    {
        $this->serviceManager->updateStatus(
            'electricity',
            false,
            'Grid maintenance',
            'Power DisCo billing offline.',
            $this->admin
        );

        $this->assertDatabaseHas('service_configurations', [
            'key' => 'electricity',
            'is_enabled' => false,
            'updated_by' => $this->admin->id,
            'maintenance_message' => 'Power DisCo billing offline.',
        ]);

        // Clear in-memory cache to simulate full app reboot
        Cache::flush();

        // Fresh instance reading directly from database
        $freshManager = new ServiceManager();
        $this->assertFalse($freshManager->isAvailable('electricity'));
        $this->assertEquals('Power DisCo billing offline.', $freshManager->getMaintenanceMessage('electricity'));
    }

    /**
     * 9. Audit logs record all administrative changes with IP address and reason.
     */
    public function test_audit_logs_record_all_administrative_changes(): void
    {
        $initialAuditCount = ServiceStatusAuditLog::count();

        $this->actingAs($this->admin)->post(route('admin.services.toggle', 'betting'), [
            'is_enabled' => 0,
            'reason' => 'Licensing compliance review',
            'maintenance_message' => 'Betting top-up is temporarily suspended.',
        ]);

        $this->assertEquals($initialAuditCount + 1, ServiceStatusAuditLog::count());

        $log = ServiceStatusAuditLog::latest()->first();
        $this->assertEquals('betting', $log->service_key);
        $this->assertEquals($this->admin->id, $log->admin_id);
        $this->assertTrue((bool) $log->previous_status);
        $this->assertFalse((bool) $log->new_status);
        $this->assertEquals('Licensing compliance review', $log->reason);
        $this->assertNotNull($log->ip_address);
    }

    /**
     * 10. Cached service availability does not allow purchases after a disable action has taken effect.
     */
    public function test_cached_service_availability_invalidates_immediately(): void
    {
        // 1. Warm cache
        $this->assertTrue($this->serviceManager->isAvailable('airtime'));

        // Cache key is populated
        $this->assertTrue(Cache::has('system_service:status:airtime'));

        // 2. Admin disables service via ServiceManager or endpoint
        $this->serviceManager->updateStatus('airtime', false, 'Immediate disable test', null, $this->admin);

        // 3. Cache must reflect the disabled state immediately
        $this->assertFalse($this->serviceManager->isAvailable('airtime'));

        // 4. Any direct purchase attempt right after is immediately rejected
        $res = $this->actingAs($this->regularUser)->postJson(route('utilities.purchase.airtime'), [
            'network' => 'mtn',
            'phone_number' => '08031234567',
            'amount' => 500,
        ]);

        $res->assertStatus(403);
    }
}
