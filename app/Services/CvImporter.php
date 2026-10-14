<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpWord\IOFactory;
use Smalot\PdfParser\Parser;

class CvImporter
{
    public function extractFile(UploadedFile $file): string
    {
        $extension = strtolower($file->getClientOriginalExtension());

        try {
            if ($extension === 'pdf') {
                return $this->extractPdfText($file->getRealPath());
            }

            if (in_array($extension, ['doc', 'docx'], true)) {
                $reader = IOFactory::createReader($extension === 'doc' ? 'MsDoc' : 'Word2007');
                $document = $reader->load($file->getRealPath());
                $lines = [];
                foreach ($document->getSections() as $section) {
                    array_push($lines, ...$this->extractWordElements($section->getElements()));
                }

                return implode("\n", $lines);
            }

            return (string) $file->get();
        } catch (\Throwable $exception) {
            throw ValidationException::withMessages([
                'cv_file' => 'Impossible de lire ce fichier. Vérifiez qu’il n’est pas protégé et réessayez.',
            ]);
        }
    }

    private function extractPdfText(string $path): string
    {
        $document = (new Parser())->parseFile($path);
        $pageTexts = [];

        foreach ($document->getPages() as $page) {
            $streams = [];
            $seenForms = [];
            $nestedTexts = [];
            foreach ($page->getXObjects() as $object) {
                if (! $object instanceof \Smalot\PdfParser\XObject\Form) {
                    continue;
                }
                if (isset($seenForms[spl_object_id($object)])) continue;
                $seenForms[spl_object_id($object)] = true;

                $text = $this->positionedPdfText($object->getContent() ?? '', $object);
                $nestedTexts = array_merge($nestedTexts, $this->nestedPdfTexts($object, $seenForms));
                if (trim($text) !== '') {
                    $streams[] = $text;
                }
            }

            if ($streams === []) {
                $pageText = $page->getText();
            } else {
                $pageText = implode("\n", $streams);
            }

            $pageText = $this->restoreNestedPdfSections($pageText, $nestedTexts);
            if ($pageText !== '') {
                $pageTexts[] = $pageText;
            }
        }

        return $pageTexts !== [] ? implode("\n", $pageTexts) : $document->getText();
    }

    private function nestedPdfTexts(\Smalot\PdfParser\Page $page, array &$seenForms): array
    {
        $texts = [];
        foreach ($page->getXObjects() as $object) {
            if (! $object instanceof \Smalot\PdfParser\XObject\Form || isset($seenForms[spl_object_id($object)])) {
                continue;
            }
            $seenForms[spl_object_id($object)] = true;
            $text = trim($object->getText($page));
            if ($text !== '' && $text !== ' ') {
                $texts[] = $text;
            }
            array_push($texts, ...$this->nestedPdfTexts($object, $seenForms));
        }

        return $texts;
    }

    private function restoreNestedPdfSections(string $pageText, array $nestedTexts): string
    {
        $lines = preg_split('/\n+/', $pageText) ?: [];
        $skillLines = [];
        foreach ($nestedTexts as $nestedText) {
            $items = array_values(array_filter(array_map('trim', preg_split('/\n+/', $nestedText) ?: [])));
            if (count($items) >= 8 && count(array_filter($items, fn ($item) => mb_strlen($item, 'UTF-8') <= 100 && ! preg_match('/[.!?]$/u', $item))) >= count($items) * 0.8) {
                $skillLines = $items;
                break;
            }
        }

        if ($skillLines !== []) {
            $skillHeading = null;
            foreach ($lines as $index => $line) {
                if ($this->sectionKey(trim($line)) === 'skills') {
                    $skillHeading = $index;
                    break;
                }
            }
            array_splice($lines, $skillHeading === null ? count($lines) : $skillHeading + 1, 0, $skillHeading === null ? array_merge(['Compétences'], $skillLines) : $skillLines);
        }

        $referenceStart = null;
        $referenceEnd = count($lines);
        $referenceLines = [];
        foreach ($lines as $index => $line) {
            $section = $this->sectionKey(trim($line));
            if ($section === 'references') {
                $referenceStart = $index;
                continue;
            }
            if ($referenceStart !== null && $section !== null) {
                $referenceEnd = $index;
                break;
            }
            if ($referenceStart !== null) {
                $referenceLines[] = $line;
            }
        }

        if ($referenceStart !== null) {
            preg_match_all('/(?:\p{Lu}[\p{Ll}\p{M}’\'-]+\s+){1,2}\p{Lu}[\p{Lu}\p{M}’\'-]+/u', implode(' ', $referenceLines), $nameMatches);
            $names = array_values(array_unique(array_map('trim', $nameMatches[0] ?? [])));
            $roles = [];
            $phones = [];
            foreach ($nestedTexts as $nestedText) {
                if (preg_match('/(?:\+?\d[\d\s().-]{7,}\d)/', $nestedText, $phoneMatch)) {
                    $phones[] = trim($phoneMatch[0]);
                }
                if (preg_match('/^(.+?)\s+(?:à|at)\s+(.+)$/iu', trim($nestedText), $roleMatch)) {
                    $roles[] = [trim($roleMatch[1]), trim($roleMatch[2])];
                }
            }

            if (count($names) >= 2) {
                $referenceBlock = [trim($lines[$referenceStart])];
                foreach ($names as $index => $name) {
                    $role = $roles[$index] ?? [null, null];
                    $referenceBlock[] = implode(' | ', array_filter([$name, $role[0], $role[1], $phones[$index] ?? null], fn ($value) => $value !== null && $value !== ''));
                }
                array_splice($lines, $referenceStart, $referenceEnd - $referenceStart, $referenceBlock);
            }
        }

        return implode("\n", $lines);
    }

