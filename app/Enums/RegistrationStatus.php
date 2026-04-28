<?php

namespace App\Enums;

enum RegistrationStatus: string
{
    case Unsubmitted = 'unsubmitted';
    case PendingReview = 'pending_review';
    case Verified = 'verified';
    case Rejected = 'rejected';
}
