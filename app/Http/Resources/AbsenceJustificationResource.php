<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AbsenceJustificationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'attendance_id' => $this->attendance_id,
            'reason' => $this->reason,
            'submitted_by' => $this->submitted_by,
            'status' => $this->status,
            'attendance' => new AttendanceResource($this->whenLoaded('attendance')),
        ];
    }
}
