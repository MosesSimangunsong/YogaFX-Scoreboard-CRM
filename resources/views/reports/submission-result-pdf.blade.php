YogaFX Scoreboard Result Report
==============================

Scoreboard: {{ $scoreboard->title }}
Participant: {{ trim(($participant->first_name ?? '').' '.($participant->last_name ?? '')) }}
Email: {{ $participant->email }}
Submission ID: {{ $submission->id }}
Submitted At: {{ optional($submission->submitted_at)->format('Y-m-d H:i:s') }}

Overall Score
-------------
{{ $submission->overall_score ?? 'N/A' }}

Result Range
------------
{{ $submission->result_title ?: 'No matched range' }}
@if($submission->result_description)
{{ $submission->result_description }}
@endif
@if($submission->result_recommendation)
Recommendation: {{ $submission->result_recommendation }}
@endif

Category Breakdown
------------------
@forelse($categoryScores as $item)
{{ $item->category_key }}: {{ $item->score }} ({{ $item->answered_questions_count }} questions)
@empty
No category scores stored.
@endforelse
