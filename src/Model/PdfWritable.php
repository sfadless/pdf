<?php

declare(strict_types=1);

namespace Sfadless\Pdf\Model;

interface PdfWritable
{
    public function getPdfTemplate(): string;

    public function getPdfParameters(): array;

    public function getPdfOptions(): array;
}