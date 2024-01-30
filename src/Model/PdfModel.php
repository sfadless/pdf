<?php

declare(strict_types=1);

namespace Sfadless\Pdf\Model;

/**
 * @author Pavel Golikov <pgolikov327@gmail.com>
 */
final class PdfModel implements PdfWritable
{
    public function __construct(
        private readonly string $pdfTemplate,
        private readonly array $pdfParameters = [],
        private readonly array $pdfOptions = []
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