    private function positionedPdfText(string $content, \Smalot\PdfParser\Page $page): string
    {
        try {
            $source = new \Smalot\PdfParser\PDFObject(
                $page->getDocument(),
                new \Smalot\PdfParser\Header([], $page->getDocument()),
                $content,
                $page->getConfig(),
            );
            $sections = $source->getSectionsText($content);
            $decoder = new \ReflectionMethod($source, 'getTJUsingFontFallback');
            $decoder->setAccessible(true);
        } catch (\Throwable) {
            return '';
        }

        $textMatrix = [1, 0, 0, 1, 0, 0];
        $textOffset = [0, 0];
        $textLeading = 0;
        $fontSize = 1;
        $font = array_values($page->getFonts())[0] ?? $page->getDocument()->getFirstFont();
        $graphicsMatrix = [1, 0, 0, 1, 0, 0];
        $graphicsStack = [];
        $glyphs = [];

        foreach ($sections as $section) {
            foreach ($source->getCommandsText($section) as $command) {
                $operator = $command['o'] ?? '';
                $arguments = $command['c'] ?? '';

                if ($operator === 'q') {
                    $graphicsStack[] = $graphicsMatrix;
                } elseif ($operator === 'Q') {
                    $graphicsMatrix = array_pop($graphicsStack) ?? $graphicsMatrix;
                } elseif ($operator === 'cm') {
                    $matrix = $this->pdfNumbers($arguments);
                    if (count($matrix) >= 6) {
                        $graphicsMatrix = $this->multiplyPdfMatrices($graphicsMatrix, array_slice($matrix, 0, 6));
                    }
                } elseif ($operator === 'BT') {
                    $textMatrix = [1, 0, 0, 1, 0, 0];
                    $textOffset = [0, 0];
                } elseif ($operator === 'Tm') {
                    $matrix = $this->pdfNumbers($arguments);
                    if (count($matrix) >= 6) {
                        $textMatrix = array_slice($matrix, 0, 6);
                        $textOffset = [0, 0];
                    }
                } elseif ($operator === 'Tf') {
                    $parts = preg_split('/\s+/', trim((string) $arguments)) ?: [];
                    $fontSize = (float) array_pop($parts);
                    $font = $page->getFont(trim((string) array_pop($parts), '/')) ?? $font;
                } elseif ($operator === 'TL') {
                    $textLeading = (float) $arguments;
                } elseif (in_array($operator, ['Td', 'TD'], true)) {
                    $offset = $this->pdfNumbers($arguments);
                    if (count($offset) >= 2) {
                        $textOffset[0] += $offset[0] * $textMatrix[0] + $offset[1] * $textMatrix[2];
                        $textOffset[1] += $offset[0] * $textMatrix[1] + $offset[1] * $textMatrix[3];
                    }
                } elseif ($operator === 'T*' || $operator === "'" || $operator === '"') {
                    $textOffset[0] = 0;
                    $textOffset[1] += $textLeading;
                } elseif (in_array($operator, ['Tj', 'TJ', "'", '"'], true)) {
                    if (! $font) continue;

                    $fontFactor = -$fontSize * $textMatrix[0] - $fontSize * $textMatrix[2];
                    if (in_array($operator, ['Tj', "'", '"'], true)) {
                        $arguments = [$command];
                    }
                    $text = $decoder->invoke($source, $font, $arguments, $page, $fontFactor);
                    if (! is_string($text) || $text === '') continue;

                    $textX = $textMatrix[4] + $textOffset[0];
                    $textY = $textMatrix[5] + $textOffset[1];
                    $glyphs[] = [
                        'x' => $graphicsMatrix[0] * $textX + $graphicsMatrix[2] * $textY + $graphicsMatrix[4],
                        'y' => $graphicsMatrix[1] * $textX + $graphicsMatrix[3] * $textY + $graphicsMatrix[5],
                        'text' => $text,
                    ];
                }
            }
        }

        if ($glyphs === []) {
            return '';
        }

        $lines = $this->groupPdfGlyphs($glyphs);
        $boundary = $this->pdfColumnBoundary($glyphs);
        $officialText = $page->getText();
        $ascending = $this->pdfColumns($lines, $boundary, false, $officialText);
        $descending = $this->pdfColumns($lines, $boundary, true, $officialText);
        $candidates = [
            [$ascending[1], $ascending[0]],
            [$ascending[0], $ascending[1]],
            [$descending[1], $descending[0]],
            [$descending[0], $descending[1]],
        ];

        usort($candidates, fn ($left, $right) => $this->pdfHeaderScore($right[0]) <=> $this->pdfHeaderScore($left[0]));
        $best = $candidates[0];

        return implode("\n", array_merge($best[0], $best[1]));
    }

    private function pdfNumbers(string $content): array
    {
        preg_match_all('/-?(?:\d+\.?\d*|\.\d+)(?:[eE][+-]?\d+)?/', $content, $matches);

        return array_map('floatval', $matches[0]);
    }

    private function multiplyPdfMatrices(array $left, array $right): array
    {
        return [
            $left[0] * $right[0] + $left[2] * $right[1],
            $left[1] * $right[0] + $left[3] * $right[1],
            $left[0] * $right[2] + $left[2] * $right[3],
            $left[1] * $right[2] + $left[3] * $right[3],
            $left[0] * $right[4] + $left[2] * $right[5] + $left[4],
            $left[1] * $right[4] + $left[3] * $right[5] + $left[5],
        ];
    }

    private function pdfCommandText(mixed $content): string
    {
        if (is_string($content)) {
            return $content;
        }

        if (! is_array($content)) {
            return '';
        }

        $text = '';
        foreach ($content as $part) {
            if (is_string($part)) {
                $text .= $part;
            } elseif (is_array($part) && is_string($part['c'] ?? null)) {
                $text .= $part['c'];
            }
        }

        return $text;
    }

