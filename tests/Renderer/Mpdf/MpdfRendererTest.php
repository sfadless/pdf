<?php

declare(strict_types=1);

namespace Sfadless\Pdf\Test\Renderer\Mpdf;

use Mpdf\Mpdf;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Sfadless\Pdf\Renderer\Mpdf\MpdfRenderer;
use Sfadless\Pdf\Renderer\OutputDestination;
use Sfadless\Pdf\Renderer\RendererOptions;
use Sfadless\Pdf\Test\PdfPageCounter;

final class MpdfRendererTest extends TestCase
{
    private MpdfRenderer $renderer;

    private PdfPageCounter $pages;

    protected function setUp(): void
    {
        $this->renderer = new MpdfRenderer();
        $this->pages = new PdfPageCounter();
    }

    private function createOptions(array $overrides = []): RendererOptions
    {
        return new RendererOptions(...array_merge(
            ['destination' => OutputDestination::STRING],
            $overrides,
        ));
    }

    public function testRendersHtmlToSinglePage(): void
    {
        $pdf = $this->renderer->render('<h1>Test</h1>', $this->createOptions());

        $this->assertStringStartsWith('%PDF-', $pdf);
        $this->assertSame(1, $this->pages->countPages($pdf));
    }

    public function testPrependsAllPagesOfExistingPdf(): void
    {
        $existing = $this->writeTempPdf($this->renderTwoPages());

        try {
            $pdf = $this->renderer->render('<h1>Body</h1>', $this->createOptions(['pdfBefore' => $existing]));

            $this->assertSame(3, $this->pages->countPages($pdf));
        } finally {
            unlink($existing);
        }
    }

    public function testAppendsAllPagesOfExistingPdf(): void
    {
        $existing = $this->writeTempPdf($this->renderTwoPages());

        try {
            $pdf = $this->renderer->render('<h1>Body</h1>', $this->createOptions(['pdfAfter' => [$existing]]));

            $this->assertSame(3, $this->pages->countPages($pdf));
        } finally {
            unlink($existing);
        }
    }

    public function testKeepsSourcePageSize(): void
    {
        $landscape = new MpdfRenderer(['format' => 'A4-L']);
        $existing = $this->writeTempPdf($landscape->render('<h1>Landscape</h1>', $this->createOptions()));

        try {
            $pdf = $this->renderer->render('<h1>Body</h1>', $this->createOptions(['pdfBefore' => $existing]));

            $this->assertSame('L', $this->firstPageOrientation($pdf));
        } finally {
            unlink($existing);
        }
    }

    public function testThrowsWhenSourceFileMissing(): void
    {
        $this->expectException(RuntimeException::class);

        $this->renderer->render('<p>x</p>', $this->createOptions(['pdfBefore' => '/no/such/file.pdf']));
    }

    private function renderTwoPages(): string
    {
        return $this->renderer->render('<h1>One</h1><pagebreak /><h1>Two</h1>', $this->createOptions());
    }

    private function firstPageOrientation(string $pdfBytes): string
    {
        $path = $this->writeTempPdf($pdfBytes);

        try {
            $mpdf = new Mpdf();
            $mpdf->setSourceFile($path);
            $size = $mpdf->getTemplateSize($mpdf->ImportPage(1));

            return $size['width'] > $size['height'] ? 'L' : 'P';
        } finally {
            unlink($path);
        }
    }

    private function writeTempPdf(string $bytes): string
    {
        $path = tempnam(sys_get_temp_dir(), 'pdf_test_') . '.pdf';
        file_put_contents($path, $bytes);

        return $path;
    }
}
