<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

final class SetupScriptTest extends TestCase
{
    private string $scriptPath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->scriptPath = dirname(__DIR__, 2) . '/setup.sh';
    }

    public function test_setup_script_exists(): void
    {
        self::assertFileExists($this->scriptPath);
    }

    public function test_setup_script_is_executable(): void
    {
        self::assertTrue(is_executable($this->scriptPath));
    }

    public function test_setup_script_uses_safe_bash_flags(): void
    {
        $content = file_get_contents($this->scriptPath);

        self::assertNotFalse($content);
        self::assertStringContainsString('#!/usr/bin/env bash', $content);
        self::assertStringContainsString('set -Eeuo pipefail', $content);
    }
}
