<?php

declare(strict_types=1);

namespace Sfadless\Pdf\Test;

use Mpdf\Mpdf;

final class PdfPageCounter
{
    public function countPages(string $pdfBytes): int
    {
        $path = tempnam(sys_get_temp_dir(), 'pdf_test_') . '.pdf';
        file_put_contents($path, $pdfBytes);

        try {
            return (new Mpdf())->setSourceFile($path);
        } finally {
            unlink($path);
        }
    }
}
