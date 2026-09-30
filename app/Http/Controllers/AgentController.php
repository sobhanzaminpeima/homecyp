<?php

namespace App\Http\Controllers;

use App\Models\Agent;

class AgentController extends Controller
{
    public function index()
    {
        $agents = Agent::active()->with(['user', 'properties'])->get();
        return view('agents.index', compact('agents'));
    }

    public function show(int $id)
    {
        $agent = Agent::active()->with(['user', 'properties.translations'])->findOrFail($id);
        $properties = $agent->properties()->active()->with('translations')->paginate(9);
        return view('agents.show', compact('agent', 'properties'));
    }
}
