<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Scoreboard;
use App\Models\ScoreboardResultRange;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ScoreboardResultRangeController extends Controller
{
    public function store(Scoreboard $assessment): RedirectResponse
    {
        $assessment->resultRanges()->create([
            'title' => 'New Result Range',
            'sort_order' => ($assessment->resultRanges()->max('sort_order') ?? 0) + 1,
        ]);

        return redirect()
            ->route('admin.scoreboards.builder', $assessment)
            ->with('status', 'scoreboard-result-range-created');
    }

    public function update(Request $request, Scoreboard $assessment, ScoreboardResultRange $resultRange): RedirectResponse
    {
        abort_unless($resultRange->scoreboard_id === $assessment->id, 404);

        $validated = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'min_score' => ['nullable', 'numeric'],
            'max_score' => ['nullable', 'numeric'],
            'sort_order' => ['required', 'integer', 'min:1'],
        ]);

        $resultRange->update($validated);

        return redirect()
            ->route('admin.scoreboards.builder', $assessment)
            ->with('status', 'scoreboard-result-range-saved');
    }

    public function destroy(Scoreboard $assessment, ScoreboardResultRange $resultRange): RedirectResponse
    {
        abort_unless($resultRange->scoreboard_id === $assessment->id, 404);

        $resultRange->delete();

        return redirect()
            ->route('admin.scoreboards.builder', $assessment)
            ->with('status', 'scoreboard-result-range-deleted');
    }
}
