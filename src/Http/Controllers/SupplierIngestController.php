<?php

namespace Platform\Commerce\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Platform\Commerce\Models\CommerceSupplier;
use Platform\Commerce\Models\CommerceSupplierImport;
use Platform\Commerce\Services\SupplierImportService;

class SupplierIngestController extends Controller
{
    /**
     * POST /api/commerce/suppliers/ingest/{token}
     *
     * Webhook endpoint for receiving supplier data. Token-based auth (no session required).
     */
    public function __invoke(Request $request, string $token, SupplierImportService $importService): JsonResponse
    {
        $supplier = CommerceSupplier::where('endpoint_token', $token)
            ->whereIn('status', ['onboarding', 'active'])
            ->first();

        if (!$supplier) {
            return response()->json(['error' => 'Invalid or inactive token.'], 404);
        }

        $rawData = $request->getContent();

        // Parse body: try JSON first, then form fields
        $payload = json_decode($rawData, true);

        if ($payload === null) {
            $input = $request->all();
            if (!empty($input)) {
                $payload = $input;
                $rawData = json_encode($input);
            } else {
                return response()->json([
                    'error' => 'Invalid JSON payload.',
                    'hint' => 'Send data as JSON body with Content-Type: application/json.',
                    'received_content_type' => $request->header('Content-Type'),
                ], 422);
            }
        }

        // Onboarding: store sample, don't import
        if ($supplier->isOnboarding()) {
            return $this->handleOnboarding($supplier, $payload, $rawData);
        }

        // Active: import
        $import = $importService->importFromPayload($supplier, $payload);

        return response()->json([
            'import_id' => $import->id,
            'status' => $import->status,
            'rows_received' => $import->rows_received,
            'rows_created' => $import->rows_created,
            'rows_updated' => $import->rows_updated,
            'rows_skipped' => $import->rows_skipped,
            'duration_ms' => $import->duration_ms,
            'errors' => $import->error_log,
        ], $import->status === 'error' ? 422 : 200);
    }

    protected function handleOnboarding(CommerceSupplier $supplier, array $payload, string $rawData): JsonResponse
    {
        $metadata = $supplier->metadata ?? [];
        $metadata['sample_payload'] = $payload;
        $supplier->update(['metadata' => $metadata]);

        // Store raw payload for later processing after activation
        CommerceSupplierImport::create([
            'commerce_supplier_id' => $supplier->id,
            'status' => 'pending',
            'rows_received' => is_array($payload) ? (isset($payload[0]) ? count($payload) : 1) : 0,
            'raw_payload' => $rawData,
        ]);

        return response()->json([
            'status' => 'onboarding',
            'message' => 'Sample received. Configure your supplier to start importing.',
        ]);
    }
}
