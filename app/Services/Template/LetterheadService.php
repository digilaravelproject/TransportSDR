<?php

namespace App\Services\Template;

use App\Models\{Tenant, TemplateLog};
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\File;

class LetterheadService
{
    public function generate(Tenant $tenant, array $data): array
    {
        $dir = storage_path(
            'app' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR .
                'tenants' . DIRECTORY_SEPARATOR . $tenant->id . DIRECTORY_SEPARATOR . 'letterheads'
        );

        if (!File::exists($dir)) {
            File::makeDirectory($dir, 0775, true);
        }

        $fileName     = 'letterhead-' . now()->format('Ymd-His') . '.pdf';
        $absoluteFile = $dir . DIRECTORY_SEPARATOR . $fileName;
        $storagePath  = "tenants/{$tenant->id}/letterheads/{$fileName}";

        Pdf::loadView('pdf.templates.letterhead', [
            'tenant'  => $tenant,
            'content' => $data['content'] ?? '',
            'subject' => $data['subject'] ?? '',
            'to'      => $data['to']      ?? '',
            'date'    => $data['date']    ?? now()->format('d-m-Y'),
            'ref'     => $data['ref']     ?? '',
        ])
            ->setPaper('a4')
            ->save($absoluteFile);

        $log = TemplateLog::create([
            'template_type' => 'letterhead',
            'file_path'     => $storagePath,
            'file_name'     => $fileName,
        ]);

        return [
            'absolute_path' => $absoluteFile,
            'file_name'     => $fileName,
            'log'           => $log,
        ];
    }
}
