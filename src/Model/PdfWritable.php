<?php

declare(strict_types=1);

namespace Sfadless\Pdf\Model;

/**
 * @author Pavel Golikov <pgolikov327@gmail.com>
 */
interface PdfWritable
{
    public function getPdfTemplate() : string;

    public function getPdfParameters() : array;

    public function getPdfOptions() : array;
}