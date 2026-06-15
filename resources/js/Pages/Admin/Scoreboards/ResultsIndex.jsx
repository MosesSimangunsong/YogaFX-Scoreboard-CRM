import { Badge } from '@/Components/ui/badge';
import { Button } from '@/Components/ui/button';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';

export default function ScoreboardResultsIndex({ scoreboard, results, status }) {
    return (
        <AuthenticatedLayout
            header={
                <div className="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                    <div>
                        <div className="flex flex-wrap items-center gap-2">
                            <Badge variant="outline">Scoreboards</Badge>
                            <Badge variant="outline">Results</Badge>
                        </div>
                        <h2 className="mt-3 text-2xl font-semibold text-slate-900">
                            {scoreboard.title} Results
                        </h2>
                        <p className="mt-1 text-sm text-slate-500">
                            Completed scoreboard attempts, scoped directly to this assessment.
                        </p>
                    </div>
                    <Button asChild variant="outline">
                        <Link href={route('admin.scoreboards.index')}>Back to Scoreboards</Link>
                    </Button>
                </div>
            }
        >
            <Head title={`${scoreboard.title} Results`} />

            <div className="py-12">
                <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    {status ? (
                        <div className="mb-6 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">
                            {status === 'scoreboard-result-deleted'
                                ? 'Scoreboard result has been deleted.'
                                : status}
                        </div>
                    ) : null}
                    <div className="rounded-2xl border border-slate-200 bg-white shadow-sm">
                        {results.length > 0 ? (
                            <div className="divide-y divide-slate-200">
                                {results.map((result) => (
                                    <div
                                        key={result.id}
                                        className="flex flex-col gap-5 p-5 lg:flex-row lg:items-center lg:justify-between"
                                    >
                                        <div className="min-w-0 flex-1">
                                            <div className="text-lg font-semibold text-slate-900">
                                                {result.name || 'Unnamed Participant'}
                                            </div>
                                            <div className="mt-1 text-sm text-slate-500">
                                                {result.email || 'No email'}
                                            </div>
                                            <div className="mt-3 flex flex-wrap gap-2">
                                                <Badge variant="outline">
                                                    Points: {result.points ?? 'Not scored'}
                                                </Badge>
                                                {result.correct_answers_label ? (
                                                    <Badge variant="outline">
                                                        Correct: {result.correct_answers_label}
                                                    </Badge>
                                                ) : null}
                                                <Badge variant="outline">
                                                    Percentage:{' '}
                                                    {result.percentage === null || result.percentage === undefined
                                                        ? 'N/A'
                                                        : `${result.percentage}%`}
                                                </Badge>
                                                <Badge variant="outline">
                                                    Completed {result.completed_at ?? 'recently'}
                                                </Badge>
                                            </div>
                                        </div>

                                        <div className="flex flex-wrap gap-3">
                                            <Button asChild>
                                                <Link
                                                    href={route('admin.scoreboards.results.show', {
                                                        assessment: scoreboard.id,
                                                        submission: result.id,
                                                    })}
                                                >
                                                    View
                                                </Link>
                                            </Button>
                                            <Button variant="destructive" asChild>
                                                <Link
                                                    href={route('admin.scoreboards.results.destroy', {
                                                        assessment: scoreboard.id,
                                                        submission: result.id,
                                                    })}
                                                    method="delete"
                                                    as="button"
                                                >
                                                    Delete
                                                </Link>
                                            </Button>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        ) : (
                            <div className="px-6 py-16 text-center">
                                <h3 className="text-lg font-semibold text-slate-900">
                                    No completed results yet
                                </h3>
                                <p className="mt-2 text-sm text-slate-500">
                                    This scoreboard does not have any completed participant results yet.
                                </p>
                            </div>
                        )}
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
