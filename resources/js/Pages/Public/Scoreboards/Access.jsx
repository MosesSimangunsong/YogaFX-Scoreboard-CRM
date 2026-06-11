import { Button } from '@/Components/ui/button';
import PublicLayout from '@/Layouts/PublicLayout';
import { Head, Link } from '@inertiajs/react';

export default function PublicScoreboardAccess({
    accessLink,
    participant,
    scoreboard,
    assessment,
}) {
    return (
        <PublicLayout>
            <Head title={`${scoreboard.title} Access`} />

            <section className="mx-auto max-w-5xl px-4 py-16 sm:px-6 lg:px-8 lg:py-20">
                <div className="rounded-[32px] border border-white/10 bg-white/5 p-6 shadow-[0_24px_60px_rgba(0,0,0,0.24)] backdrop-blur-sm sm:p-8">
                    <div className="rounded-[24px] border border-white/10 bg-[#171a20] p-8">
                        <div className="text-xs font-semibold uppercase tracking-[0.22em] text-[#d1a15b]">
                            Unique Access Verified
                        </div>
                        <h1 className="mt-4 text-3xl font-semibold tracking-tight text-white">
                            {participant.first_name ? `${participant.first_name}, your access is ready.` : 'Your access is ready.'}
                        </h1>
                        <p className="mt-4 max-w-2xl text-sm leading-7 text-white/70 sm:text-base">
                            This unique route is now resolved to the correct participant and scoreboard
                            context for <span className="text-white">{scoreboard.title}</span>. The real
                            assessment experience will attach here in the next module.
                        </p>

                        <div className="mt-8 grid gap-4 md:grid-cols-2">
                            <div className="rounded-2xl border border-white/10 bg-black/20 px-5 py-4">
                                <div className="text-[11px] font-semibold uppercase tracking-[0.18em] text-white/45">
                                    Access Code
                                </div>
                                <div className="mt-2 break-all text-sm font-medium text-white">
                                    {accessLink.code}
                                </div>
                            </div>

                            <div className="rounded-2xl border border-white/10 bg-black/20 px-5 py-4">
                                <div className="text-[11px] font-semibold uppercase tracking-[0.18em] text-white/45">
                                    Participant
                                </div>
                                <div className="mt-2 text-sm font-medium text-white">
                                    {[participant.first_name, participant.last_name].filter(Boolean).join(' ')}
                                </div>
                                <div className="mt-1 text-sm text-white/55">{participant.email}</div>
                            </div>
                        </div>

                        <div className="mt-4 rounded-2xl border border-white/10 bg-black/20 px-5 py-4 text-sm leading-7 text-white/70">
                            Access status: <span className="font-medium text-white">{accessLink.status}</span>.
                            {scoreboard.description ? ` ${scoreboard.description}` : ''}
                        </div>

                        <div className="mt-8 flex flex-wrap gap-3">
                            <Button asChild className="bg-[#c44b36] hover:bg-[#a93d2b]">
                                <Link href={assessment.has_submitted_submission ? assessment.completed_url : assessment.start_url}>
                                    {assessment.has_submitted_submission ? 'View Result' : 'Start Assessment'}
                                </Link>
                            </Button>
                        </div>
                    </div>
                </div>
            </section>
        </PublicLayout>
    );
}
