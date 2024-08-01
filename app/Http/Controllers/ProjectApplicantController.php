<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProjectApplicant;
use Illuminate\Support\Facades\DB;

class ProjectApplicantController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(ProjectApplicant $projectApplicant)
    {
        if ($projectApplicant->project->client_id != auth()->id()) {
            abort(403, 'You are not authorized to see this page');
        }

        return view('admin.projects.applicant_details', compact('projectApplicant'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ProjectApplicant $projectApplicant)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ProjectApplicant $projectApplicant)
    {
        DB::transaction(function () use ($projectApplicant) {
            $projectApplicant->update([
                'status' => 'Hired',
            ]);

            ProjectApplicant::where('project_id', $projectApplicant->project_id)
                ->where('id', '!=', $projectApplicant->id)
                ->where('status', '=', 'Waiting')
                ->update([
                    'status' => 'Rejected',
                ]);
            
            $projectApplicant->project->update([
                'has_started' => true,
            ]);
        });

        return redirect()->route('admin.projects.show', [$projectApplicant->project, $projectApplicant->id]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ProjectApplicant $projectApplicant)
    {
        //
    }
}