    private function groupPdfGlyphs(array $glyphs): array
    {
        usort($glyphs, fn ($left, $right) => $left['y'] <=> $right['y'] ?: $left['x'] <=> $right['x']);
        $lines = [];

        foreach ($glyphs as $glyph) {
            $lineIndex = array_key_last($lines);
            if ($lineIndex === null || abs($lines[$lineIndex]['y'] - $glyph['y']) > 2.5) {
                $lines[] = ['y' => $glyph['y'], 'glyphs' => [$glyph]];
                continue;
            }

            $lines[$lineIndex]['glyphs'][] = $glyph;
            $lines[$lineIndex]['y'] = ($lines[$lineIndex]['y'] + $glyph['y']) / 2;
        }

        return $lines;
    }

    private function pdfColumnBoundary(array $glyphs): ?float
    {
        $positions = array_column($glyphs, 'x');
        sort($positions, SORT_NUMERIC);
        if (count($positions) < 20) {
            return null;
        }

        $largestGap = 0;
        $boundary = null;
        $minimumSideCount = max(8, (int) floor(count($positions) * 0.08));
        for ($index = $minimumSideCount; $index < count($positions) - $minimumSideCount; $index++) {
            $gap = $positions[$index] - $positions[$index - 1];
            if ($gap > $largestGap) {
                $largestGap = $gap;
                $boundary = ($positions[$index] + $positions[$index - 1]) / 2;
            }
        }

        return $largestGap > 20 ? $boundary : null;
    }

    private function pdfColumns(array $lines, ?float $boundary, bool $reverse, string $officialText): array
    {
        $columns = [[], []];
        if ($reverse) {
            $lines = array_reverse($lines);
        }

        preg_match_all('/\S/u', $officialText, $officialCharacters, PREG_OFFSET_CAPTURE);
        $officialCompact = implode('', array_column($officialCharacters[0], 0));
        $officialOffsets = array_column($officialCharacters[0], 1);

        foreach ($lines as $line) {
            $columnGlyphs = [[], []];
            foreach ($line['glyphs'] as $glyph) {
                $column = $boundary !== null && $glyph['x'] >= $boundary ? 1 : 0;
                $columnGlyphs[$column][] = $glyph;
            }

            foreach ($columnGlyphs as $column => $glyphs) {
                if ($glyphs === []) continue;
                usort($glyphs, fn ($left, $right) => $left['x'] <=> $right['x']);
                $compact = preg_replace('/\s+/u', '', implode('', array_column($glyphs, 'text'))) ?? '';
                $position = $compact !== '' ? mb_strpos($officialCompact, $compact, 0, 'UTF-8') : false;
                if ($position === false) {
                    $text = implode('', array_column($glyphs, 'text'));
                } else {
                    $start = $officialOffsets[$position];
                    $last = $officialOffsets[$position + mb_strlen($compact, 'UTF-8') - 1];
                    $lastCharacter = mb_substr($compact, -1, 1, 'UTF-8');
                    $text = trim(preg_replace('/\s+/u', ' ', substr($officialText, $start, $last + strlen($lastCharacter) - $start)) ?? '');
                }
                if (trim($text) !== '') $columns[$column][] = trim($text);
            }
        }

        return $columns;
    }

    private function pdfHeaderScore(array $lines): int
    {
        $lines = array_values(array_filter($lines, fn ($line) => trim($line) !== ''));
        $first = trim($lines[0] ?? '');
        $second = trim($lines[1] ?? '');
        $score = 0;

        if ($first !== '' && preg_match('/\A\p{Lu}[\p{L}\p{M}\'’-]*(?:\s+\p{Lu}[\p{L}\p{M}\'’-]*){0,2}\z/u', $first)) {
            $score += 3;
        }
        if ($second !== '' && preg_match('/\A\p{Lu}[\p{L}\p{M}\'’-]*(?:\s+\p{Lu}[\p{L}\p{M}\'’-]*){0,2}\z/u', $second)) {
            $score += 3;
            if (mb_strtoupper($second, 'UTF-8') === $second) $score += 2;
        }
        if (isset($lines[2]) && $this->sectionKey($lines[0]) === null && ! $this->looksLikeMetadata($lines[2])) {
            $score++;
        }

        return $score;
    }

