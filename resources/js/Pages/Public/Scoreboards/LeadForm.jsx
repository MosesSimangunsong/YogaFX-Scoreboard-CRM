import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import PublicLayout from '@/Layouts/PublicLayout';
import { Head, useForm } from '@inertiajs/react';

export default function PublicScoreboardLeadForm({ scoreboard, status, countryOptions = [] }) {
    const { data, setData, post, processing, errors } = useForm({
        first_name: '',
        last_name: '',
        email: '',
        whatsapp: '',
        country: '',
    });

    const submit = (event) => {
        event.preventDefault();
        post(route('public.scoreboards.lead.store', scoreboard.slug));
    };

    return (
        <PublicLayout>
            <Head title={`${scoreboard.title} Lead Form`} />

            <section className="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8 lg:py-20">
                <div className="grid gap-8 lg:grid-cols-[minmax(0,1fr)_480px] lg:items-start">
                    <div className="max-w-2xl">
                        <div className="inline-flex rounded-full border border-[#d1a15b]/30 bg-[#d1a15b]/10 px-4 py-1.5 text-xs font-semibold uppercase tracking-[0.22em] text-[#f0d5a8]">
                            Participant Intake
                        </div>
                        <h1 className="mt-6 text-4xl font-semibold tracking-tight text-white sm:text-5xl">
                            {scoreboard.title}
                        </h1>
                        <p className="mt-5 text-base leading-8 text-white/70 sm:text-lg">
                            Share a few details first so YogaFX Scoreboard can prepare your participant record
                            before the assessment flow continues.
                        </p>

                        {scoreboard.description ? (
                            <div className="mt-8 rounded-[28px] border border-white/10 bg-white/5 p-6 shadow-[0_24px_60px_rgba(0,0,0,0.24)] backdrop-blur-sm">
                                <div className="text-sm leading-7 text-white/75">{scoreboard.description}</div>
                            </div>
                        ) : null}
                    </div>

                    <div className="rounded-[32px] border border-white/10 bg-white/5 p-6 shadow-[0_24px_60px_rgba(0,0,0,0.24)] backdrop-blur-sm">
                        <div className="rounded-[24px] border border-white/10 bg-[#171a20] p-6">
                            {status === 'lead-captured' ? (
                                <div className="mb-5 rounded-2xl border border-emerald-400/20 bg-emerald-400/10 px-4 py-3 text-sm text-emerald-100">
                                    Lead captured successfully.
                                </div>
                            ) : null}

                            <form onSubmit={submit} className="space-y-5">
                                <div className="grid gap-5 sm:grid-cols-2">
                                    <div className="space-y-2">
                                        <label className="text-xs font-semibold uppercase tracking-[0.16em] text-white/45">
                                            First Name
                                        </label>
                                        <Input
                                            value={data.first_name}
                                            onChange={(event) => setData('first_name', event.target.value)}
                                            className="border-white/10 bg-white/5 text-white placeholder:text-white/35"
                                        />
                                        {errors.first_name ? (
                                            <div className="text-sm text-rose-300">{errors.first_name}</div>
                                        ) : null}
                                    </div>

                                    <div className="space-y-2">
                                        <label className="text-xs font-semibold uppercase tracking-[0.16em] text-white/45">
                                            Last Name
                                        </label>
                                        <Input
                                            value={data.last_name}
                                            onChange={(event) => setData('last_name', event.target.value)}
                                            className="border-white/10 bg-white/5 text-white placeholder:text-white/35"
                                        />
                                        {errors.last_name ? (
                                            <div className="text-sm text-rose-300">{errors.last_name}</div>
                                        ) : null}
                                    </div>
                                </div>

                                <div className="space-y-2">
                                    <label className="text-xs font-semibold uppercase tracking-[0.16em] text-white/45">
                                        Email
                                    </label>
                                    <Input
                                        type="email"
                                        value={data.email}
                                        onChange={(event) => setData('email', event.target.value)}
                                        className="border-white/10 bg-white/5 text-white placeholder:text-white/35"
                                    />
                                    {errors.email ? (
                                        <div className="text-sm text-rose-300">{errors.email}</div>
                                    ) : null}
                                </div>

                                <div className="space-y-2">
                                    <label className="text-xs font-semibold uppercase tracking-[0.16em] text-white/45">
                                        WhatsApp
                                    </label>
                                    <Input
                                        value={data.whatsapp}
                                        onChange={(event) => setData('whatsapp', event.target.value)}
                                        className="border-white/10 bg-white/5 text-white placeholder:text-white/35"
                                    />
                                    {errors.whatsapp ? (
                                        <div className="text-sm text-rose-300">{errors.whatsapp}</div>
                                    ) : null}
                                </div>

                                <div className="space-y-2">
                                    <label className="text-xs font-semibold uppercase tracking-[0.16em] text-white/45">
                                        Country
                                    </label>
                                    <select
                                        value={data.country}
                                        onChange={(event) => setData('country', event.target.value)}
                                        className="flex h-10 w-full rounded-lg border border-white/10 bg-white/5 px-3 text-sm text-white"
                                    >
                                        <option value="" className="text-slate-900">
                                            Select country
                                        </option>
                                        {countryOptions.map((country) => (
                                            <option key={country} value={country} className="text-slate-900">
                                                {country}
                                            </option>
                                        ))}
                                    </select>
                                    {errors.country ? (
                                        <div className="text-sm text-rose-300">{errors.country}</div>
                                    ) : null}
                                </div>

                                <div className="pt-3">
                                    <Button
                                        type="submit"
                                        disabled={processing}
                                        className="h-11 w-full rounded-xl bg-[#c44b36] shadow-[0_18px_40px_rgba(196,75,54,0.24)] hover:bg-[#a93d2b]"
                                    >
                                        Continue
                                    </Button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </section>
        </PublicLayout>
    );
}
