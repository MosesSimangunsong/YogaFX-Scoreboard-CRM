import ApplicationLogo from '@/Components/ApplicationLogo';
import { Link } from '@inertiajs/react';

export default function PublicLayout({ children, canLogin = false }) {
    return (
        <div className="min-h-screen bg-[#0f1115] text-white">
            <div className="absolute inset-0 bg-[radial-gradient(circle_at_top,rgba(196,75,54,0.18),transparent_35%),linear-gradient(180deg,#0f1115_0%,#171a20_52%,#11141a_100%)]" />

            <div className="relative z-10">
                <header className="border-b border-white/10">
                    <div className="mx-auto flex max-w-7xl items-center justify-between px-4 py-5 sm:px-6 lg:px-8">
                        <Link href={route('public.home')} className="inline-flex items-center gap-3">
                            <ApplicationLogo className="h-9 w-9 fill-current text-white" />
                            <div>
                                <div className="text-xs font-semibold uppercase tracking-[0.22em] text-[#d1a15b]">
                                    YogaFX
                                </div>
                                <div className="text-sm font-medium text-white/80">
                                    Scoreboard
                                </div>
                            </div>
                        </Link>

                        <div className="flex items-center gap-3">
                            {canLogin ? (
                                <Link
                                    href={route('login')}
                                    className="rounded-lg border border-white/15 px-4 py-2 text-sm font-medium text-white/80 transition hover:border-white/25 hover:text-white"
                                >
                                    Admin Login
                                </Link>
                            ) : null}
                        </div>
                    </div>
                </header>

                <main>{children}</main>
            </div>
        </div>
    );
}
