<?php

declare(strict_types=1);

namespace Sfadless\Pdf;

use Sfadless\Pdf\Event\PreRenderPdfEvent;
use Sfadless\Pdf\Model\PdfOptions;
use Sfadless\Pdf\Model\PdfWritable;
use Mpdf\{Mpdf, Output\Destination};
use RuntimeException;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Twig\Environment;

/**
 * @author Pavel Golikov <pgolikov327@gmail.com>
 */
final class PdfFactory
{
    public function __construct(
        private readonly Mpdf $mpdf,
        private readonly Environment $twig,
        private readonly EventDispatcherInterface $dispatcher
    ) {}

    public function writePdf(PdfWritable $pdfWritable): string
    {
        $event = new PreRenderPdfEvent($pdfWritable);

        $this->dispatcher->dispatch($event);

        $context = $event->getParameters();

        $html = $this->twig->render(
            $pdfWritable->getPdfTemplate(),
            $context
        );

        $options = $this->configureOptions($pdfWritable->getPdfOptions());

        if (isset($options[PdfOptions::PDF_BEFORE])) {
            $this->addFromSource($options[PdfOptions::PDF_BEFORE]);
            $this->mpdf->AddPage();
        }

        $this->mpdf->WriteHTML($html);

        if (isset($options[PdfOptions::FOOTER])) {
            $this->mpdf->SetHTMLFooter($options[PdfOptions::FOOTER]);
        }

        if (isset($options[PdfOptions::PDF_AFTER])) {
            if (is_array($options[PdfOptions::PDF_AFTER])) {
                foreach ($options[PdfOptions::PDF_AFTER] as $option) {
                    $this->addFromSource($option);
                }
            } else {
                $this->addFromSource($options[PdfOptions::PDF_AFTER]);
            }
        }

        return $this->mpdf->Output($options[PdfOptions::FILE_NAME], $options[PdfOptions::DESTINATION]);
    }

    private function configureOptions(array $options) : array
    {
        $resolver = new OptionsResolver();

        $resolver
            ->setDefault(PdfOptions::FILE_NAME, 'file.pdf')
            ->setDefault(PdfOptions::DESTINATION, Destination::STRING_RETURN)
            ->setDefined(PdfOptions::FOOTER)
            ->setDefined(PdfOptions::PDF_BEFORE)
            ->setDefined(PdfOptions::PDF_AFTER)
        ;

        return $resolver->resolve($options);
    }

    private function addFromSource(string $filepath) : void
    {
        if (! file_exists($filepath)) {
            throw new RuntimeException("Не удалось добавить pdf - файла $filepath не существует");
        }

        $pageCount = $this->mpdf->setSourceFile($filepath);

        for ($i = 1 ; $i <= $pageCount; $i++) {
            $tplId = $this->mpdf->ImportPage($i);
            $this->mpdf->AddPage(resetpagenum: 1, suppress: 'on');
            $this->mpdf->setFooter('');
            $this->mpdf->UseTemplate($tplId,0,0, 210, 300);
        }
    }
}