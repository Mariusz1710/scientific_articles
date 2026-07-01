<?php

namespace App\Enum;

enum ArticleStatus: string
{
    case DRAFT = 'draft';                 // zgłoszenie w edycji, jeszcze nie przesłane
    case SUBMITTED = 'submitted';         // zgłoszony
    case IN_REVIEW = 'in_review';         // w recenzji
    case ACCEPTED = 'accepted';           // zaakceptowany przez recenzentów
    case REJECTED = 'rejected';           // odrzucony
    case PUBLISHED = 'published';         // opublikowany przez opiekuna

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Wersja robocza',
            self::SUBMITTED => 'Zgłoszony',
            self::IN_REVIEW => 'W recenzji',
            self::ACCEPTED => 'Zaakceptowany',
            self::REJECTED => 'Odrzucony',
            self::PUBLISHED => 'Opublikowany',
        };
    }
}
