import { Button } from '@/Components/ui/button';
import PublicLayout from '@/Layouts/PublicLayout';
import { Head, Link } from '@inertiajs/react';

function formatCategoryKey(value) {
    return value
        .split('_')
        .map((part) => part.charAt(0).toUpperCase() + part.slice(1))
        .join(' ');
}

export default function PublicScoreboardResult({
    accessLink,
    scoreboard,
    participant,
    submission,
    result,
    categoryScores,
    display,
    delivery,
}) {
    return (
        <PublicLayout>
            <Head title={`${scoreboard.title} Result`} />

            <section className="mx-auto max-w-6xl px-4 py-16 sm:px-6 lg:px-8 lg:py-20">
                <div className="grid gap-6 xl:grid-cols-[320px_minmax(0,1fr)]">
                    <aside className="rounded-[28px] border border-white/10 bg-white/5 p-6 shadow-[0_24px_60px_rgba(0,0,0,0.24)] backdrop-blur-sm xl:sticky xl:top-6 xl:h-fit">
                        <div className="text-xs font-semibold uppercase tracking-[0.22em] text-[#d1a15b]">
                            Result Summary
                        </div>
                        <h1 className="mt-4 text-2xl font-semibold tracking-tight text-white">
                            {participant.first_name
                                ? `${participant.first_name}, your result is ready.`
                                : 'Your result is ready.'}
                        </h1>
                        <p className="mt-3 text-sm leading-7 text-white/65">
                            This result is generated from your saved submission for {scoreboard.title}.
                        </p>

                        <div className="mt-6 grid gap-4">
                            {display.show_score ? (
                                <div className="rounded-2xl border border-white/10 bg-black/20 px-5 py-4">
                                    <div className="text-[11px] font-semibold uppercase tracking-[0.18em] text-white/45">
                                        Overall Score
                                    </div>
                                    <div className="mt-2 text-3xl font-semibold text-white">
                                        {submission.overall_score ?? 0}
                                    </div>
                                </div>
                            ) : null}

                            <div className="rounded-2xl border border-white/10 bg-black/20 px-5 py-4">
                                <div className="text-[11px] font-semibold uppercase tracking-[0.18em] text-white/45">
                                    Correct Answers
                                </div>
                                <div className="mt-2 text-sm font-medium text-white">
                                    {submission.gradable_questions_count > 0
                                        ? `${submission.correct_answers_count} / ${submission.gradable_questions_count}`
                                        : 'Not applicable'}
                                </div>
                            </div>

                            <div className="rounded-2xl border border-white/10 bg-black/20 px-5 py-4">
                                <div className="text-[11px] font-semibold uppercase tracking-[0.18em] text-white/45">
                                    Percentage
                                </div>
                                <div className="mt-2 text-sm font-medium text-white">
                                    {submission.percentage === null || submission.percentage === undefined
                                        ? 'Not applicable'
                                        : `${submission.percentage}%`}
                                </div>
                            </div>

                            <div className="rounded-2xl border border-white/10 bg-black/20 px-5 py-4">
                                <div className="text-[11px] font-semibold uppercase tracking-[0.18em] text-white/45">
                                    Time Taken
                                </div>
                                <div className="mt-2 text-sm font-medium text-white">
                                    {submission.time_taken?.display ?? 'Unavailable'}
                                </div>
                            </div>

                            <div className="rounded-2xl border border-white/10 bg-black/20 px-5 py-4">
                                <div className="text-[11px] font-semibold uppercase tracking-[0.18em] text-white/45">
                                    Email Delivery
                                </div>
                                <div className="mt-2 text-sm font-medium text-white">
                                    {delivery.email_log?.status ?? 'not_queued'}
                                </div>
                                <div className="mt-1 text-sm text-white/55">
                                    {delivery.email_log?.recipient_email ?? 'No recipient recorded'}
                                </div>
                            </div>
                        </div>
                    </aside>

                    <div className="rounded-[32px] border border-white/10 bg-white/5 p-6 shadow-[0_24px_60px_rgba(0,0,0,0.24)] backdrop-blur-sm sm:p-8">
                        <div className="rounded-[24px] border border-white/10 bg-[#171a20] p-8">
                            <div className="text-xs font-semibold uppercase tracking-[0.22em] text-[#d1a15b]">
                                Assessment Result
                            </div>

                            {display.show_range && result.title ? (
                                <div className="mt-5 rounded-[28px] border border-[#d1a15b]/20 bg-[#d1a15b]/10 p-6">
                                    <div className="text-xs font-semibold uppercase tracking-[0.18em] text-[#f0d5a8]">
                                        Matched Result Range
                                    </div>
                                    <h2 className="mt-3 text-3xl font-semibold tracking-tight text-white">
                                        {result.title}
                                    </h2>
                                    {result.description ? (
                                        <p className="mt-4 text-sm leading-7 text-white/75">
                                            {result.description}
                                        </p>
                                    ) : null}
                                    {result.recommendation ? (
                                        <p className="mt-4 text-sm leading-7 text-[#f0d5a8]">
                                            {result.recommendation}
                                        </p>
                                    ) : null}
                                </div>
                            ) : null}

                            {display.show_range && !result.title ? (
                                <div className="mt-5 rounded-[28px] border border-white/10 bg-black/20 p-6 text-sm leading-7 text-white/70">
                                    No result range is configured for this score yet, so the system is showing the
                                    score output without a segmentation label.
                                </div>
                            ) : null}

                            {categoryScores.length > 0 ? (
                                <div className="mt-8">
                                    <h3 className="text-lg font-semibold text-white">Category Breakdown</h3>
                                    <div className="mt-4 grid gap-4 md:grid-cols-2">
                                        {categoryScores.map((item) => (
                                            <div
                                                key={item.category_key}
                                                className="rounded-2xl border border-white/10 bg-black/20 px-5 py-4"
                                            >
                                                <div className="text-[11px] font-semibold uppercase tracking-[0.18em] text-white/45">
                                                    {formatCategoryKey(item.category_key)}
                                                </div>
                                                <div className="mt-2 text-2xl font-semibold text-white">
                                                    {item.score}
                                                </div>
                                                <div className="mt-1 text-sm text-white/55">
                                                    {item.answered_questions_count} scored question
                                                    {item.answered_questions_count === 1 ? '' : 's'}
                                                </div>
                                            </div>
                                        ))}
                                    </div>
                                </div>
                            ) : null}

                            <div className="mt-8 grid gap-4 md:grid-cols-2">
                                <div className="rounded-2xl border border-white/10 bg-black/20 px-5 py-4">
                                    <div className="text-[11px] font-semibold uppercase tracking-[0.18em] text-white/45">
                                        PDF Report
                                    </div>
                                    <div className="mt-2 text-sm font-medium text-white">
                                        {delivery.pdf_report?.status ?? 'not_generated'}
                                    </div>
                                    <div className="mt-1 text-sm text-white/55">
                                        {delivery.pdf_report?.file_name ?? 'No file recorded yet'}
                                    </div>
                                </div>

                                <div className="rounded-2xl border border-white/10 bg-black/20 px-5 py-4">
                                    <div className="text-[11px] font-semibold uppercase tracking-[0.18em] text-white/45">
                                        Submission Status
                                    </div>
                                    <div className="mt-2 text-sm font-medium text-white">{submission.status}</div>
                                    <div className="mt-1 text-sm text-white/55">
                                        {submission.answers_count} answers saved
                                    </div>
                                </div>
                            </div>

                            <div className="mt-8 flex flex-wrap gap-3">
                                <Button asChild className="bg-[#c44b36] hover:bg-[#a93d2b]">
                                    <Link href={route('public.scoreboards.access', accessLink.code)}>
                                        Return to Access Page
                                    </Link>
                                </Button>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </PublicLayout>
    );
}
