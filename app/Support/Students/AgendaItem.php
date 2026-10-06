<?php

namespace App\Support\Students;

use Carbon\CarbonInterface;

/**
 * Um compromisso na agenda do estudante, venha ele de uma atividade, de uma
 * aula ou do calendário do período. Normaliza as três fontes para que as
 * telas (dashboard e agenda) tenham um único formato para exibir.
 */
final readonly class AgendaItem
{
    public const KIND_DEADLINE = 'deadline';

    public const KIND_LESSON = 'lesson';

    public const KIND_TERM_EVENT = 'term_event';

    public function __construct(
        public string $kind,
        public string $title,
        public string $context,
        public CarbonInterface $startsAt,
        public string $icon,
        public ?string $url = null,
        public ?string $color = null,
        public bool $isUrgent = false,
        public bool $isAllDay = false,
    ) {}
}
