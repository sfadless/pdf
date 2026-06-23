<?php

declare(strict_types=1);

namespace Sfadless\Pdf\Model;

final readonly class PdfModel implements PdfWritable
{
    public function __construct(
        private string $pdfTemplate,
        private array $pdfParameters = [],
        private array $pdfOptions = []
    ) {}

    public function getPdfTemplate(): string
    {
        return $this->pdfTemplate;
    }

    public function getPdfParameters(): array
    {
        return $this->pdfParameters;
    }

    public function getPdfOptions(): array
    {
        return $this->pdfOptions;
    }
}