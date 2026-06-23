<?php

declare(strict_types=1);

namespace Sfadless\Pdf\Renderer;

final readonly class RendererOptions
{
    public function __construct(
        public string $fileName = 'document.pdf',
        public OutputDestination $destination = OutputDestination::INLINE,
        public ?string $footer = null,
        public ?string $pdfBefore = null,
        public array $pdfAfter = [],
        public bool $pageNumbering = false,
    ) {}
}
