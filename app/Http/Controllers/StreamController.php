<?php

namespace App\Http\Controllers;

use App\Http\Resources\Resource;
use App\Models\Stream;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class StreamController extends Controller
{
    public function dataTable()
    {
        $streams = QueryBuilder::for(
            Stream::orderBy('id')
        )->allowedFilters([
            AllowedFilter::partial('name'),
        ])->jsonPaginate();

        return Resource::collection($streams);
    }

    public function index()
    {
        return Inertia::render('admin/Configurations/Streams/Index', []);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'max:255', Rule::unique('streams', 'name')],
            'activated' => ['required','boolean'],
        ]);

        Stream::create([
            'name' => $validated['name'],
            'activated' => $validated['activated'],
        ]);

        return to_route('streams.index')->with('success', 'Stream created.');
    }

    public function update(Stream $stream, Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'max:255'],
            'activated' => ['required','boolean'],
        ]);

        $stream->update([
            'name' => $validated['name'],
            'activated' => $validated['activated'],
        ]);

        return to_route('streams.index')->with('success', 'Stream details updated.');
    }
}