    public function parse(string $raw): array
    {
        $text = $this->normalizeColumns(preg_replace('/\r\n?/', "\n", $raw) ?? $raw);
        $lines = array_values(array_filter(array_map('trim', preg_split('/\n+/', $text) ?: []), fn ($line) => $line !== ''));

        $result = [
            'first_name' => null,
            'last_name' => null,
            'title' => null,
            'email' => null,
            'phone' => null,
            'website' => null,
            'location' => null,
            'summary' => null,
            'experiences' => [],
            'educations' => [],
            'skills' => [],
            'projects' => [],
            'achievements' => [],
            'certifications' => [],
            'languages' => [],
            'technologies' => [],
            'references' => [],
            'social_links' => [],
        ];

        $firstLine = $lines[0] ?? '';
        $nameParts = [];
        if ($firstLine !== '') {
            $nameParts = preg_split('/\s+/', $firstLine, 3);
            if (count($nameParts) >= 2) {
                $result['first_name'] = $nameParts[0];
                $result['last_name'] = $nameParts[1];
            }
            if (count($nameParts) >= 3 && stripos($nameParts[2], 'http') === false) {
                $result['title'] = $nameParts[2];
            }
        }

        $email = $this->extractFirstMatch($lines, '/[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}/i');
        if ($email) {
            $result['email'] = $email;
        }

        $phonePattern = '/(?:\+\s?\d[\d\s().\/-]{7,}\d|\b\d(?:[\s().\/-]*\d){8,}\b)/';
        $phone = null;
        foreach ($lines as $line) {
            if (str_contains($line, '/') && preg_match($phonePattern, $line, $phoneMatch)) {
                $phone = trim($phoneMatch[0]);
                break;
            }
        }
        $phone ??= $this->extractFirstMatch($lines, $phonePattern);
        if ($phone) {
            $result['phone'] = preg_replace('/\s+/', ' ', trim($phone));
        }

        $result['social_links'] = $this->parseSocialLinks($lines);
        foreach ($lines as $line) {
            if (preg_match('/https?:\/\/[^\s]+/i', $line, $matches)
                && ! preg_match('/(?:github\.com|linkedin\.com|facebook\.com)/i', $matches[0])) {
                $result['website'] = rtrim($matches[0], '.,;)');
                break;
            }
        }

        $nameLineIndex = count($nameParts ?? []) >= 2 ? 0 : 0;
        if (count($nameParts ?? []) === 1 && isset($lines[1]) && ! $this->looksLikeMetadata($lines[1]) && count(preg_split('/\s+/', $lines[1]) ?: []) <= 3) {
            $result['first_name'] = $firstLine;
            $result['last_name'] = $lines[1];
            $nameLineIndex = 1;
        }
        $titleLine = null;
        foreach (array_slice($lines, $nameLineIndex + 1) as $line) {
            if ($this->sectionKey($line) !== null) break;
            if (! $this->looksLikeMetadata($line)) {
                $titleLine = $line;
                break;
            }
        }
        if ($titleLine && ! $this->looksLikeMetadata($titleLine)) {
            $result['title'] = $titleLine;
        }
        $sectionStart = count($lines);
        foreach ($lines as $index => $line) {
            if ($this->sectionKey($line) !== null) {
                $sectionStart = $index;
                break;
            }
        }
        foreach (array_slice($lines, 1, max(0, $sectionStart - 1)) as $line) {
            if ($line === $titleLine || $this->looksLikeMetadata($line)) continue;
            if (in_array($line, [$result['first_name'], $result['last_name']], true)) continue;
            if (preg_match('/^[+\d]/', $line)) continue;
            $result['location'] = $line;
        }
        if ($result['location'] === null && $result['phone'] !== null) {
            foreach ($lines as $index => $line) {
                if (! str_contains($line, $result['phone'])) continue;
                foreach (array_slice($lines, $index + 1) as $candidate) {
                    if ($this->sectionKey($candidate) !== null && $this->sectionKey($candidate) !== 'summary') break;
                        if (preg_match('/(?:mailto:|https?:\/\/|[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,})/i', $candidate)
                            || preg_match($phonePattern, $candidate)
                            || in_array($candidate, [$result['first_name'], $result['last_name'], $titleLine], true)) continue;
                    $result['location'] = $candidate;
                    break 2;
                }
            }
        }
        if ($result['location'] === null) {
            $contactFound = false;
            foreach ($lines as $line) {
                if (preg_match('/[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}/i', $line) || preg_match($phonePattern, $line)) {
                    $contactFound = true;
                    continue;
                }
                if (! $contactFound || $this->sectionKey($line) !== null) {
                    continue;
                }
                if (! in_array($line, [$result['first_name'], $result['last_name'], $titleLine], true)
                    && ! preg_match('/(?:mailto:|https?:\/\/|(?:github|linkedin|facebook)\.com\/)/i', $line)) {
                    $result['location'] = $line;
                    break;
                }
            }
        }
        if ($result['location'] === null) {
            $contactFound = false;
            foreach ($lines as $line) {
                if ($this->sectionKey($line) !== null) {
                    if ($contactFound) break;
                    continue;
                }
                if (preg_match('/[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}|(?:\+?\d[\d\s().\/-]{7,}\d)/i', $line)) {
                    $contactFound = true;
                    continue;
                }
                if ($contactFound
                    && ! preg_match('/(?:mailto:|https?:\/\/|[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}|(?:\+?\d[\d\s().\/-]{7,}\d))/i', $line)
                    && ! in_array($line, [$result['first_name'], $result['last_name'], $titleLine], true)) {
                    $result['location'] = $line;
                    break;
                }
            }
        }

        $blocks = $this->extractBlocks($lines);

        foreach ($blocks as $blockName => $items) {
            if ($blockName === 'experiences') {
                $result['experiences'] = $this->parseExperiences($items);
            }

            if ($blockName === 'educations') {
                $result['educations'] = $this->parseEducations($items);
            }

            if ($blockName === 'skills') {
                $result['skills'] = $this->parseSkills($items);
            }

            if ($blockName === 'projects') {
                $result['projects'] = $this->parseProjects($items);
            }

            if ($blockName === 'achievements') {
                $result['achievements'] = $this->parseProjects($items);
            }

            if ($blockName === 'certifications') {
                $result['certifications'] = $this->parseCertifications($items);
            }

            if ($blockName === 'languages') {
                $result['languages'] = $this->parseLanguages($items);
            }

            if ($blockName === 'technologies') {
                $result['technologies'] = $this->parseSkills($items);
            }

            if ($blockName === 'references') {
                $result['references'] = $this->parseReferences($items);
            }

            if ($blockName === 'social') {
                $result['social_links'] = $this->parseSocialLinks($items);
            }

            if ($blockName === 'summary') {
                $summaryLines = array_filter($items, fn ($item) => ! $this->looksLikeMetadata($item));
                $result['summary'] = Str::limit(trim(implode(' ', $summaryLines)), 500);
            }
        }

        $result['summary'] ??= $this->summarizeText($lines);
        $result['social_links'] = collect(array_merge($result['social_links'], $this->parseSocialLinks($lines)))
            ->unique(fn ($link) => ($link['platform'] ?? '').'|'.($link['url'] ?? ''))
            ->values()
            ->all();

        return $result;
    }

