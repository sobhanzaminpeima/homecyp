<?php

namespace Tests\Unit;

use App\Services\AttachmentTextExtractor;
use PHPUnit\Framework\TestCase;

class AttachmentTextExtractorTest extends TestCase
{
    public function test_extracts_plain_text_without_exceeding_context_limit(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'homecyp-');
        file_put_contents($path, str_repeat('property details ', 1000));

        $text = (new AttachmentTextExtractor())->extract($path, 'text/plain');
        @unlink($path);

        $this->assertNotNull($text);
        $this->assertLessThanOrEqual(12000, strlen($text));
    }
}
