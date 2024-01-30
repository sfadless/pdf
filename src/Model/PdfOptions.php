<?php

declare(strict_types=1);

namespace Sfadless\Pdf\Model;

/**
 * @author Pavel Golikov <pgolikov327@gmail.com>
 */
final class PdfOptions
{
    public const FILE_NAME = 'fileName';

    public const DESTINATION = 'destination';

    public const FOOTER = 'footer';

    /**
     * Добавить PDF в начало
     */
    public const PDF_BEFORE = 'pdfBefore';

    /**
     * Добавить PDF в конец
     */
    public const PDF_AFTER = 'pdfAfter';

    public const PAGE_NUMBERING = 'pageNumbering';
}