    private function normalizeColumns(string $text): string
    {
        $lines = preg_split('/\n+/', $text) ?: [];
        $rightStarts = [];

        foreach ($lines as $line) {
            if (preg_match('/\s{2,}(?=\S)/u', $line, $match, PREG_OFFSET_CAPTURE)) {
                $rightStarts[] = $match[0][1] + strlen($match[0][0]);
            }
        }

        if (count($rightStarts) < 4) return $text;

        $frequencies = array_count_values($rightStarts);
        arsort($frequencies);
        $boundary = (int) array_key_first($frequencies);
        if (($frequencies[$boundary] ?? 0) < 4) return $text;

        $leftColumn = [];
        $rightColumn = [];
        foreach ($lines as $line) {
            $indent = strspn($line, " \t");
            if ($indent >= $boundary - 2) {
                $rightColumn[] = trim($line);
                continue;
            }

            $gapStart = null;
            if (preg_match('/\s{2,}(?=\S)/u', $line, $match, PREG_OFFSET_CAPTURE)) {
                $candidate = $match[0][1] + strlen($match[0][0]);
                if (abs($candidate - $boundary) <= 2) {
                    $gapStart = $match[0][1];
                }
            }

            if ($gapStart === null) {
                $leftColumn[] = trim($line);
            } else {
                $leftColumn[] = trim(substr($line, 0, $gapStart));
                $rightColumn[] = trim(substr($line, $candidate));
            }
        }

        $header = [];
        $body = [];
        $inBody = false;
        foreach ($rightColumn as $line) {
            if (! $inBody && $this->sectionKey($line) !== null) {
                $inBody = true;
            }
            if ($inBody) {
                $body[] = $line;
            } else {
                $header[] = $line;
            }
        }

        return implode("\n", array_merge($header, $leftColumn, $body));
    }

    private function extractWordElements(array $elements): array
    {
        $lines = [];
        foreach ($elements as $element) {
            if (method_exists($element, 'getRows')) {
                foreach ($element->getRows() as $row) {
                    foreach ($row->getCells() as $cell) {
                        array_push($lines, ...$this->extractWordElements($cell->getElements()));
                    }
                }
            } elseif (method_exists($element, 'getElements')) {
                array_push($lines, ...$this->extractWordElements($element->getElements()));
            } elseif (method_exists($element, 'getText')) {
                $text = $element->getText();
                if (is_string($text) && trim($text) !== '') {
                    $lines[] = trim($text);
                }
            }
        }

        return $lines;
    }

    private function extractFirstMatch(array $lines, string $pattern): ?string
    {
        foreach ($lines as $line) {
            if (preg_match($pattern, $line, $matches)) {
                return trim($matches[0]);
            }
        }

        return null;
    }

    private function findLineAfter(array $lines, string $reference, callable $predicate): ?string
    {
        $started = false;

        foreach ($lines as $line) {
            if ($line === $reference) {
                $started = true;
                continue;
            }

            if ($started && $predicate($line)) {
                return $line;
            }
        }

        return null;
    }

    private function looksLikeMetadata(string $line): bool
    {
        return $this->sectionKey($line) !== null
            || preg_match('/(?:mailto:|https?:\/\/|(?:github|linkedin|facebook)\.com\/|[\w.+-]+@[\w.-]+\.[A-Z]{2,}|\+?\d)/i', $line) === 1;
    }

    private function extractBlocks(array $lines): array
    {
        $blocks = [];
        $current = null;

        foreach ($lines as $line) {
            $heading = $this->sectionKey($line);
            if ($heading !== null) {
                $current = $heading;
                $blocks[$current] = [];
                continue;
            }

            if ($current !== null) {
                $blocks[$current][] = $line;
            }
        }

        return $blocks;
    }

    private function sectionKey(string $line): ?string
    {
        $heading = preg_replace('/\s+/', ' ', trim(Str::ascii(mb_strtolower($line, 'UTF-8')), " \t:-–—")) ?? '';

        return match ($heading) {
            'experience', 'experiences', 'professional experience', 'professional experiences', 'experience professionnelle', 'experiences professionnelles' => 'experiences',
            'formation', 'formations', 'education', 'educations', 'academic background', 'parcours academique' => 'educations',
            'competence', 'competences', 'skills', 'technical skills', 'competences techniques' => 'skills',
            'certification', 'certifications', 'certificates', 'certificats' => 'certifications',
            'langue', 'langues', 'language', 'languages' => 'languages',
            'reference', 'references', 'references professionnelles', 'professional references' => 'references',
            'technologie', 'technologies', 'tools', 'technical tools' => 'technologies',
            'social', 'social links', 'social networks', 'social media', 'reseaux sociaux' => 'social',
            'achievement', 'achievements', 'accomplishments', 'realisation', 'realisations' => 'achievements',
            'project', 'projects', 'projet', 'projets' => 'projects',
            'about', 'a propos', 'profile', 'profil', 'summary', 'resume' => 'summary',
            default => null,
        };
    }

    private function parseCertifications(array $items): array
    {
        $certifications = [];
        foreach ($items as $item) {
            $item = trim(preg_replace('/^[-•*]\s*/u', '', $item) ?? $item);
            if ($item === '') {
                continue;
            }

            $parts = array_values(array_filter(array_map('trim', preg_split('/\s*\|\s*/', $item) ?: [])));
            $date = $this->extractYear($item);
            $certifications[] = [
                'title' => $parts[0] ?? $item,
                'issuer' => $parts[1] ?? null,
                'issue_date' => $date ? $date.'-01-01' : null,
                'description' => null,
            ];
        }

        return $certifications;
    }

