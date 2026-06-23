<?php

declare(strict_types=1);

namespace Sfadless\Pdf\Model;

use Sfadless\Pdf\Renderer\RendererOptions;

final readonly class PdfModel implements PdfWritable
{
    public function __construct(
        private string $pdfTemplate,
        private array $pdfParameters = [],
        private RendererOptions $pdfOptions = new RendererOptions(),
    ) {}

    public function getPdfTemplate(): string
    {
        return $this->pdfTemplate;
    }

    public function getPdfParameters(): array
    {
        return $this->pdfParameters;
    }

    public function getPdfOptions(): RendererOptions
    {
        return $this->pdfOptions;
    }
}
