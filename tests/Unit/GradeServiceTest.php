<?php

namespace Tests\Unit;

use App\Services\Grade\GradeService;
use PHPUnit\Framework\TestCase;

class GradeServiceTest extends TestCase
{
    public function test_letter_grade_thresholds_are_owned_by_the_grade_module(): void
    {
        $service = new GradeService;

        $this->assertSame('A', $service->letterGrade(90));
        $this->assertSame('B', $service->letterGrade(80));
        $this->assertSame('C', $service->letterGrade(70));
        $this->assertSame('D', $service->letterGrade(60));
        $this->assertSame('F', $service->letterGrade(59.99));
    }
}