    private function parseLanguages(array $items): array
    {
        $languages = [];
        foreach ($items as $item) {
            $item = trim(preg_replace('/^[-•*]\s*/u', '', $item) ?? $item);
            if ($item === '') {
                continue;
            }
            $parts = array_map('trim', preg_split('/\s*(?:\||:|–|—| - )\s*/u', $item, 2) ?: [$item]);
            $name = $parts[0] ?? $item;
            $languages[] = [
                'name' => $name,
                'locale' => $this->languageLocale($name),
                'level' => $parts[1] ?? null,
            ];
        }

        return $languages;
    }

    private function parseReferences(array $items): array
    {
        $columns = [];
        foreach ($items as $item) {
            $cells = array_values(array_filter(array_map('trim', preg_split('/\s{2,}/u', trim($item)) ?: [])));
            if (count($cells) > 1) $columns[] = $cells;
        }
        if (count($columns) >= 2) {
            $references = [];
            $count = max(array_map('count', $columns));
            for ($column = 0; $column < $count; $column++) {
                $name = trim((string) ($columns[0][$column] ?? ''));
                if ($name === '') continue;
                $reference = ['name' => $name, 'role' => null, 'company' => null, 'phone' => null, 'email' => null];
                foreach (array_slice($columns, 1) as $row) {
                    $value = trim((string) ($row[$column] ?? ''));
                    if (preg_match('/(?:tel|t[eé]l|phone)\s*[:.]?\s*(\+?[\d\s().-]{7,})/iu', $value, $phoneMatch)) {
                        $reference['phone'] = trim($phoneMatch[1]);
                    } elseif (preg_match('/[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}/i', $value, $emailMatch)) {
                        $reference['email'] = $emailMatch[0];
                    } elseif (preg_match('/^(.+?)\s+(?:à|at)\s+(.+)$/iu', $value, $roleMatch)) {
                        $reference['role'] = trim($roleMatch[1]);
                        $reference['company'] = trim($roleMatch[2]);
                    } elseif ($value !== '') {
                        $reference['role'] ??= $value;
                    }
                }
                $references[] = $reference;
            }

            return $references;
        }

        $references = [];
        $current = null;
        foreach ($items as $item) {
            $item = trim(preg_replace('/^[-•*]\s*/u', '', $item) ?? $item);
            if ($item === '') {
                continue;
            }
            $parts = array_values(array_filter(array_map('trim', preg_split('/\s*\|\s*/', $item) ?: [])));
            if (count($parts) >= 2) {
                if ($current) $references[] = $current;
                $current = ['name' => $parts[0], 'role' => $parts[1] ?? null, 'company' => $parts[2] ?? null, 'phone' => $parts[3] ?? null, 'email' => null];
                continue;
            }

            if (preg_match('/(?:tel|t[eé]l|phone)\s*[:.]?\s*(\+?[\d\s().-]{7,})/iu', $item, $phoneMatch)) {
                if ($current) $current['phone'] = trim($phoneMatch[1]);
                continue;
            }
            if (preg_match('/[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}/i', $item, $emailMatch)) {
                if ($current) $current['email'] = $emailMatch[0];
                continue;
            }
            if ($current && empty($current['role']) && preg_match('/^(.+?)\s+(?:à|at)\s+(.+)$/iu', $item, $roleMatch)) {
                $current['role'] = trim($roleMatch[1]);
                $current['company'] = trim($roleMatch[2]);
                continue;
            }

            if (
                $current
                && ! empty($current['role'])
                && ! empty($current['company'])
                && preg_match('/^(?:\p{Lu}[\p{L}\'-]+\s+){1,3}\p{Lu}[\p{L}\'-]+$/u', $item)
            ) {
                $references[] = $current;
                $current = ['name' => $item, 'role' => null, 'company' => null, 'phone' => null, 'email' => null];
                continue;
            }

            if ($current && (! empty($current['phone']) || ! empty($current['email']))) {
                $references[] = $current;
                $current = null;
            }
            if ($current === null) {
                $current = ['name' => $item, 'role' => null, 'company' => null, 'phone' => null, 'email' => null];
            } elseif (empty($current['role'])) {
                $current['role'] = $item;
            }
        }
        if ($current) $references[] = $current;

        return array_values(array_filter($references, fn ($reference) => ! empty($reference['name'])));
    }

    private function parseSocialLinks(array $lines): array
    {
        $links = [];
        foreach ($lines as $line) {
            if (! preg_match_all('/(?:https?:\/\/)?(?:www\.)?(?:github\.com|linkedin\.com|facebook\.com)\/[A-Za-z0-9._\/-]+/i', $line, $matches)) {
                continue;
            }
            foreach ($matches[0] as $match) {
                $url = str_starts_with(strtolower($match), 'http') ? $match : 'https://'.$match;
                $host = strtolower((string) parse_url($url, PHP_URL_HOST));
                $platform = match (true) {
                    str_contains($host, 'github.com') => 'GitHub',
                    str_contains($host, 'linkedin.com') => 'LinkedIn',
                    str_contains($host, 'facebook.com') => 'Facebook',
                    default => null,
                };
                if ($platform) {
                    $links[$platform] = ['platform' => $platform, 'label' => $platform, 'url' => rtrim($url, '.,;)'), 'icon' => strtolower($platform)];
                }
            }
        }

        return array_values($links);
    }

    private function languageLocale(string $name): ?string
    {
        return match (Str::ascii(mb_strtolower($name, 'UTF-8'))) {
            'francais', 'french' => 'fr',
            'anglais', 'english' => 'en',
            'more', 'moore', 'moore' => 'mos',
            default => null,
        };
    }

    private function extractYear(string $text): ?string
    {
        return preg_match('/\b(?:19|20)\d{2}\b/', $text, $matches) ? $matches[0] : null;
    }

