<?php

namespace App\Models;

use App\Enums\TermEventType;
use Database\Factories\AcademicTermEventFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Marco do calendário de um período letivo: semana de provas, feriado...
 */
#[Fillable(['title', 'type', 'starts_on', 'ends_on'])]
class AcademicTermEvent extends Model
{
    /** @use HasFactory<AcademicTermEventFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => TermEventType::class,
            'starts_on' => 'date',
            'ends_on' => 'date',
        ];
    }

    /**
     * @return BelongsTo<AcademicTerm, $this>
     */
    public function academicTerm(): BelongsTo
    {
        return $this->belongsTo(AcademicTerm::class);
    }
}
