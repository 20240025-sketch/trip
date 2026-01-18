<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Services\PdfService;
use Illuminate\Http\Request;

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
    public function generate(Request $request, string $planId)
    {
        $plan = Plan::with([
            'days.scheduleItems',
            'participants',
            'checklistItems',
            'user'
        ])->findOrFail($planId);

        $user = $request->user();

        // Check if user can view this plan
        if (!$plan->canView($user) && !$plan->canEdit($user)) {
            return response()->json([
                'message' => 'この計画のPDFを出力する権限がありません。'
            ], 403);
        }

        return $this->pdfService->download($plan);
    }

    /**
     * Stream PDF for preview
     */
    public function preview(Request $request, string $planId)
    {
        $plan = Plan::with([
            'days.scheduleItems',
            'participants',
            'checklistItems',
            'user'
        ])->findOrFail($planId);

        $user = $request->user();

        // Check if user can view this plan
        if (!$plan->canView($user) && !$plan->canEdit($user)) {
            return response()->json([
                'message' => 'この計画のPDFをプレビューする権限がありません。'
            ], 403);
        }

        return $this->pdfService->stream($plan);
    }
}
