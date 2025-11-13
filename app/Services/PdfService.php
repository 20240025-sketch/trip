<?php

namespace App\Services;

use App\Models\Plan;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\View;

class PdfService
{
    /**
     * Generate PDF for a plan
     *
     * @param Plan $plan
     * @return \Barryvdh\DomPDF\PDF
     */
    public function generate(Plan $plan)
    {
        // Load all necessary relationships
        $plan->load([
            'days.scheduleItems.images',
            'participants',
            'checklistItems',
            'images',
            'attachments'
        ]);

        // Prepare data for the view
        $data = [
            'plan' => $plan,
            'days' => $plan->days,
            'participants' => $plan->participants,
            'checklistItems' => $plan->checklistItems->groupBy('category'),
            'attachments' => $plan->attachments,
        ];

        // Generate PDF with DomPDF
        $pdf = Pdf::loadView('pdf.plan', $data);
        
        // Configure PDF with Japanese font support
        $pdf->setPaper('a4', 'portrait');
        
        // Get the Dompdf instance and configure it
        $dompdf = $pdf->getDomPDF();
        $options = $dompdf->getOptions();
        
        // Set font directories
        $options->setFontDir(storage_path('fonts'));
        $options->setFontCache(storage_path('fonts'));
        $options->setIsRemoteEnabled(false);
        $options->setDefaultFont('ipaexg');
        
        return $pdf;
    }

    /**
     * Download PDF for a plan
     *
     * @param Plan $plan
     * @return \Illuminate\Http\Response
     */
    public function download(Plan $plan)
    {
        $pdf = $this->generate($plan);
        
        // Generate filename
        $filename = $this->sanitizeFilename($plan->title) . '_' . now()->format('Ymd') . '.pdf';
        
        return $pdf->download($filename);
    }

    /**
     * Stream PDF for a plan
     *
     * @param Plan $plan
     * @return \Illuminate\Http\Response
     */
    public function stream(Plan $plan)
    {
        $pdf = $this->generate($plan);
        
        return $pdf->stream();
    }

    /**
     * Sanitize filename
     *
     * @param string $filename
     * @return string
     */
    private function sanitizeFilename(string $filename): string
    {
        // Remove special characters and spaces
        $filename = preg_replace('/[^a-zA-Z0-9\-_\s]/', '', $filename);
        $filename = preg_replace('/\s+/', '_', $filename);
        
        return $filename ?: 'plan';
    }
}

