import { Badge } from '@/Components/ui/badge';
import { Button } from '@/Components/ui/button';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';

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

    return 'No answer recorded';
}

export default function ScoreboardResultShow({ scoreboard, result, answers }) {
    return (
        <AuthenticatedLayout
            header={
                <div className="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                    <div>
                        <div className="flex flex-wrap items-center gap-2">
                            <Badge variant="outline">Scoreboards</Badge>
                            <Badge variant="outline">Result Detail</Badge>
                        </div>
                        <h2 className="mt-3 text-2xl font-semibold text-slate-900">
                            {result.name || 'Unnamed Participant'}
                        </h2>
                        <p className="mt-1 text-sm text-slate-500">
                            Full answer review for {scoreboard.title}.
                        </p>
                    </div>
                    <Button asChild variant="outline">
                        <Link href={route('admin.scoreboards.results.index', scoreboard.id)}>
                            Back to Results
                        </Link>
                    </Button>
                </div>
            }
        >
            <Head title={`${scoreboard.title} Result Detail`} />

            <div className="py-12">
                <div className="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
                    <div className="grid gap-6 xl:grid-cols-[minmax(0,1fr)_320px]">
                        <div className="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                            <h3 className="text-lg font-semibold text-slate-900">Question Review</h3>
                            <div className="mt-5 space-y-4">
                                {answers.map((answer) => (
                                    <div
                                        key={answer.id}
                                        className="rounded-2xl border border-slate-200 bg-slate-50 p-5"
                                    >
                                        <div className="flex flex-wrap items-center gap-2">
                                            <Badge variant="outline">{answer.question_type}</Badge>
                                            {answer.is_gradable ? (
                                                <Badge variant={answer.is_correct ? 'secondary' : 'outline'}>
                                                    {answer.is_correct ? 'Correct' : 'Incorrect'}
                                                </Badge>
                                            ) : (
                                                <Badge variant="outline">Not gradable</Badge>
                                            )}
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
                                                <span className="font-semibold text-slate-900">Other text:</span>{' '}
                                                {answer.other_text}
                                            </div>
                                        ) : null}
                                    </div>
                                ))}
                            </div>
                        </div>

                        <div className="space-y-6">
                            <div className="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                                <h3 className="text-lg font-semibold text-slate-900">Summary</h3>
                                <div className="mt-5 space-y-4 text-sm text-slate-600">
                                    <div>
                                        <div className="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">
                                            Name
                                        </div>
                                        <div className="mt-1 font-medium text-slate-900">{result.name || '—'}</div>
                                    </div>
                                    <div>
                                        <div className="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">
                                            Email
                                        </div>
                                        <div className="mt-1 font-medium text-slate-900">{result.email || '—'}</div>
                                    </div>
                                    <div>
                                        <div className="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">
                                            Phone
                                        </div>
                                        <div className="mt-1 font-medium text-slate-900">{result.phone || '—'}</div>
                                    </div>
                                    <div>
                                        <div className="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">
                                            Correct Answers
                                        </div>
                                        <div className="mt-1 font-medium text-slate-900">
                                            {result.gradable_questions_count > 0
                                                ? `${result.correct_answers_count} / ${result.gradable_questions_count}`
                                                : 'Not applicable'}
                                        </div>
                                    </div>
                                    <div>
                                        <div className="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">
                                            Points
                                        </div>
                                        <div className="mt-1 font-medium text-slate-900">
                                            {result.points ?? 'Not scored'}
                                        </div>
                                    </div>
                                    <div>
                                        <div className="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">
                                            Percentage
                                        </div>
                                        <div className="mt-1 font-medium text-slate-900">
                                            {result.percentage === null || result.percentage === undefined
                                                ? 'Not applicable'
                                                : `${result.percentage}%`}
                                        </div>
                                    </div>
                                    <div>
                                        <div className="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">
                                            Time Taken
                                        </div>
                                        <div className="mt-1 font-medium text-slate-900">
                                            {result.time_taken?.display ?? 'Unavailable'}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {(result.result_title || result.result_description) && (
                                <div className="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                                    <h3 className="text-lg font-semibold text-slate-900">Result</h3>
                                    {result.result_title ? (
                                        <div className="mt-4 font-medium text-slate-900">{result.result_title}</div>
                                    ) : null}
                                    {result.result_description ? (
                                        <p className="mt-2 text-sm leading-7 text-slate-600">
                                            {result.result_description}
                                        </p>
                                    ) : null}
                                </div>
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
