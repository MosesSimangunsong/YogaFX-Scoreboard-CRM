import PublicLayout from '@/Layouts/PublicLayout';
import { Head, Link } from '@inertiajs/react';

export default function PublicHome({ canLogin, featuredScoreboard }) {
    return (
        <PublicLayout canLogin={canLogin}>
            <Head title="YogaFX Scoreboard" />

            <section className="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8 lg:py-28">
                <div className="grid gap-10 lg:grid-cols-[minmax(0,1.15fr)_minmax(320px,0.85fr)] lg:items-center">
                    <div className="max-w-3xl">
                        <div className="inline-flex rounded-full border border-[#d1a15b]/30 bg-[#d1a15b]/10 px-4 py-1.5 text-xs font-semibold uppercase tracking-[0.22em] text-[#f0d5a8]">
                            Standalone Assessment Platform
                        </div>

                        <h1 className="mt-6 text-4xl font-semibold tracking-tight text-white sm:text-5xl lg:text-6xl">
                            Lead qualification, scoring, and result delivery in one public flow.
                        </h1>

                        <p className="mt-6 max-w-2xl text-base leading-8 text-white/70 sm:text-lg">
                            YogaFX Scoreboard is the public-facing assessment engine for participant intake,
                            scoring, segmentation, result presentation, and follow-up.
                        </p>

                        <div className="mt-8 flex flex-wrap gap-4">
                            {featuredScoreboard ? (
                                <Link
                                    href={route('public.scoreboards.lead.show', featuredScoreboard.slug)}
                                    className="inline-flex items-center rounded-lg bg-[#c44b36] px-5 py-3 text-sm font-semibold text-white shadow-[0_18px_40px_rgba(196,75,54,0.24)] transition hover:bg-[#a93d2b]"
                                >
                                    Start Live Scoreboard
                                </Link>
                            ) : (
                                <div className="inline-flex items-center rounded-lg border border-white/10 bg-white/5 px-5 py-3 text-sm font-semibold text-white/45">
                                    No live scoreboards yet
                                </div>
                            )}

                            {canLogin ? (
                                <Link
                                    href={route('dashboard')}
                                    className="inline-flex items-center rounded-lg border border-white/15 px-5 py-3 text-sm font-semibold text-white/85 transition hover:border-white/25 hover:text-white"
                                >
                                    Go to Admin
                                </Link>
                            ) : null}
                        </div>
                    </div>

                    <div className="rounded-[28px] border border-white/10 bg-white/5 p-6 shadow-[0_24px_60px_rgba(0,0,0,0.24)] backdrop-blur-sm">
                        <div className="rounded-[24px] border border-white/10 bg-[#171a20] p-6">
                            <div className="text-xs font-semibold uppercase tracking-[0.2em] text-[#d1a15b]">
                                Product Boundary
                            </div>
                            <div className="mt-4 space-y-4 text-sm text-white/75">
                                <p>Built for lead intake, assessment, scoring, result, and follow-up.</p>
                                <p>Not an LMS, not a lesson flow, and not a certificate system.</p>
                                <p>
                                    {featuredScoreboard
                                        ? `Current live entry: ${featuredScoreboard.title}.`
                                        : 'Publish and activate a scoreboard from admin to open the first live public intake flow.'}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </PublicLayout>
    );
}
