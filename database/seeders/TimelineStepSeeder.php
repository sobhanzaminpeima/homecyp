<?php

namespace Database\Seeders;

use App\Models\TimelineStep;
use Illuminate\Database\Seeder;

class TimelineStepSeeder extends Seeder
{
    public function run(): void
    {
        // Default global journey (project_id null); admin can add project-specific
        // overrides later per spec section 5.8.
        $steps = [
            ['key' => 'choose_area', 'label' => 'Choose Area', 'description' => 'Decide which region of North Cyprus fits your budget and lifestyle.', 'next_action' => 'Tell the AI your priorities (beach, schools, ROI, quiet) to get an area match.', 'sort_order' => 1],
            ['key' => 'choose_property', 'label' => 'Choose Property', 'description' => 'Shortlist specific properties or projects that match your budget and requirements.', 'next_action' => 'Ask to see matching properties or book a viewing.', 'sort_order' => 2],
            ['key' => 'reserve', 'label' => 'Reserve', 'description' => 'Pay a reservation deposit to take the property off the market while contracts are prepared.', 'next_action' => 'Contact your agent to confirm the reservation deposit amount.', 'sort_order' => 3],
            ['key' => 'contract', 'label' => 'Contract', 'description' => 'Sign the sales contract and register it at the Land Registry Office.', 'next_action' => 'Your agent/lawyer will guide contract signing and registration.', 'sort_order' => 4],
            ['key' => 'title_deed', 'label' => 'Title Deed', 'description' => 'Transfer of the title deed (Kocan) once payment terms are met and permits are approved.', 'next_action' => 'Track permit approval status with your lawyer.', 'sort_order' => 5],
            ['key' => 'residency', 'label' => 'Residency', 'description' => 'Apply for a residence permit using your title deed / contract of sale as supporting proof.', 'next_action' => 'Ask the AI about the Residency Advisor tool for required documents.', 'sort_order' => 6],
        ];

        foreach ($steps as $step) {
            TimelineStep::updateOrCreate(
                ['project_id' => null, 'key' => $step['key']],
                $step
            );
        }
    }
}
