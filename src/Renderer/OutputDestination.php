<?php

declare(strict_types=1);

namespace Sfadless\Pdf\Renderer;

enum OutputDestination
{
    case FILE;
    case DOWNLOAD;
    case STRING;
    case INLINE;
}
