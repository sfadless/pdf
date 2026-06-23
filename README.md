# sfadless/pdf

Generate PDF files from Twig templates on top of [mPDF](https://github.com/mpdf/mpdf).

The library renders a Twig template to HTML and converts it to PDF. An event is
dispatched before rendering so you can enrich the template context. The PDF engine sits
behind a `PdfRenderer` interface, so mPDF can be swapped for another implementation.
At runtime the library does not depend on Symfony — the event is dispatched through the
standard [PSR-14](https://www.php-fig.org/psr/psr-14/) interface.

## Requirements

- PHP >= 8.1
- twig/twig ^3.8
- psr/event-dispatcher ^1.0 (any PSR-14 dispatcher implementation)
- mpdf/mpdf ^8.2 (for the default renderer)

## Installation

```bash
composer require sfadless/pdf
```

## Quick start

```php
use Sfadless\Pdf\PdfFactory;
use Sfadless\Pdf\Model\PdfModel;
use Sfadless\Pdf\Renderer\Mpdf\MpdfRenderer;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

$twig = new Environment(new FilesystemLoader(__DIR__ . '/templates'));

$factory = new PdfFactory(
    new MpdfRenderer(),
    $twig,
    new EventDispatcher(), // any PSR-14 dispatcher
);

// By default the raw PDF bytes are returned
$pdf = $factory->writePdf(new PdfModel('invoice.html.twig', [
    'number' => 'INV-001',
    'total'  => 1200,
]));

file_put_contents('invoice.pdf', $pdf);
```

`PdfModel` takes three arguments:

```php
new PdfModel(
    string          $pdfTemplate,   // Twig template name
    array           $pdfParameters, // template variables
    RendererOptions $pdfOptions,    // output options (see below)
);
```

## Options

Output options are a `Sfadless\Pdf\Renderer\RendererOptions` value object passed as the third
argument of `PdfModel`. Every property has a default, so you only set what you need:

| Property | Meaning | Type | Default |
|---|---|---|---|
| `fileName` | file name | `string` | `document.pdf` |
| `destination` | output mode | `OutputDestination` | `OutputDestination::STRING` |
| `footer` | footer HTML | `?string` | `null` |
| `pdfBefore` | path to a PDF prepended to the document | `?string` | `null` |
| `pdfAfter` | paths to PDFs appended to the document | `string[]` | `[]` |

```php
use Sfadless\Pdf\Model\PdfModel;
use Sfadless\Pdf\Renderer\RendererOptions;

$pdf = $factory->writePdf(new PdfModel('report.html.twig', ['title' => 'Report'], new RendererOptions(
    fileName:  'report.pdf',
    footer:    '<div style="text-align:center">{PAGENO}</div>',
    pdfBefore: '/path/to/cover.pdf',
    pdfAfter:  ['/path/to/appendix-1.pdf', '/path/to/appendix-2.pdf'],
)));
```

> A missing path in `pdfBefore`/`pdfAfter` results in a `RuntimeException`.

## Output modes

`Sfadless\Pdf\Renderer\OutputDestination` controls what `writePdf` does:

| Value | Behaviour |
|---|---|
| `OutputDestination::STRING` | return the PDF as a string (default) |
| `OutputDestination::FILE` | save to the `fileName` path on disk |
| `OutputDestination::DOWNLOAD` | send to the browser as a download |
| `OutputDestination::INLINE` | display in the browser inline |

```php
use Sfadless\Pdf\Model\PdfModel;
use Sfadless\Pdf\Renderer\OutputDestination;
use Sfadless\Pdf\Renderer\RendererOptions;

$factory->writePdf(new PdfModel('invoice.html.twig', $data, new RendererOptions(
    fileName:    'invoice.pdf',
    destination: OutputDestination::DOWNLOAD,
)));
```

## Enriching the context via the event

`Sfadless\Pdf\Event\PreRenderPdfEvent` is dispatched before rendering. A listener can add
or replace template variables — handy for shared data (current user, date, settings) you
don't want to pass into every `PdfModel`.

```php
use Sfadless\Pdf\Event\PreRenderPdfEvent;
use Symfony\Component\EventDispatcher\EventDispatcher;

$dispatcher = new EventDispatcher();
$dispatcher->addListener(PreRenderPdfEvent::class, function (PreRenderPdfEvent $event): void {
    $event->addParameter('generatedAt', new DateTimeImmutable());

    // the original model is available for conditional logic
    $writable = $event->getPdfWritable();
});

$factory = new PdfFactory(new MpdfRenderer(), $twig, $dispatcher);
```

In the template the variable is available as usual: `{{ generatedAt|date('d.m.Y') }}`.

## Configuring mPDF

mPDF configuration is passed to the `MpdfRenderer` constructor and proxied to `new Mpdf(...)`:

```php
use Sfadless\Pdf\Renderer\Mpdf\MpdfRenderer;

$renderer = new MpdfRenderer([
    'format'        => 'A4',
    'margin_top'    => 20,
    'margin_bottom' => 20,
    'default_font'  => 'dejavusans',
]);
```

See the full list of parameters in the [mPDF documentation](https://mpdf.github.io/reference/mpdf-functions/construct.html).

## Custom renderer

`PdfFactory` depends only on the `Sfadless\Pdf\Renderer\PdfRenderer` interface, so mPDF can
be replaced — for example with Dompdf, wkhtmltopdf or Gotenberg:

```php
use Sfadless\Pdf\Renderer\PdfRenderer;
use Sfadless\Pdf\Renderer\RendererOptions;

final class DompdfRenderer implements PdfRenderer
{
    public function render(string $html, RendererOptions $options): string
    {
        // available: $options->fileName, $options->footer, $options->destination,
        //            $options->pdfBefore, $options->pdfAfter
        // ...
        return $pdfBytes;
    }
}

$factory = new PdfFactory(new DompdfRenderer(), $twig, $dispatcher);
```

## Custom document model

Instead of `PdfModel` you can implement the `Sfadless\Pdf\Model\PdfWritable` interface —
handy when the document is a domain entity:

```php
use Sfadless\Pdf\Model\PdfWritable;
use Sfadless\Pdf\Renderer\RendererOptions;

final class Invoice implements PdfWritable
{
    public function __construct(private string $number, private float $total) {}

    public function getPdfTemplate(): string
    {
        return 'invoice.html.twig';
    }

    public function getPdfParameters(): array
    {
        return ['number' => $this->number, 'total' => $this->total];
    }

    public function getPdfOptions(): RendererOptions
    {
        return new RendererOptions(fileName: "invoice-{$this->number}.pdf");
    }
}

$pdf = $factory->writePdf(new Invoice('INV-001', 1200));
```

## Tests

```bash
composer test
```