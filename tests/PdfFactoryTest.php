<?php

declare(strict_types=1);

namespace Sfadless\Pdf\Test;

use PHPUnit\Framework\TestCase;
use Sfadless\Pdf\Event\PreRenderPdfEvent;
use Sfadless\Pdf\Model\PdfModel;
use Sfadless\Pdf\PdfFactory;
use Sfadless\Pdf\Renderer\Mpdf\MpdfRenderer;
use Sfadless\Pdf\Renderer\OutputDestination;
use Sfadless\Pdf\Renderer\RendererOptions;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Twig\Environment;
use Twig\Error\RuntimeError;
use Twig\Loader\ArrayLoader;

final class PdfFactoryTest extends TestCase
{
    private function createFactory(EventDispatcher $dispatcher, array $templates): PdfFactory
    {
        // strict_variables makes an undefined template variable throw, so tests can
        // observe whether event parameters actually reached the Twig context.
        $twig = new Environment(new ArrayLoader($templates), ['strict_variables' => true]);

        return new PdfFactory(new MpdfRenderer(), $twig, $dispatcher);
    }

    public function testForwardsOptionsToRenderer(): void
    {
        $factory = $this->createFactory(new EventDispatcher(), ['doc.twig' => '<h1>Body</h1>']);
        $existing = $this->writeTempPdf($this->renderTwoPagePdf());

        try {
            $pdf = $factory->writePdf(
                new PdfModel('doc.twig', [], new RendererOptions(pdfBefore: $existing)),
            );

            $this->assertSame(3, (new PdfPageCounter())->countPages($pdf));
        } finally {
            unlink($existing);
        }
    }

    public function testInjectsEventParametersIntoTemplate(): void
    {
        $dispatcher = new EventDispatcher();
        $dispatcher->addListener(
            PreRenderPdfEvent::class,
            static fn (PreRenderPdfEvent $event) => $event->addParameter('injected', 'from-listener'),
        );

        $factory = $this->createFactory($dispatcher, ['doc.twig' => 'Value: {{ injected }}']);

        $pdf = $factory->writePdf(new PdfModel('doc.twig'));

        $this->assertStringStartsWith('%PDF-', $pdf);
    }

    public function testThrowsOnMissingTemplateParameter(): void
    {
        $factory = $this->createFactory(new EventDispatcher(), ['doc.twig' => 'Value: {{ injected }}']);

        $this->expectException(RuntimeError::class);

        $factory->writePdf(new PdfModel('doc.twig'));
    }

    private function renderTwoPagePdf(): string
    {
        return (new MpdfRenderer())->render(
            '<h1>One</h1><pagebreak /><h1>Two</h1>',
            new RendererOptions(destination: OutputDestination::STRING),
        );
    }

    private function writeTempPdf(string $bytes): string
    {
        $path = tempnam(sys_get_temp_dir(), 'pdf_test_') . '.pdf';
        file_put_contents($path, $bytes);

        return $path;
    }
}
