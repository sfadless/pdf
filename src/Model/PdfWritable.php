<?php

declare(strict_types=1);

namespace Sfadless\Pdf\Model;

use Sfadless\Pdf\Renderer\RendererOptions;

interface PdfWritable
{
    public function getPdfTemplate(): string;

    public function getPdfParameters(): array;

    public function getPdfOptions(): RendererOptions;
}
