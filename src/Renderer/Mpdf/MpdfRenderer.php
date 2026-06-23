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

        $footer = $this->resolveFooter($options);
        if ($footer !== null) {
            $mpdf->SetHTMLFooter($footer);
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
            throw new RuntimeException("Unable to add pdf - file $filepath does not exist");
        }

        $pageCount = $mpdf->setSourceFile($filepath);

        for ($i = 1 ; $i <= $pageCount; $i++) {
            $tplId = $mpdf->ImportPage($i);
            $size = $mpdf->getTemplateSize($tplId);

            // Match the added sheet to the source page size, so the page itself
            // matches the imported PDF instead of staying the document's default format.
            $mpdf->AddPageByArray([
                'orientation'  => 'P',
                'newformat'    => [$size['width'], $size['height']],
                'resetpagenum' => 1,
                'suppress'     => 'on',
            ]);
            $mpdf->setFooter('');
            $mpdf->UseTemplate($tplId, 0, 0, $size['width'], $size['height']);
        }
    }

    private function resolveFooter(RendererOptions $options): ?string
    {
        // An explicit footer wins; page numbering only provides a default one.
        if ($options->footer !== null) {
            return $options->footer;
        }

        if ($options->pageNumbering) {
            return '<div style="text-align: center">{PAGENO} / {nbpg}</div>';
        }

        return null;
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
