<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Participant;
use App\Models\Payment;
use App\Models\Registration;
use App\Models\RegistrationDraft;
use App\Models\RegistrationDraftItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InvoiceController extends Controller
{
    public function pdf(Request $request, int $event)
    {
        $event = Event::findOrFail($event);
        $contingent = Auth::user()->contingent;

        if (!$contingent) {
            abort(403, 'Anda belum memiliki data kontingen.');
        }

        $draft = RegistrationDraft::where('event_id', $event->id)
            ->where('contingent_id', $contingent->id)
            ->where('status', 'draft')
            ->first();

        if (!$draft) {
            abort(404, 'Draft pendaftaran tidak ditemukan.');
        }

        $athleteItems = RegistrationDraftItem::query()
            ->where('registration_draft_id', $draft->id)
            ->whereNotNull('sub_category_id')
            ->with(['participant', 'subCategory.eventCategory'])
            ->get();

        $athleteSelections = $athleteItems->groupBy('sub_category_id')
            ->map(function ($items, $subCategoryId) {
                $subCategory = $items->first()->subCategory;

                $athletes = $items->map(function ($item) {
                    return [
                        'participant' => $item->participant,
                        'team_group_id' => $item->team_group_id,
                    ];
                });

                return [
                    'subCategory' => $subCategory,
                    'athletes' => $athletes,
                ];
            })
            ->values();

        $coachIds = RegistrationDraftItem::query()
            ->where('registration_draft_id', $draft->id)
            ->whereNull('sub_category_id')
            ->pluck('participant_id')
            ->unique()
            ->values()
            ->toArray();

        $coaches = collect();
        if (count($coachIds) > 0) {
            $coaches = Participant::coaches()
                ->where('contingent_id', $contingent->id)
                ->whereIn('id', $coachIds)
                ->orderBy('name')
                ->get();
        }

        // Calculate totals
        $totalAthleteFee = 0;
        foreach ($athleteSelections as $selection) {
            if ($selection['subCategory']->isTeam()) {
                $teamCount = collect($selection['athletes'])
                    ->pluck('team_group_id')
                    ->filter()
                    ->unique()
                    ->count();
                $teamCount = max($teamCount, 1);
                $totalAthleteFee += (float) $selection['subCategory']->price * $teamCount;
            } else {
                $totalAthleteFee += (float) $selection['subCategory']->price * count($selection['athletes']);
            }
        }

        $totalCoachFee = (float) $event->coach_fee * $coaches->count();
        $uniqueCode = $draft->getOrAssignUniqueCode();
        $totalAmount = (float) $event->event_fee + $totalAthleteFee + $totalCoachFee + $uniqueCode;

        return view('pdf.event-invoice', [
            'event' => $event,
            'contingent' => $contingent,
            'athleteSelections' => $athleteSelections,
            'coaches' => $coaches,
            'totalAthleteFee' => $totalAthleteFee,
            'totalCoachFee' => $totalCoachFee,
            'totalAmount' => $totalAmount,
            'uniqueCode' => $uniqueCode,
            'eventFee' => $event->event_fee,
        ]);
    }

    /**
     * Download invoice PDF by payment ID (for already submitted payments)
     */
    public function downloadByPayment(int $payment)
    {
        $payment = Payment::with(['event', 'contingent', 'registrations.participant', 'registrations.subCategory.eventCategory'])
            ->findOrFail($payment);

        $contingent = Auth::user()->contingent;

        if (!$contingent || $payment->contingent_id !== $contingent->id) {
            abort(403, 'Akses ditolak. Invoice ini bukan milik kontingen Anda.');
        }

        $event = $payment->event;

        // Build athlete selections from registrations
        $athleteRegistrations = $payment->registrations->filter(fn ($r) => $r->sub_category_id !== null);
        $coachRegistrations = $payment->registrations->filter(fn ($r) => $r->sub_category_id === null);

        $athleteSelections = $athleteRegistrations->groupBy('sub_category_id')
            ->map(function ($items) {
                $subCategory = $items->first()->subCategory;
                $athletes = $items->map(function ($reg) {
                    return [
                        'participant' => $reg->participant,
                        'team_group_id' => $reg->team_group_id,
                    ];
                });

                return [
                    'subCategory' => $subCategory,
                    'athletes' => $athletes,
                ];
            })
            ->values();

        $coaches = $coachRegistrations->pluck('participant')->sortBy('name')->values();

        // Calculate totals
        $totalAthleteFee = 0;
        foreach ($athleteSelections as $selection) {
            if ($selection['subCategory']->isTeam()) {
                $teamCount = collect($selection['athletes'])
                    ->pluck('team_group_id')
                    ->filter()
                    ->unique()
                    ->count();
                $teamCount = max($teamCount, 1);
                $totalAthleteFee += (float) $selection['subCategory']->price * $teamCount;
            } else {
                $totalAthleteFee += (float) $selection['subCategory']->price * count($selection['athletes']);
            }
        }

        $totalCoachFee = (float) $event->coach_fee * $coaches->count();
        $uniqueCode = $payment->total_amount - ($event->event_fee + $totalAthleteFee + $totalCoachFee);
        $uniqueCode = max(0, (int) $uniqueCode);
        $totalAmount = (float) $payment->total_amount;

        return view('pdf.event-invoice', [
            'event' => $event,
            'contingent' => $contingent,
            'athleteSelections' => $athleteSelections,
            'coaches' => $coaches,
            'totalAthleteFee' => $totalAthleteFee,
            'totalCoachFee' => $totalCoachFee,
            'totalAmount' => $totalAmount,
            'uniqueCode' => $uniqueCode,
            'eventFee' => $event->event_fee,
            'payment' => $payment,
        ]);
    }
}
