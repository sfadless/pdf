# Changelog

All notable changes to this project are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [0.1.0] - 2026-06-23

Decouples the library from a concrete PDF engine and from Symfony. The PDF engine
now sits behind a `PdfRenderer` interface, output options are a typed value object,
and events are dispatched through PSR-14 — so at runtime the library no longer
depends on Symfony.

### Added
- `PdfRenderer` interface and `MpdfRenderer` adapter — the engine is swappable
  (Dompdf, Gotenberg, …); mPDF config is passed to the renderer's constructor.
- `RendererOptions` value object and `OutputDestination` enum (`STRING`, `FILE`,
  `DOWNLOAD`, `INLINE`) for typed output options.
- PSR-14 event dispatching — `PreRenderPdfEvent` is a plain object and `PdfFactory`
  depends on `Psr\EventDispatcher\EventDispatcherInterface`; any PSR-14 dispatcher works.
- `pageNumbering` support for the mPDF renderer (centered `{PAGENO} / {nbpg}` footer;
  an explicit `footer` takes precedence).
- PHPUnit test suite, README, php-cs-fixer config, and `composer test` / `composer cs` scripts.

### Changed
- **Breaking:** `PdfFactory` now takes a `PdfRenderer` instead of an `Mpdf`, and a
  PSR-14 dispatcher instead of Symfony's.
- **Breaking:** options are typed — `PdfModel`'s third argument and
  `PdfWritable::getPdfOptions()` are now `RendererOptions`, not an array.
- Dependencies: removed `symfony/options-resolver`; `symfony/event-dispatcher` moved
  to dev-only; added `psr/event-dispatcher`.

### Removed
- **Breaking:** the `PdfOptions` string-key class.

### Fixed
- Output options were silently ignored (`OptionsResolver::resolve()` was called with
  no argument) — options now reach the renderer.
- The footer was applied after `WriteHTML` and never rendered — now applied before,
  and the check is null-safe.
- Prepended/appended PDFs were forced to A4 — each sheet is now sized to the source
  page (`getTemplateSize()` / `AddPageByArray()`), preserving format and orientation.

## [0.0.1]

Initial release.

[0.1.0]: https://github.com/sfadless/pdf/releases/tag/0.1.0
[0.0.1]: https://github.com/sfadless/pdf/releases/tag/0.0.1