    private function parseExperiences(array $items): array
    {
        $experiences = [];
        $current = null;
        $pendingYear = null;

        foreach ($items as $item) {
            $item = trim($item);
            if ($item === '') continue;

            if (preg_match('/^[-•*]\s*/', $item)) {
                if ($current) $current['responsibilities'][] = preg_replace('/^[-•*]\s*/u', '', $item);
                continue;
            }

            if (preg_match('/\A((?:19|20)\d{2})\z/', $item, $standaloneYear)) {
                if ($current && (! empty($current['company']) || ! empty($current['position']))) {
                    $experiences[] = $current;
                }
                $current = null;
                $pendingYear = $standaloneYear[1];
                continue;
            }

            if ($pendingYear !== null) {
                $current = $this->emptyExperience();
                $current['company'] = $item;
                $current['start_date'] = $pendingYear.'-01-01';
                $current['end_date'] = $pendingYear.'-12-31';
                $pendingYear = null;
                continue;
            }

            $period = $this->extractDateRange($item);
            if ($period) {
                $heading = trim(substr($item, 0, $period['offset']), " \t|,;–—-");
                $heading = preg_replace('/(?<=\p{L})(?=(?:19|20)\d{2})/u', ' ', $heading) ?? $heading;
                if ($heading !== '' && ! preg_match('/^du(?:\s+\d{1,2})?$/iu', $heading)) {
                    if ($current && (! empty($current['company']) || ! empty($current['position']))) {
                        $experiences[] = $current;
                    }
                    $current = $this->emptyExperience();
                    if (str_contains($heading, '|')) {
                        [$current['company'], $current['position']] = array_pad(array_map('trim', explode('|', $heading, 2)), 2, null);
                    } else {
                        $current['company'] = $heading;
                    }
                }
                $current ??= $this->emptyExperience();
                $current['start_date'] = $period['start_date'];
                $current['end_date'] = $period['end_date'];
                $current['is_current'] = $period['is_current'];
                continue;
            }

            if (preg_match('/\b((?:19|20)\d{2})\b/', $item, $yearMatch, PREG_OFFSET_CAPTURE)
                && ! preg_match('/\b(?:année|year)\s*$/iu', substr($item, 0, $yearMatch[0][1]))) {
                $heading = trim(substr($item, 0, $yearMatch[0][1]), " \t|,;–—-");
                if ($heading !== '') {
                    if ($current && (! empty($current['company']) || ! empty($current['position']))) {
                        $experiences[] = $current;
                    }
                    $current = $this->emptyExperience();
                    $current['company'] = $heading;
                    $current['start_date'] = $yearMatch[1][0].'-01-01';
                    $current['end_date'] = $yearMatch[1][0].'-12-31';
                    continue;
                }
            }

            if (str_contains($item, '|')) {
                if ($current && (! empty($current['company']) || ! empty($current['position']))) {
                    $experiences[] = $current;
                }
                $current = $this->emptyExperience();
                [$current['company'], $current['position']] = array_pad(array_map('trim', explode('|', $item, 2)), 2, null);
                continue;
            }

            $current ??= $this->emptyExperience();
            if (empty($current['company'])) {
                $current['company'] = $item;
                continue;
            }
            if (empty($current['position'])) {
                $current['position'] = $item;
                continue;
            }

            $current['description'] = trim(($current['description'] ?? '').' '.$item);
        }

        if ($current && (! empty($current['company']) || ! empty($current['position']))) {
            $experiences[] = $current;
        }
        return array_values(array_filter($experiences, fn ($exp) => ! empty($exp['company']) || ! empty($exp['position'])));
    }

    private function emptyExperience(): array
    {
        return ['company' => null, 'position' => null, 'location' => null, 'start_date' => null, 'end_date' => null, 'is_current' => false, 'description' => null, 'responsibilities' => [], 'achievements' => []];
    }

    private function parseEducations(array $items): array
    {
        $educations = [];
        $current = null;

        foreach ($items as $item) {
            $trimmed = trim($item);
            if ($trimmed === '') {
                continue;
            }

            $period = $this->extractDateRange($trimmed);
            if ($period || preg_match('/\A(?:19|20)\d{2}\z/', $trimmed)) {
                $current ??= $this->emptyEducation();
                $current['start_date'] = $period['start_date'] ?? $trimmed.'-01-01';
                $current['end_date'] = $period['end_date'] ?? $trimmed.'-01-01';
                if (! $current['school'] && $current['degree']) $current['school'] = 'École / Université';
                if (! empty($current['school']) || ! empty($current['degree'])) $educations[] = $current;
                $current = null;
                continue;
            }

            $current ??= $this->emptyEducation();
            $isInstitution = preg_match('/^(?:universit|university|college|coll[eè]ge|institut|institute|[eé]cole|school|academy|centre|center|force\b)/iu', $trimmed) === 1;
            if ($isInstitution && $current['school'] && $current['degree']) {
                $educations[] = $current;
                $current = $this->emptyEducation();
            }
            if ($isInstitution && empty($current['school'])) {
                $current['school'] = $trimmed;
                continue;
            }

            if (empty($current['degree'])) {
                if (preg_match('/^(Master|Licence|Bac|Dipl[oô]me|Bachelor|Doctorat|Certification|Formation|M\.?Sc|MSc|MBA|cyber|informatique)/iu', $trimmed)) {
                    $current['degree'] = $trimmed;
                    continue;
                }

                if (empty($current['school'])) {
                    $current['school'] = $trimmed;
                    continue;
                }
            }

            if (empty($current['field']) && ! preg_match('/\d{4}/', $trimmed)) {
                $current['field'] = $trimmed;
                continue;
            }

            $current['description'] = $trimmed;
        }

        if ($current) {
            if ($current['school'] === null && $current['degree'] !== null) {
                $current['school'] = 'École / Université';
            }
            $educations[] = $current;
        }

        return array_values(array_filter($educations, fn ($edu) => ! empty($edu['degree']) || ! empty($edu['school'])));
    }

    private function emptyEducation(): array
    {
        return ['school' => null, 'degree' => null, 'field' => null, 'start_date' => null, 'end_date' => null, 'description' => null];
    }

