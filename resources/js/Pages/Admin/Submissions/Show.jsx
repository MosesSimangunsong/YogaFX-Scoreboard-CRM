import { Badge } from '@/Components/ui/badge';
import { Button } from '@/Components/ui/button';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';

function formatLabel(value) {
    return value
        .split('_')
        .map((part) => part.charAt(0).toUpperCase() + part.slice(1))
        .join(' ');
}

function renderAnswerValue(answer) {
    if (answer.selected_options.length > 0) {
        return answer.selected_options.map((option) => option.label).join(', ');
    }

    if (answer.answer_text) {
        return answer.answer_text;
    }

    if (answer.answer_number !== null && answer.answer_number !== undefined) {
        return answer.answer_number;
    }

    return 'No answer payload';
}

export default function SubmissionShow({ submission, categoryScores, answers, delivery }) {
    return (
        <AuthenticatedLayout
            header={
                <div className="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                    <div>
                        <div className="flex flex-wrap items-center gap-2">
                            <Badge variant="outline">Submissions</Badge>
                            <Badge variant="outline">Review</Badge>
                        </div>
                        <h2 className="mt-3 text-2xl font-semibold text-slate-900">
                            Submission #{submission.id}
                        </h2>
                        <p className="mt-1 text-sm text-slate-500">
                            Review participant identity, answers, score, result, tracking, and delivery state.
                        </p>
                    </div>
                    <Button asChild variant="outline">
                        <Link href={route('admin.submissions.index')}>Back to Submissions</Link>
                    </Button>
                </div>
            }
        >
            <Head title={`Submission ${submission.id}`} />

            <div className="py-12">
                <div className="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
                    <div className="grid gap-6 xl:grid-cols-[minmax(0,1fr)_320px]">
                        <div className="space-y-6">
                            <div className="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                                <div className="flex flex-wrap items-center gap-2">
                                    <Badge variant="outline">{submission.status}</Badge>
                                    {submission.result_title ? (
                                        <Badge variant="secondary">{submission.result_title}</Badge>
                                    ) : null}
                                </div>

                                <div className="mt-5 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                                    <div>
                                        <div className="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">
                                            Scoreboard
                                        </div>
                                        <div className="mt-2 text-sm font-medium text-slate-900">
                                            {submission.scoreboard.title}
                                        </div>
                                    </div>
                                    <div>
                                        <div className="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">
                                            Overall Score
                                        </div>
                                        <div className="mt-2 text-sm font-medium text-slate-900">
                                            {submission.overall_score ?? 'Not scored'}
                                        </div>
                                    </div>
                                    <div>
                                        <div className="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">
                                            Answers
                                        </div>
                                        <div className="mt-2 text-sm font-medium text-slate-900">
                                            {submission.answers_count}
                                        </div>
                                    </div>
                                    <div>
                                        <div className="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">
                                            Submitted At
                                        </div>
                                        <div className="mt-2 text-sm font-medium text-slate-900">
                                            {submission.submitted_at || 'Not submitted'}
                                        </div>
                                    </div>
                                </div>

                                {submission.result_description || submission.result_recommendation ? (
                                    <div className="mt-6 rounded-2xl border border-slate-200 bg-slate-50 p-5">
                                        <h3 className="text-sm font-semibold text-slate-900">Result Context</h3>
                                        {submission.result_description ? (
                                            <p className="mt-3 text-sm leading-7 text-slate-600">
                                                {submission.result_description}
                                            </p>
                                        ) : null}
                                        {submission.result_recommendation ? (
                                            <p className="mt-3 text-sm leading-7 text-slate-700">
                                                Recommendation: {submission.result_recommendation}
                                            </p>
                                        ) : null}
                                    </div>
                                ) : null}
                            </div>

                            <div className="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                                <h3 className="text-lg font-semibold text-slate-900">Answers</h3>
                                <div className="mt-5 space-y-4">
                                    {answers.map((answer) => (
                                        <div
                                            key={answer.id}
                                            className="rounded-2xl border border-slate-200 bg-slate-50 p-5"
                                        >
                                            <div className="flex flex-wrap items-center gap-2">
                                                <Badge variant="outline">{formatLabel(answer.question_type)}</Badge>
                                                {answer.scoring_category ? (
                                                    <Badge variant="outline">
                                                        {formatLabel(answer.scoring_category)}
                                                    </Badge>
                                                ) : null}
                                            </div>

                                            <h4 className="mt-3 text-base font-semibold text-slate-900">
                                                {answer.question_title}
                                            </h4>
                                            {answer.question_text ? (
                                                <p className="mt-2 text-sm leading-7 text-slate-600">
                                                    {answer.question_text}
                                                </p>
                                            ) : null}

                                            <div className="mt-4 text-sm text-slate-700">
                                                <span className="font-semibold text-slate-900">Answer:</span>{' '}
                                                {renderAnswerValue(answer)}
                                            </div>

                                            {answer.other_text ? (
                                                <div className="mt-2 text-sm text-slate-700">
                                                    <span className="font-semibold text-slate-900">Other Text:</span>{' '}
                                                    {answer.other_text}
                                                </div>
                                            ) : null}

                                            {answer.selected_options.length > 0 ? (
                                                <div className="mt-3 flex flex-wrap gap-2">
                                                    {answer.selected_options.map((option, index) => (
                                                        <Badge key={`${answer.id}-${index}`} variant="outline">
                                                            {option.label}
                                                            {option.score_value !== null && option.score_value !== undefined
                                                                ? ` (${option.score_value})`
                                                                : ''}
                                                        </Badge>
                                                    ))}
                                                </div>
                                            ) : null}
                                        </div>
                                    ))}
                                </div>
                            </div>
                        </div>

                        <div className="space-y-6">
                            <div className="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                                <h3 className="text-lg font-semibold text-slate-900">Participant</h3>
                                <div className="mt-5 space-y-4 text-sm text-slate-600">
                                    <div>
                                        <div className="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">
                                            Name
                                        </div>
                                        <div className="mt-1 font-medium text-slate-900">
                                            {[submission.participant.first_name, submission.participant.last_name]
                                                .filter(Boolean)
                                                .join(' ') || 'Unnamed Participant'}
                                        </div>
                                    </div>
                                    <div>
                                        <div className="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">
                                            Email
                                        </div>
                                        <div className="mt-1 font-medium text-slate-900">
                                            {submission.participant.email || 'No email'}
                                        </div>
                                    </div>
                                    <div>
                                        <div className="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">
                                            WhatsApp
                                        </div>
                                        <div className="mt-1 font-medium text-slate-900">
                                            {submission.participant.whatsapp || 'No phone'}
                                        </div>
                                    </div>
                                    <div>
                                        <div className="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">
                                            Source
                                        </div>
                                        <div className="mt-1 font-medium text-slate-900">
                                            {submission.participant.source_type || 'Unknown'}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div className="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                                <h3 className="text-lg font-semibold text-slate-900">Category Scores</h3>
                                <div className="mt-5 space-y-3">
                                    {categoryScores.length > 0 ? (
                                        categoryScores.map((item) => (
                                            <div
                                                key={item.category_key}
                                                className="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3"
                                            >
                                                <div className="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">
                                                    {formatLabel(item.category_key)}
                                                </div>
                                                <div className="mt-1 text-sm font-medium text-slate-900">
                                                    {item.score} · {item.answered_questions_count} question
                                                    {item.answered_questions_count === 1 ? '' : 's'}
                                                </div>
                                            </div>
                                        ))
                                    ) : (
                                        <div className="rounded-xl border border-dashed border-slate-300 bg-slate-50 px-4 py-5 text-sm text-slate-500">
                                            No category score breakdown stored for this submission.
                                        </div>
                                    )}
                                </div>
                            </div>

                            <div className="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                                <h3 className="text-lg font-semibold text-slate-900">Tracking</h3>
                                <div className="mt-5 space-y-3">
                                    {Object.keys(submission.tracking_payload || {}).length > 0 ? (
                                        Object.entries(submission.tracking_payload).map(([key, value]) => (
                                            <div key={key}>
                                                <div className="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">
                                                    {formatLabel(key)}
                                                </div>
                                                <div className="mt-1 text-sm font-medium text-slate-900">
                                                    {value || '—'}
                                                </div>
                                            </div>
                                        ))
                                    ) : (
                                        <div className="rounded-xl border border-dashed border-slate-300 bg-slate-50 px-4 py-5 text-sm text-slate-500">
                                            No tracking payload was stored for this submission.
                                        </div>
                                    )}
                                </div>
                            </div>

                            <div className="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                                <h3 className="text-lg font-semibold text-slate-900">Delivery State</h3>
                                <div className="mt-5 space-y-4">
                                    <div>
                                        <div className="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">
                                            PDF Reports
                                        </div>
                                        <div className="mt-3 space-y-3">
                                            {delivery.pdf_reports.length > 0 ? (
                                                delivery.pdf_reports.map((report) => (
                                                    <div
                                                        key={report.id}
                                                        className="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700"
                                                    >
                                                        <div className="font-medium text-slate-900">
                                                            {report.file_name || `Report #${report.id}`}
                                                        </div>
                                                        <div className="mt-1">
                                                            Status: {report.status}
                                                        </div>
                                                        {report.generation_error ? (
                                                            <div className="mt-1 text-rose-600">
                                                                {report.generation_error}
                                                            </div>
                                                        ) : null}
                                                    </div>
                                                ))
                                            ) : (
                                                <div className="rounded-xl border border-dashed border-slate-300 bg-slate-50 px-4 py-5 text-sm text-slate-500">
                                                    No PDF reports recorded yet.
                                                </div>
                                            )}
                                        </div>
                                    </div>

                                    <div>
                                        <div className="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">
                                            Email Logs
                                        </div>
                                        <div className="mt-3 space-y-3">
                                            {delivery.email_logs.length > 0 ? (
                                                delivery.email_logs.map((emailLog) => (
                                                    <div
                                                        key={emailLog.id}
                                                        className="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700"
                                                    >
                                                        <div className="font-medium text-slate-900">
                                                            {emailLog.recipient_email}
                                                        </div>
                                                        <div className="mt-1">Status: {emailLog.status}</div>
                                                        <div className="mt-1">Mailer: {emailLog.mailer || 'default'}</div>
                                                        {emailLog.error_message ? (
                                                            <div className="mt-1 text-rose-600">
                                                                {emailLog.error_message}
                                                            </div>
                                                        ) : null}
                                                    </div>
                                                ))
                                            ) : (
                                                <div className="rounded-xl border border-dashed border-slate-300 bg-slate-50 px-4 py-5 text-sm text-slate-500">
                                                    No email logs recorded yet.
                                                </div>
                                            )}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
