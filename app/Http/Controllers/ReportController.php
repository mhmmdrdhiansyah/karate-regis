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
        $payments = Payment::with(['contingent', 'event'])->orderBy('created_at', 'desc')->paginate(20)->withQueryString();

        return view('reports.index', compact('payments'));
    }

    public function participants(Request $request)
    {
        // Build query with all necessary joins
        $query = Registration::with(['participant.contingent', 'subCategory.eventCategory.event', 'teamGroup'])
            ->whereNull('deleted_at')           // Exclude soft-deleted registrations
            ->whereNotNull('sub_category_id')   // Exclude coach registrations
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
                'event_categories.name as event_category_name',
                'event_categories.min_birth_date',
                'events.name as event_name',
                'team_groups.team_name',
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
            $query->where('registrations.status_berkas', $request->status_berkas);
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
            'M' => 'Laki-laki',
            'F' => 'Perempuan',
            'Mixed' => 'Campuran',
        ];
        $statusBerkasOptions = [
            'pending' => 'Pending',
            'verified' => 'Verified',
            'rejected' => 'Rejected',
        ];

        // Calculate ages for each registration
        $registrations->getCollection()->transform(function ($registration) {
            // Participant age
            $registration->age = $registration->participant->birth_date
                ? \Carbon\Carbon::parse($registration->participant->birth_date)->age
                : '-';

            // Min age from event category
            $registration->min_age = $registration->subCategory->eventCategory->min_birth_date
                ? \Carbon\Carbon::parse($registration->subCategory->eventCategory->min_birth_date)->age
                : '-';

            // Kelas: event_category.name - sub_category.name
            $registration->kelas = $registration->subCategory->eventCategory->name . ' - ' . $registration->subCategory->name;

            // Team: "t" if beregu, else ""
            $registration->team = $registration->subCategory->category_type === 'beregu' ? 't' : '';

            return $registration;
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
}
