<?php

declare(strict_types=1);

namespace Sfadless\Pdf;

use Sfadless\Pdf\Event\PreRenderPdfEvent;
use Sfadless\Pdf\Model\PdfOptions;
use Sfadless\Pdf\Model\PdfWritable;
use Sfadless\Pdf\Renderer\OutputDestination;
use Sfadless\Pdf\Renderer\PdfRenderer;
use Sfadless\Pdf\Renderer\RendererOptions;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Twig\Environment;

final readonly class PdfFactory
{
    public function __construct(
        private PdfRenderer $renderer,
        private Environment $twig,
        private EventDispatcherInterface $dispatcher
    ) {}

    public function writePdf(PdfWritable $pdfWritable): string
    {
        $event = new PreRenderPdfEvent($pdfWritable);

        $this->dispatcher->dispatch($event);

        $context = $event->getParameters();

        $html = $this->twig->render(
            $pdfWritable->getPdfTemplate(),
            $context
        );

        $options = $this->configureOptions($pdfWritable->getPdfOptions());

        return $this->renderer->render($html, $options);
    }

    private function configureOptions(array $options): RendererOptions
    {
        $resolver = new OptionsResolver();

        $options = $resolver
            ->setDefault(PdfOptions::FILE_NAME, 'file.pdf')
            ->setDefault(PdfOptions::DESTINATION, OutputDestination::STRING)
            ->setAllowedTypes(PdfOptions::DESTINATION, OutputDestination::class)
            ->setDefined(PdfOptions::FOOTER)
            ->setDefined(PdfOptions::PDF_BEFORE)
            ->setDefined(PdfOptions::PDF_AFTER)
            ->resolve()
        ;

        return new RendererOptions(
            fileName: $options[PdfOptions::FILE_NAME],
            destination: $options[PdfOptions::DESTINATION],
            footer: $options[PdfOptions::FOOTER] ?? null,
            pdfBefore: $options[PdfOptions::PDF_BEFORE] ?? null,
            pdfAfter: $options[PdfOptions::PDF_AFTER] ?? [],
        );
    }
}
