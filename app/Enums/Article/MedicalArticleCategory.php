<?php

namespace App\Enums\Article;

enum MedicalArticleCategory :string
{

    case Cardiology = 'Cardiology';
    case Pulmonology = 'Pulmonology';
    case Gastroenterology = 'Gastroenterology';
    case Urology = 'Urology';
    case Ophthalmology = 'Ophthalmology';
    case Neurology = 'Neurology';
    case Otolaryngology = 'Otolaryngology (ENT)';
    case Dermatology = 'Dermatology';
    case Orthopedics = 'Orthopedics';
    case Dentistry = 'Dentistry';
    case Gynecology = 'Gynecology';
}
