import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Textarea } from '@/Components/ui/textarea';

function FieldError({ message }) {
    if (!message) {
        return null;
    }

    return (
        <div className="text-sm text-rose-600">
            {message}
        </div>
    );
}

function ToggleRow({ label, description, checked, onChange }) {
    return (
        <label className="flex items-start justify-between gap-4 rounded-2xl border border-slate-200 bg-[#fafaf8] px-4 py-4">
            <div className="min-w-0 flex-1">
                <div className="text-sm font-medium text-slate-900">{label}</div>
                {description ? (
                    <div className="mt-1 text-xs leading-5 text-slate-500">{description}</div>
                ) : null}
            </div>

            <input
                type="checkbox"
                checked={checked}
                onChange={(event) => onChange(event.target.checked)}
                className="mt-1 h-4 w-4 rounded border-slate-300 text-[#203529] focus:ring-[#203529]"
            />
        </label>
    );
}

export default function ScoreboardMetaForm({
    data,
    setData,
    errors,
    processing,
    statusOptions = [],
    scoringModeOptions = [],
    resultModeOptions = [],
    onSubmit,
    submitLabel,
    currentThumbnailUrl = null,
}) {
    return (
        <form onSubmit={onSubmit} className="space-y-8">
            <div className="grid gap-6 lg:grid-cols-2">
                <div className="space-y-2">
                    <label className="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">
                        Title
                    </label>
                    <Input
                        value={data.title}
                        onChange={(event) => setData('title', event.target.value)}
                        placeholder="Teacher Readiness Scoreboard"
                    />
                    <FieldError message={errors.title} />
                </div>

                <div className="space-y-2">
                    <label className="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">
                        Slug
                    </label>
                    <Input
                        value={data.slug}
                        onChange={(event) => setData('slug', event.target.value)}
                        placeholder="teacher-readiness-scoreboard"
                    />
                    <FieldError message={errors.slug} />
                </div>
            </div>

            <div className="space-y-2">
                <label className="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">
                    Description
                </label>
                <Textarea
                    value={data.description}
                    onChange={(event) => setData('description', event.target.value)}
                    placeholder="Short internal and editorial summary for this scoreboard."
                    className="min-h-[140px]"
                />
                <FieldError message={errors.description} />
            </div>

            <div className="grid gap-6 lg:grid-cols-4">
                <div className="space-y-2">
                    <label className="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">
                        Status
                    </label>
                    <select
                        value={data.status}
                        onChange={(event) => setData('status', event.target.value)}
                        className="flex h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm text-slate-900"
                    >
                        {statusOptions.map((option) => (
                            <option key={option.value ?? option} value={option.value ?? option}>
                                {option.label ?? option}
                            </option>
                        ))}
                    </select>
                    <FieldError message={errors.status} />
                </div>

                <div className="space-y-2">
                    <label className="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">
                        Duration Minutes
                    </label>
                    <Input
                        type="number"
                        min="0"
                        value={data.duration_minutes}
                        onChange={(event) => setData('duration_minutes', event.target.value)}
                    />
                    <FieldError message={errors.duration_minutes} />
                </div>

                <div className="space-y-2">
                    <label className="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">
                        Scoring Mode
                    </label>
                    <select
                        value={data.scoring_mode}
                        onChange={(event) => setData('scoring_mode', event.target.value)}
                        className="flex h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm text-slate-900"
                    >
                        {scoringModeOptions.map((option) => (
                            <option key={option.value ?? option} value={option.value ?? option}>
                                {option.label ?? option}
                            </option>
                        ))}
                    </select>
                    <FieldError message={errors.scoring_mode} />
                </div>

                <div className="space-y-2">
                    <label className="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">
                        Result Mode
                    </label>
                    <select
                        value={data.result_mode}
                        onChange={(event) => setData('result_mode', event.target.value)}
                        className="flex h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm text-slate-900"
                    >
                        {resultModeOptions.map((option) => (
                            <option key={option.value ?? option} value={option.value ?? option}>
                                {option.label ?? option}
                            </option>
                        ))}
                    </select>
                    <FieldError message={errors.result_mode} />
                </div>
            </div>

            <div className="space-y-3">
                <div className="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">
                    Thumbnail
                </div>

                {currentThumbnailUrl ? (
                    <div className="overflow-hidden rounded-2xl border border-slate-200 bg-[#fafaf8] p-3">
                        <img
                            src={currentThumbnailUrl}
                            alt="Current thumbnail"
                            className="h-40 w-full rounded-xl object-cover"
                        />
                    </div>
                ) : null}

                <Input
                    type="file"
                    accept="image/*"
                    onChange={(event) => setData('thumbnail', event.target.files?.[0] ?? null)}
                    className="h-auto py-2"
                />
                <FieldError message={errors.thumbnail} />
            </div>

            <div className="grid gap-4 lg:grid-cols-3">
                <ToggleRow
                    label="Active"
                    description="Controls whether this scoreboard should be available operationally."
                    checked={Boolean(data.is_active)}
                    onChange={(checked) => setData('is_active', checked)}
                />
                <ToggleRow
                    label="Show Progress Bar"
                    description="Enable progress visibility in the participant experience."
                    checked={Boolean(data.show_progress_bar)}
                    onChange={(checked) => setData('show_progress_bar', checked)}
                />
                <ToggleRow
                    label="Allow Back Navigation"
                    description="Permit participants to move back to previous screens."
                    checked={Boolean(data.allow_back_navigation)}
                    onChange={(checked) => setData('allow_back_navigation', checked)}
                />
            </div>

            <div className="flex flex-wrap justify-end gap-3 border-t border-slate-200 pt-6">
                <Button type="submit" disabled={processing}>
                    {submitLabel}
                </Button>
            </div>
        </form>
    );
}
