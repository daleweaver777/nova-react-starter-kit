<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Finder\Finder;

final class FormErrorFocusTest extends TestCase
{
    public function test_focus_helper_scopes_error_fields_to_their_form_until_focus_succeeds(): void
    {
        $contents = file_get_contents(
            dirname(__DIR__, 2).'/resources/js/lib/utils.ts',
        );

        $this->assertIsString($contents);
        $this->assertStringContainsString('for (const fieldName of Object.keys(errors))', $contents);
        $this->assertStringContainsString('document.getElementById(formId)', $contents);
        $this->assertStringContainsString('form instanceof HTMLFormElement', $contents);
        $this->assertStringContainsString('form.elements.namedItem(fieldName)', $contents);
        $this->assertStringContainsString('field instanceof HTMLElement', $contents);
        $this->assertStringContainsString('field.focus()', $contents);
        $this->assertStringContainsString('document.activeElement === field', $contents);
        $this->assertStringNotContainsString('document.getElementsByName', $contents);
        $this->assertStringNotContainsString('requestAnimationFrame', $contents);
    }

    public function test_every_inertia_form_with_named_fields_has_its_own_error_focus_handler(): void
    {
        $files = Finder::create()
            ->files()
            ->in(dirname(__DIR__, 2).'/resources/js')
            ->name(['*.tsx', '*.jsx']);

        $formCount = 0;
        $formsWithoutFocusHandling = [];

        foreach ($files as $file) {
            $contents = $file->getContents();
            preg_match_all('/<Form\b/', $contents, $formMatches, PREG_OFFSET_CAPTURE);

            foreach ($formMatches[0] as [$match, $offset]) {
                $length = strlen($contents);
                $braceDepth = 0;
                $quote = null;
                $escaped = false;
                $openingTagEnd = null;

                for ($index = $offset + strlen($match); $index < $length; $index++) {
                    $character = $contents[$index];

                    if ($quote !== null) {
                        if ($escaped) {
                            $escaped = false;

                            continue;
                        }

                        if ($character === '\\') {
                            $escaped = true;

                            continue;
                        }

                        if ($character === $quote) {
                            $quote = null;
                        }

                        continue;
                    }

                    if (in_array($character, ['"', "'", '`'], true)) {
                        $quote = $character;

                        continue;
                    }

                    if ($character === '{') {
                        $braceDepth++;

                        continue;
                    }

                    if ($character === '}') {
                        $braceDepth--;

                        continue;
                    }

                    if ($character === '>' && $braceDepth === 0) {
                        $openingTagEnd = $index;

                        break;
                    }
                }

                $this->assertNotNull(
                    $openingTagEnd,
                    sprintf('Could not parse Form in %s.', $file->getRelativePathname()),
                );

                $closingTagOffset = strpos($contents, '</Form>', $openingTagEnd);

                $this->assertNotFalse(
                    $closingTagOffset,
                    sprintf('Could not find closing Form tag in %s.', $file->getRelativePathname()),
                );

                $openingTag = substr($contents, $offset, $openingTagEnd - $offset + 1);
                $formBody = substr($contents, $openingTagEnd + 1, $closingTagOffset - $openingTagEnd - 1);

                if (! preg_match('/\bname=(?:"[^"]+"|\'[^\']+\'|\{[^}]+\})/', $formBody)) {
                    continue;
                }

                $formCount++;
                $line = substr_count(substr($contents, 0, $offset), "\n") + 1;

                if (! preg_match('/\bid=\{([A-Za-z_$][A-Za-z0-9_$]*)\}/', $openingTag, $idMatch)) {
                    $formsWithoutFocusHandling[] = sprintf('%s:%d (missing form id)', $file->getRelativePathname(), $line);

                    continue;
                }

                $formId = $idMatch[1];

                if (
                    ! preg_match('/const\s+'.preg_quote($formId, '/').'\s*=\s*useId\(\)/', $contents)
                    || ! str_contains($openingTag, "focusFirstFormError({$formId}, errors)")
                ) {
                    $formsWithoutFocusHandling[] = sprintf('%s:%d', $file->getRelativePathname(), $line);
                }
            }
        }

        $this->assertGreaterThan(0, $formCount, 'No Inertia forms with named fields were found.');
        $this->assertSame(
            [],
            $formsWithoutFocusHandling,
            "Every Inertia form with named fields must focus its first validation error:\n".
                implode("\n", $formsWithoutFocusHandling),
        );
    }

    public function test_pages_use_inertias_form_component_directly(): void
    {
        $files = Finder::create()
            ->files()
            ->in(dirname(__DIR__, 2).'/resources/js')
            ->name(['*.tsx', '*.jsx']);

        $formFileCount = 0;
        $customFormImports = [];

        foreach ($files as $file) {
            $contents = $file->getContents();

            if (! str_contains($contents, '<Form')) {
                continue;
            }

            $formFileCount++;

            if (! preg_match('/import \{[^}]*\bForm\b[^}]*\} from [\'\"]@inertiajs\/react[\'\"]/', $contents)) {
                $customFormImports[] = $file->getRelativePathname();
            }
        }

        $this->assertGreaterThan(0, $formFileCount, 'No Inertia forms were found.');
        $this->assertSame(
            [],
            $customFormImports,
            "Use Inertia's Form component directly:\n".implode("\n", $customFormImports),
        );
    }
}
