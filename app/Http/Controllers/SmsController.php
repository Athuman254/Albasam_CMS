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
            Message::with(['contact','campaign'])->orderBy('scheduled_at')
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
        return Inertia::render('Admin/Sms/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(MessageRequest $request)
    {
        $user = User::first();
        $validated = $request->validated();
        if($validated['group'] == "Parents"){
            $validated["re"] = "";
        }
        dd($request->validated());
        $messageService = new MessageService();
        $result = $messageService->sendMessages(
            $user,
            $request->validated()
        );
        return response()->json([
            'message' => 'Messages queued successfully',
            'message_count' => $result['message_count'],
            'campaign_id' => $result['campaign_id']
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
