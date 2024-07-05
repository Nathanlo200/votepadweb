<?php

namespace App\Http\Controllers;

use App\Models\Vote;
use App\Models\Phase;
use App\Http\Requests\StoreVoteRequest;
use App\Http\Requests\UpdateVoteRequest;

class VoteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $phases = Phase::latest()->paginate(10);
        return view("votes.index",compact('phases'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreVoteRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show( $vote)
    {
       $phase_id= Phase::where('slug',$vote)->first()->id;
       $phaseAndSpeaker = Phase::with('intervenants')->findOrFail($phase_id);
        // return response()->json($phaseAndSpeaker);
        return view("votes.show",compact('phaseAndSpeaker'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Vote $vote)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateVoteRequest $request, Vote $vote)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Vote $vote)
    {
        //
    }
}
