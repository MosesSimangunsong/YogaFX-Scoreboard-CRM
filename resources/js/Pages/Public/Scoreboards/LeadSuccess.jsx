import { Button } from '@/Components/ui/button';
import PublicLayout from '@/Layouts/PublicLayout';
import { Head, Link } from '@inertiajs/react';

export default function PublicScoreboardLeadSuccess({
    scoreboard,
    participantFirstName,
    accessLink,
}) {
    return (
        <PublicLayout>
            <Head title="Lead Captured" />

            <section className="mx-auto max-w-5xl px-4 py-16 sm:px-6 lg:px-8 lg:py-20">
                <div className="rounded-[32px] border border-white/10 bg-white/5 p-6 shadow-[0_24px_60px_rgba(0,0,0,0.24)] backdrop-blur-sm sm:p-8">
                    <div className="rounded-[24px] border border-white/10 bg-[#171a20] p-8">
                        <div className="text-xs font-semibold uppercase tracking-[0.22em] text-[#d1a15b]">
                            Lead Captured
                        </div>
                        <h1 className="mt-4 text-3xl font-semibold tracking-tight text-white">
                            {participantFirstName
                                ? `Thanks, ${participantFirstName}.`
                                : 'Thanks for sharing your details.'}
                        </h1>
                        <p className="mt-4 max-w-2xl text-sm leading-7 text-white/70 sm:text-base">
                            Your participant record for {scoreboard.title} has been created and your unique
                            access route is ready. The participant assessment flow will attach to this link
                            in the next module.
                        </p>

                        {accessLink ? (
                            <div className="mt-8 rounded-2xl border border-white/10 bg-black/20 px-5 py-4">
                                <div className="text-[11px] font-semibold uppercase tracking-[0.18em] text-white/45">
                                    Unique Access Code
                                </div>
                                <div className="mt-2 break-all text-sm text-white/85">{accessLink.code}</div>
                            </div>
                        ) : null}

                        <div className="mt-8 flex flex-wrap gap-3">
                            {accessLink ? (
                                <Button asChild className="bg-[#c44b36] hover:bg-[#a93d2b]">
                                    <Link href={accessLink.url}>Open Unique Access Link</Link>
                                </Button>
                            ) : (
                                <Button asChild className="bg-[#c44b36] hover:bg-[#a93d2b]">
                                    <Link href={route('public.scoreboards.lead.show', scoreboard.slug)}>
                                        Back to Lead Form
                                    </Link>
                                </Button>
                            )}
                            <Button asChild variant="outline" className="border-white/10 bg-white/5 text-white hover:bg-white/10 hover:text-white">
                                <Link href={route('public.home')}>
                                    Return Home
                                </Link>
                            </Button>
                        </div>
                    </div>
                </div>
            </section>
        </PublicLayout>
    );
}
