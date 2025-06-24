<?php

namespace App\Http\Controllers;

use App\Http\Resources\Resource;
use App\Models\EmergencyContact;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class EmergencyContactController extends Controller
{
    public function dataTable()
    {
        $emergencyContacts = QueryBuilder::for(
            EmergencyContact::with('relationship')->orderBy('id')
        )->allowedFilters([
            AllowedFilter::exact('id'),
            AllowedFilter::exact('employee_id'),
        ])->jsonPaginate();

        return Resource::collection($emergencyContacts);
    }
}
