<?php

namespace App\Http\Controllers;

use App\Services\QuidaxService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class QuidaxWebhookController extends Controller
{
    protected QuidaxService $quidax;

    public function __construct(QuidaxService $quidax)
    {
        $this->quidax = $quidax;
    }

    /**
     * Handle incoming Quidax webhook event.
     */
    public function handle(Request $request)
    {
        $rawPayload = $request->getContent();
        
        // Quidax Ramp uses X-Ramp-Signature; older/alternate webhooks use Quidax-Signature
        $signature = $request->header('X-Ramp-Signature') 
            ?: $request->header('Quidax-Signature')
            ?: $request->header('x-ramp-signature');

        // Verify webhook signature
        if (! $this->quidax->verifyWebhookSignature($rawPayload, $signature)) {
            Log::warning('Quidax webhook rejected: Invalid signature', [
                'signature' => $signature,
                'ip' => $request->ip(),
            ]);

            return response()->json(['status' => 'error', 'message' => 'Invalid signature'], 401);
        }

        $payload = $request->json()->all();
        if (empty($payload)) {
            $payload = json_decode($rawPayload, true) ?: [];
        }

        try {
            $result = $this->quidax->handleWebhook($payload, $rawPayload, $signature);

            return response()->json($result, 200);
        } catch (\Throwable $e) {
            Log::error('Quidax webhook handling error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            // Quidax expects 200 once received; return 200 to acknowledge receipt and prevent endless retries
            return response()->json(['status' => 'error_handled', 'message' => $e->getMessage()], 200);
        }
    }
}
