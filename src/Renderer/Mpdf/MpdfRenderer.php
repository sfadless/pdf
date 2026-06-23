<?php

declare(strict_types=1);

namespace Sfadless\Pdf\Renderer\Mpdf;

use Mpdf\Mpdf;
use RuntimeException;
use Sfadless\Pdf\Renderer\OutputDestination;
use Sfadless\Pdf\Renderer\PdfRenderer;
use Sfadless\Pdf\Renderer\RendererOptions;

final readonly class MpdfRenderer implements PdfRenderer
{
    public function __construct(public array $mpdfOptions = []) {}

    public function render(string $html, RendererOptions $options): string
    {
        $mpdf = new Mpdf($this->mpdfOptions);

        if ($options->pdfBefore) {
            $this->addFromSource($options->pdfBefore, $mpdf);
            $mpdf->AddPage();
        }

        if ($options->footer) {
            $mpdf->SetHTMLFooter($options->footer);
        }

        $mpdf->WriteHTML($html);

        if (!empty($options->pdfAfter)) {
            foreach ($options->pdfAfter as $file) {
                $this->addFromSource($file, $mpdf);
            }
        }

        return $mpdf->Output($options->fileName, $this->mapDestinationToMpdf($options->destination));
    }

    private function addFromSource(string $filepath, Mpdf $mpdf): void
    {
        if (! file_exists($filepath)) {
            throw new RuntimeException("Не удалось добавить pdf - файла $filepath не существует");
        }

        $pageCount = $mpdf->setSourceFile($filepath);

        $a4pageWidth = 210;
        $a4pageHeight = 297;

        for ($i = 1 ; $i <= $pageCount; $i++) {
            $tplId = $mpdf->ImportPage($i);
            $mpdf->AddPage(resetpagenum: 1, suppress: 'on');
            $mpdf->setFooter('');
            $mpdf->UseTemplate($tplId,0,0, $a4pageWidth, $a4pageHeight);
        }
    }

    private function mapDestinationToMpdf(OutputDestination $destination): string
    {
        return match ($destination) {
            OutputDestination::DOWNLOAD => 'D',
            OutputDestination::INLINE => 'I',
            OutputDestination::STRING => 'S',
            OutputDestination::FILE => 'F',
        };
    }
}
