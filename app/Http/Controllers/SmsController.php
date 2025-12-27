<?php

namespace App\Http\Controllers;

use App\Models\User;
use Inertia\Inertia;
use App\Models\Message;
use Illuminate\Http\Request;
use App\Http\Resources\Resource;
use App\Services\MessageService;
use App\Http\Requests\MessageRequest;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;

class SmsController extends Controller
{

    public function dataTable()
    {
        $outbox = QueryBuilder::for(
            Message::with(['contact', 'campaign'])->orderBy('scheduled_at')
        )->allowedFilters([
            AllowedFilter::partial('status'),
        ])->jsonPaginate();


        return Resource::collection($outbox);
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('Admin/Sms/Index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Admin/Sms/Create', [
            'classes' => \App\Models\Rank::all()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(MessageRequest $request)
    {
        $user = auth()->user() ?? User::first();
        $validated = $request->validated();

        // If a group is selected but no specific recipients are provided, we should handle it
        // However, the frontend is expected to load the contacts and populate the recipients field.
        // We'll keep the current logic but ensure we use the authenticated user if available.

        $messageService = new MessageService();
        $result = $messageService->sendMessages(
            $user,
            $validated
        );

        return response()->json([
            'message' => 'Messages queued successfully',
            'message_count' => $result['message_count'],
            'campaign_id' => $result['campaign_id']
        ]);
    }

    /**
     * Get contacts for a specific group
     */
    public function getGroupContacts(Request $request)
    {
        $group = $request->get('group');
        $rankId = $request->get('rank_id');

        $phones = [];

        switch ($group) {
            case 'Parents':
                $phones = \App\Models\Guardian::whereNotNull('phone')
                    ->where('phone', '!=', '')
                    ->pluck('phone')
                    ->unique()
                    ->toArray();
                break;
            case 'Students':
                $phones = \App\Models\Student::whereNotNull('user_id')
                    ->with('user')
                    ->get()
                    ->pluck('user.phone')
                    ->filter()
                    ->unique()
                    ->toArray();
                break;
            case 'Teachers':
                $phones = \App\Models\User::whereRoleIs('teacher')
                    ->whereNotNull('phone')
                    ->where('phone', '!=', '')
                    ->pluck('phone')
                    ->unique()
                    ->toArray();
                break;
            case 'Staff/Admin':
                $phones = \App\Models\User::whereRoleIs(['administrator', 'principal', 'hr-manager', 'academic-coordinator', 'accountant', 'librarian', 'receptionist'])
                    ->whereNotNull('phone')
                    ->where('phone', '!=', '')
                    ->pluck('phone')
                    ->unique()
                    ->toArray();
                break;
            case 'Class Parents':
                if ($rankId) {
                    $phones = \App\Models\Guardian::whereHas('student', function ($q) use ($rankId) {
                        $q->where('rank_id', $rankId);
                    })
                        ->whereNotNull('phone')
                        ->where('phone', '!=', '')
                        ->pluck('phone')
                        ->unique()
                        ->toArray();
                }
                break;
            case 'Class Students':
                if ($rankId) {
                    $phones = \App\Models\Student::where('rank_id', $rankId)
                        ->whereNotNull('user_id')
                        ->with('user')
                        ->get()
                        ->pluck('user.phone')
                        ->filter()
                        ->unique()
                        ->toArray();
                }
                break;
            case 'All Contacts':
                $parentPhones = \App\Models\Guardian::whereNotNull('phone')->where('phone', '!=', '')->pluck('phone')->toArray();

                // Get all users who are NOT students and have a phone number
                $staffPhones = \App\Models\User::whereDoesntHave('student')
                    ->whereNotNull('phone')
                    ->where('phone', '!=', '')
                    ->pluck('phone')
                    ->toArray();

                $phones = array_unique(array_merge($parentPhones, $staffPhones));
                break;
        }

        return response()->json([
            'phones' => array_values($phones),
            'count' => count($phones)
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
