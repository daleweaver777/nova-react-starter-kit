<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectRulesTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_rule_index_covers_every_declared_path()
    {
        $root = dirname(__DIR__, 2);
        $index = file_get_contents($root.'/.ai/rules/index.md');
        $ruleFiles = glob($root.'/.ai/rules/*.md');
        $declaredMappings = [];

        $this->assertNotFalse($ruleFiles);

        foreach ($ruleFiles as $ruleFile) {
            if (basename($ruleFile) === 'index.md') {
                continue;
            }

            preg_match('/\A---\R(.*?)\R---\R/s', file_get_contents($ruleFile), $frontmatter);
            $this->assertArrayHasKey(1, $frontmatter, basename($ruleFile).' has no frontmatter');

            preg_match_all('/^\s*-\s+[\'\"]?(.+?)[\'\"]?\s*$/m', $frontmatter[1], $paths);
            $this->assertNotEmpty($paths[1], basename($ruleFile).' declares no paths');

            foreach ($paths[1] as $path) {
                $rulePath = '.ai/rules/'.basename($ruleFile);
                $declaredMappings[] = $path.'|'.$rulePath;

                $this->assertMatchesRegularExpression(
                    '/^\|\s*'.preg_quote($path, '/').'\s*\|\s*'.preg_quote($rulePath, '/').'\s*\|$/m',
                    $index,
                    basename($ruleFile).' path '.$path.' is not mapped to the rule in index.md',
                );
            }
        }

        preg_match_all('/^\|\s*([^|]+?)\s*\|\s*(\.ai\/rules\/[^|]+?)\s*\|$/m', $index, $indexedRows, PREG_SET_ORDER);
        $indexedMappings = array_map(
            fn (array $row): string => trim($row[1]).'|'.trim($row[2]),
            $indexedRows,
        );

        sort($declaredMappings);
        sort($indexedMappings);

        $this->assertSame($declaredMappings, $indexedMappings, 'index.md contains a missing, stale or duplicate rule mapping');
    }

    public function test_the_repository_agent_router_loads_the_rule_index()
    {
        $agents = file_get_contents(dirname(__DIR__, 2).'/AGENTS.md');

        $this->assertStringContainsString('.ai/rules/index.md', $agents);
        $this->assertStringContainsString('Multiple globs may match', $agents);
    }

    public function test_shadcn_customizations_are_fully_restored()
    {
        $root = dirname(__DIR__, 2);
        $css = file_get_contents($root.'/resources/css/app.css');
        $sonner = file_get_contents($root.'/resources/js/components/ui/sonner.tsx');
        $package = file_get_contents($root.'/package.json');
        $workspace = file_get_contents($root.'/pnpm-workspace.yaml');

        $this->assertSame(1, preg_match_all('/^@import\s+["\']tw-animate-css["\'];$/m', $css));
        $this->assertSame(1, preg_match_all('/^@import\s+["\']shadcn\/tailwind\.css["\'];$/m', $css));
        $this->assertSame(1, preg_match_all('/^@import\s+["\']@fontsource-variable\/inter["\'];$/m', $css));
        $this->assertStringEndsWith("\n", $css);
        $this->assertSame(1, preg_match_all('/^:root \{/m', $css));
        $this->assertSame(1, preg_match_all('/^\.dark \{/m', $css));
        $this->assertSame(1, preg_match_all('/^@theme inline \{/m', $css));
        $this->assertSame(1, preg_match_all('/^@layer base \{/m', $css));

        $this->assertFileExists($root.'/resources/js/hooks/use-mobile.tsx');
        $this->assertFileDoesNotExist($root.'/resources/js/hooks/use-mobile.ts');
        $this->assertStringContainsString('useAppearance', $sonner);
        $this->assertStringContainsString('useFlashToast', $sonner);
        $this->assertStringContainsString('theme={appearance}', $sonner);
        $this->assertStringNotContainsString('next-themes', $package);

        $this->assertMatchesRegularExpression('/^allowBuilds:\R\s+unrs-resolver: false$/m', $workspace);
        $this->assertStringNotContainsString('unrs-resolver: set this to true or false', $workspace);
    }
}
