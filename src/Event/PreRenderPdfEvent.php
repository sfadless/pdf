<?php

declare(strict_types=1);

namespace Sfadless\Pdf\Event;

use Sfadless\Pdf\Model\PdfWritable;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * @author Pavel Golikov <pgolikov327@gmail.com>
 */
final class PreRenderPdfEvent extends Event
{
    private array $parameters;

    public function __construct(private readonly PdfWritable $pdfWritable)
    {
        $this->parameters = $pdfWritable->getPdfParameters();
    }

    public function getParameters() : array
    {
        return $this->parameters;
    }

    public function setParameters(array $parameters) : self
    {
        $this->parameters = $parameters;

        return $this;
    }

    public function addParameter(string $parameter, $value) : self
    {
        $this->parameters[$parameter] = $value;

        return $this;
    }

    public function getPdfWritable() : PdfWritable
    {
        return $this->pdfWritable;
    }
}