    private function extractDateRange(string $text): ?array
    {
        $month = '(?:jan(?:uary|vier|v)?|feb(?:ruary)?|f[eé]v(?:rier)?|mar(?:ch|s)?|apr(?:il)?|avr(?:il)?|may|mai|jun(?:e|in)?|juin|jul(?:y)?|juil(?:let)?|aug(?:ust)?|ao[uû]t|sep(?:tember)?|sept(?:embre)?|oct(?:ober)?|nov(?:ember)?|dec(?:ember)?|d[eé]c(?:embre)?)';
        $date = '(?:\d{1,2}\s+)?(?:'.$month.'\.?\s+)?(?:19|20)\d{2}';
        $current = '(?:aujourd[’\']hui|present|current|now|nos\s+jours|à\s+nos\s+jours)';

        if (preg_match('/((?:19|20)\d{2})\s*[-–—]\s*(?:à\s*)?('.$month.'\.?\s+(?:19|20)\d{2})/iu', $text, $rangeMatch, PREG_OFFSET_CAPTURE)) {
            return [
                'start_date' => $this->normalizeDateToken($rangeMatch[1][0]),
                'end_date' => $this->normalizeDateToken($rangeMatch[2][0]),
                'is_current' => false,
                'offset' => $rangeMatch[0][1],
            ];
        }

        if (! preg_match('/('.$date.')\s*(?:-|–|—|à|au|to)\s*(?:à\s*)?('.$date.'|'.$current.')/iu', $text, $matches, PREG_OFFSET_CAPTURE)) {
            return null;
        }

        $start = $this->normalizeDateToken($matches[1][0]);
        $endToken = trim($matches[2][0]);
        $isCurrent = preg_match('/^(?:aujourd|present|current|now|nos\s+jours|à\s+nos)/iu', $endToken) === 1;

        return [
            'start_date' => $start,
            'end_date' => $isCurrent ? null : $this->normalizeDateToken($endToken),
            'is_current' => $isCurrent,
            'offset' => $matches[0][1],
        ];
    }

    private function normalizeDateToken(string $token): ?string
    {
        if (! preg_match('/((?:19|20)\d{2})/', $token, $yearMatch)) {
            return null;
        }
        $year = $yearMatch[1];
        $monthToken = trim(Str::ascii(mb_strtolower($token, 'UTF-8')));
        $months = [
            'jan' => 1, 'january' => 1, 'janvier' => 1, 'janv' => 1,
            'feb' => 2, 'february' => 2, 'fev' => 2, 'fevrier' => 2,
            'mar' => 3, 'march' => 3, 'mars' => 3,
            'apr' => 4, 'april' => 4, 'avr' => 4, 'avril' => 4,
            'may' => 5, 'mai' => 5,
            'jun' => 6, 'june' => 6, 'juin' => 6,
            'jul' => 7, 'july' => 7, 'juil' => 7, 'juillet' => 7,
            'aug' => 8, 'august' => 8, 'aou' => 8, 'aout' => 8,
            'sep' => 9, 'september' => 9, 'sept' => 9, 'septembre' => 9,
            'oct' => 10, 'october' => 10, 'octobre' => 10,
            'nov' => 11, 'november' => 11, 'novembre' => 11,
            'dec' => 12, 'december' => 12, 'decembre' => 12,
        ];

        foreach ($months as $name => $monthNumber) {
            if (preg_match('/\b'.preg_quote($name, '/').'\.?\s*'.$year.'\b/', $monthToken)) {
                return sprintf('%s-%02d-01', $year, $monthNumber);
            }
        }

        return $year.'-01-01';
    }

    private function parseSkills(array $items): array
    {
        $skills = [];

        foreach ($items as $item) {
            $parts = preg_split('/[;,]/', $item);
            foreach ($parts as $part) {
                $skill = trim($part);
                if ($skill !== '') {
                    $skills[] = $skill;
                }
            }
        }

        return $skills;
    }

    private function parseProjects(array $items): array
    {
        $projects = [];
        $current = null;
        foreach ($items as $item) {
            $item = trim($item);
            if ($item === '') continue;

            $period = $this->extractDateRange($item);
            if ($period) {
                if ($current) $current['project_date'] = $period['start_date'];
                continue;
            }
            if (preg_match('/^[-•*]\s*/u', $item)) {
                $item = trim(preg_replace('/^[-•*]\s*/u', '', $item) ?? $item);
            }

            if (str_contains($item, '|')) {
                if ($current) $projects[] = $current;
                [$title, $description] = array_pad(array_map('trim', explode('|', $item, 2)), 2, null);
                $current = ['title' => $title, 'description' => $description, 'project_date' => $this->extractYear($item) ? $this->extractYear($item).'-01-01' : null];
                continue;
            }

            if ($current === null) {
                $current = ['title' => $item, 'description' => null, 'project_date' => null];
            } elseif (empty($current['description'])) {
                $current['description'] = $item;
            } else {
                $current['description'] .= ' '.$item;
            }
        }
        if ($current) $projects[] = $current;

        return array_values(array_filter($projects, fn ($project) => ! empty($project['title'])));
    }

    private function summarizeText(array $lines): ?string
    {
        $candidate = [];
        foreach ($lines as $index => $line) {
            if ($index === 0 || preg_match('/^(EXPERIENCE|FORMATION|EDUCATION|COMPETENCES|COMPÉTENCES|SKILLS|LANGUES|LANGUAGES)$/i', $line)) {
                continue;
            }
            if ($this->looksLikeMetadata($line)) {
                continue;
            }
            $candidate[] = $line;
        }

        $summary = trim(implode(' ', array_slice($candidate, 0, 25)));

        return $summary !== '' ? Str::limit($summary, 500) : null;
    }
}
