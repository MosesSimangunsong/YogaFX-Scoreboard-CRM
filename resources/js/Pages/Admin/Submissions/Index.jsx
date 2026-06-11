import { Badge } from '@/Components/ui/badge';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import { useState } from 'react';

function buildBadgeVariant(status) {
    if (['sent', 'generated', 'submitted', 'published', 'active'].includes(status)) {
        return 'secondary';
    }

    if (['failed', 'archived'].includes(status)) {
        return 'destructive';
    }

    return 'outline';
}

export default function SubmissionsIndex({ submissions, scoreboards, filters }) {
    const [search, setSearch] = useState(filters.search ?? '');
    const [scoreboardId, setScoreboardId] = useState(filters.scoreboard_id ?? '');
    const [status, setStatus] = useState(filters.status ?? '');

    const applyFilters = (event) => {
        event.preventDefault();

        router.get(
            route('admin.submissions.index'),
            {
                search,
                scoreboard_id: scoreboardId || undefined,
                status: status || undefined,
            },
            {
                preserveState: true,
                preserveScroll: true,
            },
        );
    };

    return (
        <AuthenticatedLayout
            header={
                <div className="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                    <div>
                        <h2 className="text-2xl font-semibold text-slate-900">Submissions</h2>
                        <p className="mt-1 text-sm text-slate-500">
                            Review participant attempts, scores, answers, delivery state, and tracking context.
                        </p>
                    </div>
                </div>
            }
        >
            <Head title="Submissions" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
                    <form
                        onSubmit={applyFilters}
                        className="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"
                    >
                        <div className="grid gap-4 lg:grid-cols-[minmax(0,1.4fr)_220px_180px_auto]">
                            <Input
                                value={search}
                                onChange={(event) => setSearch(event.target.value)}
                                placeholder="Search participant, email, phone, scoreboard, or result"
                            />

                            <select
                                value={scoreboardId}
                                onChange={(event) => setScoreboardId(event.target.value)}
                                className="h-10 rounded-md border border-slate-200 bg-white px-3 text-sm text-slate-700 shadow-sm focus:border-[#203529] focus:outline-none focus:ring-2 focus:ring-[#203529]/15"
                            >
                                <option value="">All Scoreboards</option>
                                {scoreboards.map((scoreboard) => (
                                    <option key={scoreboard.id} value={scoreboard.id}>
                                        {scoreboard.title}
                                    </option>
                                ))}
                            </select>

                            <select
                                value={status}
                                onChange={(event) => setStatus(event.target.value)}
                                className="h-10 rounded-md border border-slate-200 bg-white px-3 text-sm text-slate-700 shadow-sm focus:border-[#203529] focus:outline-none focus:ring-2 focus:ring-[#203529]/15"
                            >
                                <option value="">All Statuses</option>
                                <option value="in_progress">In Progress</option>
                                <option value="submitted">Submitted</option>
                            </select>

                            <div className="flex gap-3">
                                <Button type="submit">Apply Filters</Button>
                                <Button
                                    type="button"
                                    variant="outline"
                                    onClick={() => {
                                        setSearch('');
                                        setScoreboardId('');
                                        setStatus('');
                                        router.get(route('admin.submissions.index'));
                                    }}
                                >
                                    Reset
                                </Button>
                            </div>
                        </div>
                    </form>

                    <div className="space-y-4">
                        {submissions.data.map((submission) => (
                            <div
                                key={submission.id}
                                className="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"
                            >
                                <div className="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
                                    <div className="min-w-0 flex-1">
                                        <div className="flex flex-wrap items-center gap-2">
                                            <Badge variant={buildBadgeVariant(submission.status)}>
                                                {submission.status}
                                            </Badge>
                                            {submission.delivery.pdf_status ? (
                                                <Badge variant={buildBadgeVariant(submission.delivery.pdf_status)}>
                                                    PDF {submission.delivery.pdf_status}
                                                </Badge>
                                            ) : null}
                                            {submission.delivery.email_status ? (
                                                <Badge variant={buildBadgeVariant(submission.delivery.email_status)}>
                                                    Email {submission.delivery.email_status}
                                                </Badge>
                                            ) : null}
                                            {submission.tracking.has_tracking ? (
                                                <Badge variant="outline">Tracking Captured</Badge>
                                            ) : null}
                                        </div>

                                        <h3 className="mt-3 text-lg font-semibold text-slate-900">
                                            {submission.participant.full_name || 'Unnamed Participant'}
                                        </h3>
                                        <div className="mt-1 text-sm text-slate-500">
                                            {submission.participant.email || 'No email'} · {submission.scoreboard.title}
                                        </div>

                                        <div className="mt-4 grid gap-3 text-sm text-slate-600 sm:grid-cols-2 lg:grid-cols-4">
                                            <div>
                                                <div className="font-semibold text-slate-900">Overall Score</div>
                                                <div>{submission.overall_score ?? 'Not scored'}</div>
                                            </div>
                                            <div>
                                                <div className="font-semibold text-slate-900">Result</div>
                                                <div>{submission.result_title || 'No matched range'}</div>
                                            </div>
                                            <div>
                                                <div className="font-semibold text-slate-900">Answers</div>
                                                <div>{submission.answers_count}</div>
                                            </div>
                                            <div>
                                                <div className="font-semibold text-slate-900">Submitted</div>
                                                <div>{submission.submitted_at || 'Not yet'}</div>
                                            </div>
                                        </div>
                                    </div>

                                    <div className="flex shrink-0 flex-wrap gap-3">
                                        <Button asChild>
                                            <Link href={route('admin.submissions.show', submission.id)}>
                                                Review Submission
                                            </Link>
                                        </Button>
                                    </div>
                                </div>
                            </div>
                        ))}
                    </div>

                    {submissions.data.length === 0 ? (
                        <div className="rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-14 text-center">
                            <h3 className="text-lg font-semibold text-slate-900">No submissions found</h3>
                            <p className="mt-2 text-sm text-slate-500">
                                Try adjusting the filters or complete a participant flow first.
                            </p>
                        </div>
                    ) : null}
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
