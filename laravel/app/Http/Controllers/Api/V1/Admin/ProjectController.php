<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\BusinessProjectResource;
use App\Models\BusinessProject;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $query = BusinessProject::query()->with(['business']);

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        return BusinessProjectResource::collection($query->latest()->paginate($request->integer('per_page', 20)));
    }

    public function update(Request $request, BusinessProject $project)
    {
        $data = $request->validate([
            'status' => ['sometimes', Rule::in(['pending', 'approved', 'rejected'])],
            'is_featured' => ['sometimes', 'boolean'],
        ]);

        $project->update($data);

        return new BusinessProjectResource($project->load('business'));
    }

    public function destroy(BusinessProject $project)
    {
        $project->delete();

        return response()->json(['message' => 'Project deleted.']);
    }
}
