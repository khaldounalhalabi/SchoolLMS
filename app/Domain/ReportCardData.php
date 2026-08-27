<?php

namespace App\Domain;

use App\Models\Semester;
use App\Models\User;
use Illuminate\Support\Collection;

readonly class ReportCardData
{
    public function __construct(
        public User        $student,
        public ?Semester   $semester,
        public Collection  $summaries,
        public Collection  $grades,
        public Collection  $examTypes,
    ) {}
}
