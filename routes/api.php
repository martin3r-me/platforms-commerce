<?php

use Platform\Commerce\Http\Controllers\SupplierIngestController;

Route::post('/suppliers/ingest/{token}', SupplierIngestController::class)
    ->name('commerce.api.suppliers.ingest');
