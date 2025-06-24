<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Resources\Resource;
use App\Models\Stream;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class StreamController extends Controller
{
    public function dataTable()
    {
        $streams = QueryBuilder::for(
            Stream::orderBy('id')
        )->allowedFilters([
            AllowedFilter::exact('id'),
            AllowedFilter::exact('activated'),
            AllowedFilter::partial('name'),
        ])->jsonPaginate();

        return Resource::collection($streams);
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

        return back(303)->with('success', 'Stream created.');
    }

    public function update(Stream $stream, Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'max:255', Rule::unique('streams', 'name')->ignore($stream->id)],
            'activated' => ['required','boolean'],
        ]);

        $stream->update([
            'name' => $validated['name'],
            'activated' => $validated['activated'],
        ]);

        return back(303)->with('success', 'Stream details updated.');
    }
}
