<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Finder\Finder;

final class FormValidationTest extends TestCase
{
    public function test_every_react_form_disables_native_browser_validation(): void
    {
        $files = Finder::create()
            ->files()
            ->in(dirname(__DIR__, 2).'/resources')
            ->name(['*.tsx', '*.jsx']);

        $formCount = 0;
        $formsMissingNoValidate = [];

        foreach ($files as $file) {
            $contents = $file->getContents();
            preg_match_all(
                '/<(?:Form|form)\b[^>]*>/s',
                $contents,
                $matches,
                PREG_OFFSET_CAPTURE,
            );

            foreach ($matches[0] as [$openingTag, $offset]) {
                $formCount++;

                if (! preg_match('/\bnoValidate\b/', $openingTag)) {
                    $line = substr_count(substr($contents, 0, $offset), "\n") + 1;
                    $formsMissingNoValidate[] = sprintf(
                        '%s:%d',
                        $file->getRelativePathname(),
                        $line,
                    );
                }
            }
        }

        $this->assertGreaterThan(0, $formCount, 'No React forms were found to validate.');
        $this->assertSame(
            [],
            $formsMissingNoValidate,
            "Every React form must disable native browser validation. Missing noValidate:\n".
                implode("\n", $formsMissingNoValidate),
        );
    }
}
