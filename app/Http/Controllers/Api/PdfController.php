<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Services\PdfService;

class PdfController extends Controller
{
    protected PdfService $pdfService;

    public function __construct(PdfService $pdfService)
    {
        $this->pdfService = $pdfService;
    }

    /**
     * Generate and download PDF for a plan
     */
    public function generate(string $planId)
    {
        $plan = Plan::findOrFail($planId);

        return $this->pdfService->download($plan);
    }

    /**
     * Stream PDF for preview
     */
    public function preview(string $planId)
    {
        $plan = Plan::findOrFail($planId);

        return $this->pdfService->stream($plan);
    }
}
