<?php

namespace App\Modules\Reports\Services;

use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Shared\Html;
use PhpOffice\PhpWord\Style\Font;

class WordDocumentService
{
    public function convertDocxToHtml(string $filePath): string
    {
        $phpWord = IOFactory::load($filePath);
        $tempHtml = tempnam(sys_get_temp_dir(), 'sk_doc_') . '.html';
        $writer = IOFactory::createWriter($phpWord, 'HTML');
        $writer->save($tempHtml);
        $html = file_get_contents($tempHtml) ?: '';
        @unlink($tempHtml);

        return $this->sanitizeExtractedHtml($html);
    }

    public function generateDocxFromHtml(string $title, string $html, string $outputPath): void
    {
        $phpWord = new PhpWord;
        $phpWord->getDocInfo()->setTitle($title);
        $section = $phpWord->addSection();

        $heading = $section->addText($title, ['bold' => true, 'size' => 16]);
        $section->addTextBreak(1);

        $body = trim($html);
        if ($body !== '') {
            try {
                Html::addHtml($section, $body, false, false);
            } catch (\Throwable) {
                $section->addText(strip_tags($body));
            }
        }

        $writer = IOFactory::createWriter($phpWord, 'Word2007');
        $writer->save($outputPath);
    }

    /**
     * @param  array<int, array<string, string>>  $tableRows
     */
    public function generateReportDocx(
        string $title,
        string $bodyHtml,
        string $outputPath,
        array $tableRows = [],
        ?string $subtitle = null
    ): void {
        $phpWord = new PhpWord;
        $phpWord->getDocInfo()->setTitle($title);
        $section = $phpWord->addSection();

        $section->addText($title, ['bold' => true, 'size' => 18, 'name' => 'Calibri']);
        if ($subtitle) {
            $section->addText($subtitle, ['size' => 11, 'italic' => true, 'name' => 'Calibri']);
        }
        $section->addTextBreak(1);

        $body = trim($bodyHtml);
        if ($body !== '') {
            try {
                Html::addHtml($section, $body, false, false);
            } catch (\Throwable) {
                $section->addText(strip_tags($body), ['name' => 'Calibri', 'size' => 11]);
            }
        }

        if ($tableRows !== []) {
            $section->addTextBreak(1);
            $table = $section->addTable(['borderSize' => 6, 'borderColor' => '999999', 'cellMargin' => 80]);
            $headers = array_keys($tableRows[0]);
            $table->addRow();
            foreach ($headers as $header) {
                $table->addCell(2000)->addText((string) $header, ['bold' => true, 'size' => 10]);
            }
            foreach ($tableRows as $row) {
                $table->addRow();
                foreach ($headers as $header) {
                    $table->addCell(2000)->addText((string) ($row[$header] ?? ''), ['size' => 10]);
                }
            }
        }

        IOFactory::createWriter($phpWord, 'Word2007')->save($outputPath);
    }

    private function sanitizeExtractedHtml(string $html): string
    {
        $html = preg_replace('/<script\b[^>]*>.*?<\/script>/is', '', $html) ?? $html;
        $html = preg_replace('/<style\b[^>]*>.*?<\/style>/is', '', $html) ?? $html;

        if (preg_match('/<body[^>]*>(.*)<\/body>/is', $html, $m)) {
            return trim($m[1]);
        }

        return trim($html);
    }
}
