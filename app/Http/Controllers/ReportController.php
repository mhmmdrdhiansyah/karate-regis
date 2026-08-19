<?php

namespace App\Http\Controllers;

use App\Models\Contingent;
use App\Models\Event;
use App\Models\Payment;
use App\Models\Registration;
use App\Models\SubCategory;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $query = Payment::with(['contingent', 'event', 'verifiedBy', 'registrations.subCategory']);

        // Filter Event
        if ($request->filled('event')) {
            $query->where('event_id', $request->event);
        }

        // Filter Contingent
        if ($request->filled('contingent')) {
            $query->where('contingent_id', $request->contingent);
        }

        // Filter Status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter Date Range
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        // Search Keyword
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', '%' . $search . '%')
                  ->orWhereHas('contingent', function ($cq) use ($search) {
                      $cq->where('name', 'like', '%' . $search . '%');
                  });
            });
        }

        // Clone query for financial summary statistics (before pagination)
        $summaryQuery = clone $query;
        $allFilteredPayments = $summaryQuery->get();

        $allFilteredPayments->transform(function ($p) {
            return $this->formatPaymentRecord($p);
        });

        $summary = [
            'total_verified_amount' => $allFilteredPayments->where('status_raw', 'verified')->sum('total_amount'),
            'total_pending_amount' => $allFilteredPayments->where('status_raw', 'pending')->sum('total_amount'),
            'total_discount_amount' => $allFilteredPayments->sum('total_discount'),
            'total_subtotal_amount' => $allFilteredPayments->sum('subtotal_amount'),
            'total_grand_amount' => $allFilteredPayments->sum('total_amount'),
            'total_invoices' => $allFilteredPayments->count(),
            'total_verified_count' => $allFilteredPayments->where('status_raw', 'verified')->count(),
            'total_athletes' => $allFilteredPayments->sum('athlete_count'),
            'total_teams' => $allFilteredPayments->sum('team_count'),
            'total_coaches' => $allFilteredPayments->sum('coach_count'),
        ];

        // Ordering & Pagination
        $query->orderBy('created_at', 'desc');

        $perPage = $request->get('per_page', 25);
        if (!in_array($perPage, [10, 25, 50, 100])) {
            $perPage = 25;
        }

        $payments = $query->paginate($perPage)->withQueryString();

        // Format collection for current page
        $payments->getCollection()->transform(function ($payment) {
            return $this->formatPaymentRecord($payment);
        });

        // Filter options
        $events = Event::orderBy('name')->get(['id', 'name']);
        $contingents = Contingent::orderBy('name')->get(['id', 'name']);
        $statusOptions = [
            'pending' => 'Pending (Menunggu)',
            'verified' => 'Verified (Lunas)',
            'rejected' => 'Rejected (Ditolak)',
            'cancelled' => 'Cancelled (Batal)',
        ];

        return view('reports.index', compact(
            'payments',
            'events',
            'contingents',
            'statusOptions',
            'summary'
        ));
    }

    public function financialExport(Request $request)
    {
        $query = Payment::with(['contingent', 'event', 'verifiedBy', 'registrations.subCategory']);

        if ($request->filled('event')) {
            $query->where('event_id', $request->event);
        }
        if ($request->filled('contingent')) {
            $query->where('contingent_id', $request->contingent);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', '%' . $search . '%')
                  ->orWhereHas('contingent', function ($cq) use ($search) {
                      $cq->where('name', 'like', '%' . $search . '%');
                  });
            });
        }

        $payments = $query->orderBy('created_at', 'desc')->get();

        $payments->transform(function ($payment) {
            return $this->formatPaymentRecord($payment);
        });

        return response()->json($payments);
    }

    public function financialPdf(Request $request)
    {
        $query = Payment::with(['contingent', 'event', 'verifiedBy', 'registrations.subCategory']);

        $activeEvent = $request->filled('event') ? Event::find($request->event) : null;
        $activeContingent = $request->filled('contingent') ? Contingent::find($request->contingent) : null;

        if ($request->filled('event')) {
            $query->where('event_id', $request->event);
        }
        if ($request->filled('contingent')) {
            $query->where('contingent_id', $request->contingent);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', '%' . $search . '%')
                  ->orWhereHas('contingent', function ($cq) use ($search) {
                      $cq->where('name', 'like', '%' . $search . '%');
                  });
            });
        }

        $payments = $query->orderBy('created_at', 'desc')->get();

        $payments->transform(function ($payment) {
            return $this->formatPaymentRecord($payment);
        });

        $summary = [
            'total_verified_amount' => $payments->where('status_raw', 'verified')->sum('total_amount'),
            'total_pending_amount' => $payments->where('status_raw', 'pending')->sum('total_amount'),
            'total_discount_amount' => $payments->sum('total_discount'),
            'total_subtotal_amount' => $payments->sum('subtotal_amount'),
            'total_grand_amount' => $payments->sum('total_amount'),
            'total_invoices' => $payments->count(),
            'total_verified_count' => $payments->where('status_raw', 'verified')->count(),
            'total_athletes' => $payments->sum('athlete_count'),
            'total_teams' => $payments->sum('team_count'),
            'total_coaches' => $payments->sum('coach_count'),
        ];

        $printedAt = now()->translatedFormat('d F Y, H:i') . ' WIB';

        return view('reports.financial-pdf', compact(
            'payments',
            'activeEvent',
            'activeContingent',
            'summary',
            'printedAt'
        ));
    }

    /**
     * Format payment record with all financial attributes and breakdown counts
     */
    private function formatPaymentRecord($payment)
    {
        $payment->invoice_number = 'INV-' . str_pad($payment->id, 5, '0', STR_PAD_LEFT);
        $payment->contingent_name = $payment->contingent?->name ?? '-';
        $payment->event_name = $payment->event?->name ?? '-';

        $statusVal = is_object($payment->status) ? $payment->status->value : $payment->status;
        $payment->status_raw = strtolower($statusVal);

        // Subtotal = total_amount + total_discount
        $payment->subtotal_amount = (float) $payment->total_amount + (float) $payment->total_discount;

        // Breakdown registration counts
        $regs = $payment->registrations;
        $athleteRegs = $regs->filter(fn ($r) => $r->sub_category_id !== null && $r->subCategory?->category_type !== 'beregu');
        $teamRegs = $regs->filter(fn ($r) => $r->sub_category_id !== null && $r->subCategory?->category_type === 'beregu');
        $coachRegs = $regs->filter(fn ($r) => $r->sub_category_id === null);

        $payment->athlete_count = $athleteRegs->count();
        $payment->team_count = $teamRegs->pluck('team_group_id')->filter()->unique()->count() ?: ($teamRegs->count() ? 1 : 0);
        $payment->coach_count = $coachRegs->count();

        $entryParts = [];
        if ($payment->athlete_count > 0) $entryParts[] = $payment->athlete_count . ' Atlet';
        if ($payment->team_count > 0) $entryParts[] = $payment->team_count . ' Tim';
        if ($payment->coach_count > 0) $entryParts[] = $payment->coach_count . ' Official';

        $payment->entry_summary = count($entryParts) > 0 ? implode(', ', $entryParts) : '-';
        $payment->date_formatted = $payment->created_at ? $payment->created_at->format('d-m-Y H:i') : '-';
        $payment->verified_by_name = $payment->verifiedBy?->name ?? '-';

        return $payment;
    }

    public function participants(Request $request)
    {
        // Build query with all necessary joins
        $query = Registration::with(['participant.contingent', 'subCategory.eventCategory.event', 'teamGroup'])
            ->whereNull('registrations.deleted_at')  // Exclude soft-deleted registrations
            ->whereNotNull('registrations.sub_category_id')  // Exclude coach registrations
            ->join('participants', 'participants.id', '=', 'registrations.participant_id')
            ->join('sub_categories', 'sub_categories.id', '=', 'registrations.sub_category_id')
            ->join('event_categories', 'event_categories.id', '=', 'sub_categories.event_category_id')
            ->join('events', 'events.id', '=', 'event_categories.event_id')
            ->join('contingents', 'contingents.id', '=', 'participants.contingent_id')
            ->leftJoin('team_groups', 'team_groups.id', '=', 'registrations.team_group_id')
            ->select([
                'registrations.*',
                'participants.name as participant_name',
                'participants.institusi',
                'participants.birth_date',
                'participants.gender as participant_gender',
                'contingents.name as contingent_name',
                'sub_categories.name as sub_category_name',
                'sub_categories.category_type',
                'sub_categories.gender as sub_category_gender',
                'event_categories.type',
                'event_categories.class_name',
                'event_categories.min_birth_date',
                'event_categories.max_birth_date',
                'events.name as event_name',
                'team_groups.team_name',
                'participants.is_verified',
                'participants.rejection_reason',
            ]);

        // Apply filters
        if ($request->filled('event')) {
            $query->where('events.id', $request->event);
        }

        if ($request->filled('contingent')) {
            $query->where('contingents.id', $request->contingent);
        }

        if ($request->filled('category_type')) {
            $query->where('sub_categories.category_type', $request->category_type);
        }

        if ($request->filled('gender')) {
            $query->where('sub_categories.gender', $request->gender);
        }

        if ($request->filled('status_berkas')) {
            if ($request->status_berkas === 'verified') {
                $query->where('participants.is_verified', true);
            } elseif ($request->status_berkas === 'rejected') {
                $query->where('participants.is_verified', false)
                      ->whereNotNull('participants.rejection_reason')
                      ->where('participants.rejection_reason', '!=', '');
            } elseif ($request->status_berkas === 'pending') {
                $query->where('participants.is_verified', false)
                      ->where(function ($q) {
                          $q->whereNull('participants.rejection_reason')
                            ->orWhere('participants.rejection_reason', '=', '');
                      });
            }
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('participants.name', 'like', '%' . $search . '%')
                  ->orWhere('contingents.name', 'like', '%' . $search . '%');
            });
        }

        // Default ordering: contingent name, then participant name
        $query->orderBy('contingents.name', 'asc')
              ->orderBy('participants.name', 'asc');

        // Get per page from request, default to 25
        $perPage = $request->get('per_page', 25);
        if (!in_array($perPage, [10, 25, 50, 100])) {
            $perPage = 25;
        }

        // Paginate with query string
        $registrations = $query->paginate($perPage)->withQueryString();

        // Get filter options
        $events = Event::orderBy('name')->get(['id', 'name']);
        $contingents = Contingent::orderBy('name')->get(['id', 'name']);
        $categoryTypes = SubCategory::distinct()->orderBy('category_type')->pluck('category_type');
        $genders = [
            'M' => 'Putra',
            'F' => 'Putri',
            'Mixed' => 'Campuran',
        ];
        $statusBerkasOptions = [
            'pending' => 'Pending',
            'verified' => 'Verified',
            'rejected' => 'Rejected',
        ];

        // Calculate and format 12 required report fields for each registration
        $registrations->getCollection()->transform(function ($registration) {
            return $this->formatRegistrationRecord($registration);
        });

        return view('reports.participants', compact(
            'registrations',
            'events',
            'contingents',
            'categoryTypes',
            'genders',
            'statusBerkasOptions',
        ));
    }

    public function participantsExport(Request $request)
    {
        // Build query with all necessary joins (same as participants method)
        $query = Registration::with(['participant.contingent', 'subCategory.eventCategory.event', 'teamGroup'])
            ->whereNull('registrations.deleted_at')
            ->whereNotNull('registrations.sub_category_id')
            ->join('participants', 'participants.id', '=', 'registrations.participant_id')
            ->join('sub_categories', 'sub_categories.id', '=', 'registrations.sub_category_id')
            ->join('event_categories', 'event_categories.id', '=', 'sub_categories.event_category_id')
            ->join('events', 'events.id', '=', 'event_categories.event_id')
            ->join('contingents', 'contingents.id', '=', 'participants.contingent_id')
            ->leftJoin('team_groups', 'team_groups.id', '=', 'registrations.team_group_id')
            ->select([
                'registrations.*',
                'participants.name as participant_name',
                'participants.institusi',
                'participants.birth_date',
                'participants.gender as participant_gender',
                'contingents.name as contingent_name',
                'sub_categories.name as sub_category_name',
                'sub_categories.category_type',
                'sub_categories.gender as sub_category_gender',
                'event_categories.type',
                'event_categories.class_name',
                'event_categories.min_birth_date',
                'event_categories.max_birth_date',
                'events.name as event_name',
                'team_groups.team_name',
                'participants.is_verified',
                'participants.rejection_reason',
            ]);

        // Apply filters (same as participants method)
        if ($request->filled('event')) {
            $query->where('events.id', $request->event);
        }

        if ($request->filled('contingent')) {
            $query->where('contingents.id', $request->contingent);
        }

        if ($request->filled('category_type')) {
            $query->where('sub_categories.category_type', $request->category_type);
        }

        if ($request->filled('gender')) {
            $query->where('sub_categories.gender', $request->gender);
        }

        if ($request->filled('status_berkas')) {
            if ($request->status_berkas === 'verified') {
                $query->where('participants.is_verified', true);
            } elseif ($request->status_berkas === 'rejected') {
                $query->where('participants.is_verified', false)
                      ->whereNotNull('participants.rejection_reason')
                      ->where('participants.rejection_reason', '!=', '');
            } elseif ($request->status_berkas === 'pending') {
                $query->where('participants.is_verified', false)
                      ->where(function ($q) {
                          $q->whereNull('participants.rejection_reason')
                            ->orWhere('participants.rejection_reason', '=', '');
                      });
            }
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('participants.name', 'like', '%' . $search . '%')
                  ->orWhere('contingents.name', 'like', '%' . $search . '%');
            });
        }

        // Same ordering
        $query->orderBy('contingents.name', 'asc')
              ->orderBy('participants.name', 'asc');

        // Get ALL data (no pagination)
        $registrations = $query->get();

        // Calculate and format 12 required report fields for each registration
        $registrations->transform(function ($registration) {
            return $this->formatRegistrationRecord($registration);
        });

        // Return JSON for export
        return response()->json($registrations);
    }

    public function participantsPdf(Request $request)
    {
        // Build query with all necessary joins
        $query = Registration::with(['participant.contingent', 'subCategory.eventCategory.event', 'teamGroup'])
            ->whereNull('registrations.deleted_at')
            ->whereNotNull('registrations.sub_category_id')
            ->join('participants', 'participants.id', '=', 'registrations.participant_id')
            ->join('sub_categories', 'sub_categories.id', '=', 'registrations.sub_category_id')
            ->join('event_categories', 'event_categories.id', '=', 'sub_categories.event_category_id')
            ->join('events', 'events.id', '=', 'event_categories.event_id')
            ->join('contingents', 'contingents.id', '=', 'participants.contingent_id')
            ->leftJoin('team_groups', 'team_groups.id', '=', 'registrations.team_group_id')
            ->select([
                'registrations.*',
                'participants.name as participant_name',
                'participants.institusi',
                'participants.birth_date',
                'participants.gender as participant_gender',
                'contingents.name as contingent_name',
                'sub_categories.name as sub_category_name',
                'sub_categories.category_type',
                'sub_categories.gender as sub_category_gender',
                'event_categories.type',
                'event_categories.class_name',
                'event_categories.min_birth_date',
                'event_categories.max_birth_date',
                'events.name as event_name',
                'team_groups.team_name',
                'participants.is_verified',
                'participants.rejection_reason',
            ]);

        // Selected filter models for PDF Header Context
        $activeEvent = $request->filled('event') ? Event::find($request->event) : null;
        $activeContingent = $request->filled('contingent') ? Contingent::find($request->contingent) : null;

        // Apply filters
        if ($request->filled('event')) {
            $query->where('events.id', $request->event);
        }

        if ($request->filled('contingent')) {
            $query->where('contingents.id', $request->contingent);
        }

        if ($request->filled('category_type')) {
            $query->where('sub_categories.category_type', $request->category_type);
        }

        if ($request->filled('gender')) {
            $query->where('sub_categories.gender', $request->gender);
        }

        if ($request->filled('status_berkas')) {
            if ($request->status_berkas === 'verified') {
                $query->where('participants.is_verified', true);
            } elseif ($request->status_berkas === 'rejected') {
                $query->where('participants.is_verified', false)
                      ->whereNotNull('participants.rejection_reason')
                      ->where('participants.rejection_reason', '!=', '');
            } elseif ($request->status_berkas === 'pending') {
                $query->where('participants.is_verified', false)
                      ->where(function ($q) {
                          $q->whereNull('participants.rejection_reason')
                            ->orWhere('participants.rejection_reason', '=', '');
                      });
            }
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('participants.name', 'like', '%' . $search . '%')
                  ->orWhere('contingents.name', 'like', '%' . $search . '%');
            });
        }

        // Ordering
        $query->orderBy('contingents.name', 'asc')
              ->orderBy('participants.name', 'asc');

        $registrations = $query->get();

        // Calculate and format fields for each registration
        $registrations->transform(function ($registration) {
            return $this->formatRegistrationRecord($registration);
        });

        // Summary statistics for PDF Footer / Summary
        $summary = [
            'total_registrations' => $registrations->count(),
            'total_male' => $registrations->whereIn('sex', ['M', 'L'])->count(),
            'total_female' => $registrations->whereIn('sex', ['F', 'P'])->count(),
            'total_team' => $registrations->where('team', 't')->count(),
            'total_contingents' => $registrations->pluck('full_name_kontingen')->unique()->count(),
        ];

        $printedAt = now()->translatedFormat('d F Y, H:i') . ' WIB';

        return view('reports.participants-pdf', compact(
            'registrations',
            'activeEvent',
            'activeContingent',
            'summary',
            'printedAt'
        ));
    }

    /**
     * Format all 12 required fields for participant registration report
     */
    private function formatRegistrationRecord($registration)
    {
        // 1. Full name kontingen
        $registration->full_name_kontingen = $registration->contingent_name;

        // 2. Short name kontingen (same as full name)
        $registration->short_name_kontingen = $registration->contingent_name;

        // 3. Kode negara (default INA)
        $registration->kode_negara = 'INA';

        // 4. First name (Perguruan di participant)
        $registration->first_name = $registration->institusi ?? '';

        // 5. Last name (Nama lengkap participant)
        $registration->last_name = $registration->participant_name;

        // 6. Sex (Kelamin participant)
        $registration->sex = is_object($registration->participant_gender)
            ? $registration->participant_gender->value
            : $registration->participant_gender;

        // 7. Age (Tanggal lahir participant: YYYY-MM-DD)
        $registration->age = $registration->birth_date
            ? \Carbon\Carbon::parse($registration->birth_date)->format('Y-m-d')
            : '-';

        // 8. Kelas (Subkategori / Kelas)
        $typeStr = is_object($registration->type) ? $registration->type->value : $registration->type;
        $className = $registration->class_name ? $registration->class_name . ' - ' : '';

        // 9. Subcategory Gender (category_gender)
        $registration->category_gender = is_object($registration->sub_category_gender)
            ? $registration->sub_category_gender->value
            : $registration->sub_category_gender;

        $subGenderLabel = match ((string) $registration->category_gender) {
            'M' => 'Putra',
            'F' => 'Putri',
            'Mixed' => 'Campuran',
            default => (string) $registration->category_gender,
        };
        $fullSubCatName = $registration->sub_category_name;
        if ($subGenderLabel && ! preg_match('/\b(pria|perempuan|putra|putri|campuran)\b/i', $fullSubCatName)) {
            $fullSubCatName .= " ({$subGenderLabel})";
        }

        $registration->kelas = trim(($typeStr ? $typeStr . ' ' : '') . $className . $fullSubCatName);

        // 10. Team ('t' jika bertim/beregu, else '')
        $registration->team = $registration->category_type === 'beregu' ? 't' : '';

        // 11. Name Team (Nama team jika team = 't', else '')
        $registration->name_team = $registration->team === 't' ? ($registration->team_name ?? '') : '';

        // 12. Min Age (usia termuda = usia dari max_birth_date; min_birth_date = usia tertua)
        $registration->min_age = $registration->max_birth_date
            ? \Carbon\Carbon::parse($registration->max_birth_date)->age
            : '-';

        // 13. Status Berkas (verified / rejected / pending)
        $rawStatus = is_object($registration->status_berkas) ? $registration->status_berkas->value : (string) $registration->status_berkas;
        if ($registration->is_verified || $rawStatus === 'verified') {
            $registration->status_berkas = 'verified';
        } elseif (!empty($registration->rejection_reason) || $rawStatus === 'rejected') {
            $registration->status_berkas = 'rejected';
        } else {
            $registration->status_berkas = 'pending';
        }

        return $registration;
    }
}
