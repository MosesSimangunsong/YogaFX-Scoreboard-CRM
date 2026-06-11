import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';

export default function Dashboard() {
    return (
        <AuthenticatedLayout
            header={
                <div>
                    <div className="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">
                        Admin
                    </div>
                    <h2 className="mt-2 text-2xl font-semibold tracking-tight text-slate-900">
                        Dashboard
                    </h2>
                    <p className="mt-1 text-sm text-slate-500">
                        Internal workspace for managing scoreboards, participant data, and assessment operations.
                    </p>
                </div>
            }
        >
            <Head title="Dashboard" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div className="rounded-[28px] border border-slate-200 bg-white p-8 shadow-sm">
                        <div className="max-w-2xl">
                            <div className="text-sm font-medium text-slate-900">
                                Admin foundation is ready
                            </div>
                            <div className="mt-2 text-sm leading-7 text-slate-500">
                                This dashboard acts as the entry point for the YogaFX Scoreboard admin workspace.
                                Continue into Scoreboards to manage assessment structures and builder flows.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
