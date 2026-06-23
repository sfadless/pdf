<?php

declare(strict_types=1);

namespace Sfadless\Pdf\Renderer;

interface PdfRenderer
{
    public function render(string $html, RendererOptions $options): string;
}
