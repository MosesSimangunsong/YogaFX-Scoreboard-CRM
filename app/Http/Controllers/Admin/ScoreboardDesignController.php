<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Scoreboard;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ScoreboardDesignController extends Controller
{
    public function update(Request $request, Scoreboard $scoreboard): RedirectResponse
    {
        $validated = $request->validate([
            'logo' => ['nullable', 'image', 'max:5120'],
            'logo_max_width' => ['nullable', 'string', 'max:255'],
            'logo_alignment' => ['nullable', 'string', 'max:255'],
            'logo_link' => ['nullable', 'string', 'max:255'],
            'header_position' => ['nullable', 'string', 'max:255'],
            'section_background' => ['nullable', 'string'],
            'top_margin' => ['nullable', 'integer', 'min:0'],
            'bottom_margin' => ['nullable', 'integer', 'min:0'],
            'footer_content' => ['nullable', 'string'],
        ]);

        if ($request->hasFile('logo')) {
            if ($scoreboard->logo_path) {
                Storage::disk('public')->delete($scoreboard->logo_path);
            }

            $validated['logo_path'] = $request->file('logo')->store('scoreboards/logos', 'public');
        }

        unset($validated['logo']);

        $scoreboard->update($validated);

        return redirect()
            ->route('admin.scoreboards.builder', $scoreboard)
            ->with('status', 'scoreboard-design-saved');
    }
}
