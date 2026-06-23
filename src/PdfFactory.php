<?php

declare(strict_types=1);

namespace Sfadless\Pdf;

use Sfadless\Pdf\Event\PreRenderPdfEvent;
use Sfadless\Pdf\Model\PdfWritable;
use Sfadless\Pdf\Renderer\PdfRenderer;
use Psr\EventDispatcher\EventDispatcherInterface;
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

        return $this->renderer->render($html, $pdfWritable->getPdfOptions());
    }
}
