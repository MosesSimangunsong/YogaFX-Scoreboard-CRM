import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Textarea } from '@/Components/ui/textarea';
import PublicLayout from '@/Layouts/PublicLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import { useEffect } from 'react';

function buildScaleValues(question) {
    const min = Number(question.score_range_min ?? 1);
    const max = Number(question.score_range_max ?? min);

    if (!Number.isFinite(min) || !Number.isFinite(max) || max < min) {
        return [];
    }

    const values = [];

    for (let value = min; value <= max; value += 1) {
        values.push(value);
    }

    return values.slice(0, 12);
}

function QuestionOptions({ question, data, setData }) {
    const selectedOptionIds = data.selected_option_ids ?? [];
    const isMultiSelect =
        question.question_type === 'multiple_choice_checkboxes' ||
        question.allow_multi_select;

    const toggleOption = (optionId) => {
        if (isMultiSelect) {
            const exists = selectedOptionIds.includes(optionId);
            setData(
                'selected_option_ids',
                exists
                    ? selectedOptionIds.filter((value) => value !== optionId)
                    : [...selectedOptionIds, optionId],
            );

            return;
        }

        setData('selected_option_ids', [optionId]);
    };

    return (
        <div className="grid gap-3">
            {question.options.map((option) => {
                const active = selectedOptionIds.includes(option.id);

                return (
                    <button
                        key={option.id}
                        type="button"
                        onClick={() => toggleOption(option.id)}
                        className={[
                            'overflow-hidden rounded-2xl border px-4 py-4 text-left transition',
                            active
                                ? 'border-[#d1a15b] bg-[#d1a15b]/15 text-white shadow-[0_16px_36px_rgba(209,161,91,0.16)]'
                                : 'border-white/10 bg-white/5 text-white/85 hover:border-white/20 hover:bg-white/10',
                        ].join(' ')}
                    >
                        {option.image_url ? (
                            <img
                                src={option.image_url}
                                alt={option.label ?? 'Option image'}
                                className="mb-3 h-40 w-full rounded-xl object-cover"
                            />
                        ) : null}
                        <div className="flex items-start justify-between gap-3">
                            <div className="text-sm font-medium leading-6">
                                {option.label || option.internal_value || `Option ${option.id}`}
                            </div>
                            <div
                                className={[
                                    'mt-0.5 size-5 shrink-0 rounded-full border',
                                    active ? 'border-[#d1a15b] bg-[#d1a15b]' : 'border-white/30',
                                ].join(' ')}
                            />
                        </div>
                    </button>
                );
            })}
        </div>
    );
}

function ScaleLabels({ question }) {
    if (!question.left_label && !question.center_label && !question.right_label) {
        return null;
    }

    return (
        <div className="mt-3 grid grid-cols-3 text-xs uppercase tracking-[0.16em] text-white/40">
            <div>{question.left_label}</div>
            <div className="text-center">{question.center_label}</div>
            <div className="text-right">{question.right_label}</div>
        </div>
    );
}

function LinearScaleInput({ question, value, setValue }) {
    const scaleValues = buildScaleValues(question);

    return (
        <div className="space-y-4">
            <div className="flex flex-wrap gap-3">
                {scaleValues.map((item) => {
                    const active = String(value) === String(item);

                    return (
                        <button
                            key={item}
                            type="button"
                            onClick={() => setValue('answer_number', String(item))}
                            className={[
                                'inline-flex min-w-12 items-center justify-center rounded-2xl border px-4 py-3 text-sm font-semibold transition',
                                active
                                    ? 'border-[#d1a15b] bg-[#d1a15b]/15 text-white shadow-[0_16px_36px_rgba(209,161,91,0.16)]'
                                    : 'border-white/10 bg-white/5 text-white/85 hover:border-white/20 hover:bg-white/10',
                            ].join(' ')}
                        >
                            {item}
                        </button>
                    );
                })}
            </div>
            <ScaleLabels question={question} />
        </div>
    );
}

function DividedScaleInput({ question, value, setValue }) {
    const scaleValues = buildScaleValues(question);
    const columnCount = Math.min(Math.max(Number(question.section_count || 2), 1), Math.max(scaleValues.length, 1));

    return (
        <div className="space-y-4">
            <div
                className="grid gap-3"
                style={{ gridTemplateColumns: `repeat(${columnCount}, minmax(0, 1fr))` }}
            >
                {scaleValues.map((item) => {
                    const active = String(value) === String(item);

                    return (
                        <button
                            key={item}
                            type="button"
                            onClick={() => setValue('answer_number', String(item))}
                            className={[
                                'inline-flex min-h-12 items-center justify-center rounded-2xl border px-4 py-3 text-sm font-semibold transition',
                                active
                                    ? 'border-[#d1a15b] bg-[#d1a15b]/15 text-white shadow-[0_16px_36px_rgba(209,161,91,0.16)]'
                                    : 'border-white/10 bg-white/5 text-white/85 hover:border-white/20 hover:bg-white/10',
                            ].join(' ')}
                        >
                            {item}
                        </button>
                    );
                })}
            </div>
        </div>
    );
}

export default function PublicScoreboardAssessment({
    accessLink,
    scoreboard,
    participant,
    submission,
    progress,
    question,
    answer,
    navigation,
}) {
    const { data, setData, post, processing, errors } = useForm({
        question_id: question.id,
        selected_option_ids: answer.selected_option_ids ?? [],
        answer_text: answer.answer_text ?? '',
        answer_number: answer.answer_number ?? '',
        other_text: answer.other_text ?? '',
    });

    useEffect(() => {
        setData('question_id', question.id);
        setData('selected_option_ids', answer.selected_option_ids ?? []);
        setData('answer_text', answer.answer_text ?? '');
        setData('answer_number', answer.answer_number ?? '');
        setData('other_text', answer.other_text ?? '');
    }, [
        answer.answer_number,
        answer.answer_text,
        answer.other_text,
        answer.selected_option_ids,
        question.id,
        setData,
    ]);

    const submit = (event) => {
        event.preventDefault();
        post(route('public.scoreboards.assessment.store', accessLink.code));
    };

    const selectedOtherOption = question.options.find(
        (option) =>
            option.is_other_option && (data.selected_option_ids ?? []).includes(option.id),
    );

    return (
        <PublicLayout>
            <Head title={`${scoreboard.title} Assessment`} />

            <section className="mx-auto max-w-6xl px-4 py-14 sm:px-6 lg:px-8 lg:py-16">
                <div className="grid gap-6 xl:grid-cols-[320px_minmax(0,1fr)]">
                    <aside className="rounded-[28px] border border-white/10 bg-white/5 p-6 shadow-[0_24px_60px_rgba(0,0,0,0.24)] backdrop-blur-sm xl:sticky xl:top-6 xl:h-fit">
                        <div className="text-xs font-semibold uppercase tracking-[0.22em] text-[#d1a15b]">
                            Assessment Progress
                        </div>
                        <h1 className="mt-4 text-2xl font-semibold tracking-tight text-white">
                            {scoreboard.title}
                        </h1>
                        <p className="mt-3 text-sm leading-7 text-white/65">
                            {participant.first_name ? `${participant.first_name}, ` : ''}
                            your responses are saved to this unique assessment attempt.
                        </p>

                        {scoreboard.show_progress_bar ? (
                            <div className="mt-6">
                                <div className="flex items-center justify-between text-[11px] font-semibold uppercase tracking-[0.18em] text-white/45">
                                    <span>Step {progress.current_step}</span>
                                    <span>{progress.total_steps}</span>
                                </div>
                                <div className="mt-3 h-2 rounded-full bg-white/10">
                                    <div
                                        className="h-2 rounded-full bg-[#c44b36]"
                                        style={{
                                            width: `${Math.max(
                                                8,
                                                Math.round((progress.current_step / progress.total_steps) * 100),
                                            )}%`,
                                        }}
                                    />
                                </div>
                            </div>
                        ) : null}

                        <div className="mt-6 rounded-2xl border border-white/10 bg-black/20 px-4 py-4">
                            <div className="text-[11px] font-semibold uppercase tracking-[0.18em] text-white/45">
                                Submission State
                            </div>
                            <div className="mt-2 text-sm font-medium text-white">
                                {submission.status}
                            </div>
                            <div className="mt-1 text-sm text-white/55">
                                {progress.completed_steps} answered so far
                            </div>
                        </div>
                    </aside>

                    <div className="rounded-[32px] border border-white/10 bg-white/5 p-6 shadow-[0_24px_60px_rgba(0,0,0,0.24)] backdrop-blur-sm sm:p-8">
                        <div className="rounded-[24px] border border-white/10 bg-[#171a20] p-6 sm:p-8">
                            <div className="flex flex-wrap items-center gap-3 text-xs font-semibold uppercase tracking-[0.18em] text-white/45">
                                <span>Question {progress.current_step}</span>
                                {question.required ? <span>Required</span> : <span>Optional</span>}
                            </div>

                            <div className="mt-5">
                                <h2 className="text-3xl font-semibold tracking-tight text-white">
                                    {question.title || question.question_text || `Question ${progress.current_step}`}
                                </h2>
                                {question.question_text && question.title ? (
                                    <p className="mt-4 text-base leading-8 text-white/75">
                                        {question.question_text}
                                    </p>
                                ) : null}
                                {question.instruction_text ? (
                                    <p className="mt-4 text-sm leading-7 text-[#f0d5a8]">
                                        {question.instruction_text}
                                    </p>
                                ) : null}
                            </div>

                            <form onSubmit={submit} className="mt-8 space-y-6">
                                {['yes_no_maybe', 'radio_buttons', 'multiple_choice_buttons', 'multiple_choice_checkboxes', 'image_button'].includes(question.question_type) ? (
                                    <QuestionOptions question={question} data={data} setData={setData} />
                                ) : null}

                                {selectedOtherOption ? (
                                    <div className="space-y-2">
                                        <label className="text-xs font-semibold uppercase tracking-[0.16em] text-white/45">
                                            Other Response
                                        </label>
                                        <Input
                                            value={data.other_text}
                                            onChange={(event) => setData('other_text', event.target.value)}
                                            className="border-white/10 bg-white/5 text-white placeholder:text-white/35"
                                            placeholder="Share your custom answer"
                                        />
                                        {errors.other_text ? (
                                            <div className="text-sm text-rose-300">{errors.other_text}</div>
                                        ) : null}
                                    </div>
                                ) : null}

                                {question.question_type === 'numeric' ? (
                                    <div className="space-y-4">
                                        <div className="space-y-2">
                                            <label className="text-xs font-semibold uppercase tracking-[0.16em] text-white/45">
                                                Your Score
                                            </label>
                                            <Input
                                                type="number"
                                                min={question.score_range_min ?? undefined}
                                                max={question.score_range_max ?? undefined}
                                                step={question.allow_decimals ? '0.01' : '1'}
                                                value={data.answer_number}
                                                onChange={(event) => setData('answer_number', event.target.value)}
                                                className="border-white/10 bg-white/5 text-white placeholder:text-white/35"
                                            />
                                        </div>
                                    </div>
                                ) : null}

                                {question.question_type === 'sliding_scale' ? (
                                    <div className="space-y-4">
                                        <div className="space-y-2">
                                            <label className="text-xs font-semibold uppercase tracking-[0.16em] text-white/45">
                                                Your Score
                                            </label>
                                            <Input
                                                type="range"
                                                min={question.score_range_min ?? 0}
                                                max={question.score_range_max ?? 10}
                                                step={question.allow_decimals ? '0.01' : '1'}
                                                value={data.answer_number || question.starting_score || question.score_range_min || 0}
                                                onChange={(event) => setData('answer_number', event.target.value)}
                                                className="h-12 border-none bg-transparent px-0"
                                            />
                                        </div>
                                        <div className="text-sm font-medium text-white/80">
                                            Selected: {data.answer_number || question.starting_score || question.score_range_min || 0}
                                        </div>
                                        <ScaleLabels question={question} />
                                    </div>
                                ) : null}

                                {question.question_type === 'linear_scale' ? (
                                    <LinearScaleInput question={question} value={data.answer_number} setValue={setData} />
                                ) : null}

                                {question.question_type === 'divided_scale' ? (
                                    <DividedScaleInput question={question} value={data.answer_number} setValue={setData} />
                                ) : null}

                                {question.question_type === 'open_text' ? (
                                    <div className="space-y-2">
                                        <label className="text-xs font-semibold uppercase tracking-[0.16em] text-white/45">
                                            Your Response
                                        </label>
                                        <Textarea
                                            value={data.answer_text}
                                            onChange={(event) => setData('answer_text', event.target.value)}
                                            className="min-h-40 border-white/10 bg-white/5 text-white placeholder:text-white/35"
                                            placeholder="Write your answer here"
                                        />
                                        {question.character_limit ? (
                                            <div className="text-xs text-white/45">
                                                Max {question.character_limit} characters
                                            </div>
                                        ) : null}
                                    </div>
                                ) : null}

                                {question.question_type === 'info_screen' ? (
                                    <div className="rounded-2xl border border-white/10 bg-black/20 px-5 py-4 text-sm leading-7 text-white/72">
                                        This screen is informational only. Continue when you are ready.
                                    </div>
                                ) : null}

                                {errors.selected_option_ids ? (
                                    <div className="text-sm text-rose-300">{errors.selected_option_ids}</div>
                                ) : null}
                                {errors.answer_text ? (
                                    <div className="text-sm text-rose-300">{errors.answer_text}</div>
                                ) : null}
                                {errors.answer_number ? (
                                    <div className="text-sm text-rose-300">{errors.answer_number}</div>
                                ) : null}

                                <div className="flex flex-wrap items-center justify-between gap-3 border-t border-white/10 pt-6">
                                    <div>
                                        {navigation.previous_url ? (
                                            <Button
                                                asChild
                                                type="button"
                                                variant="outline"
                                                className="border-white/10 bg-white/5 text-white hover:bg-white/10 hover:text-white"
                                            >
                                                <Link href={navigation.previous_url}>Back</Link>
                                            </Button>
                                        ) : (
                                            <Button
                                                asChild
                                                type="button"
                                                variant="outline"
                                                className="border-white/10 bg-white/5 text-white hover:bg-white/10 hover:text-white"
                                            >
                                                <Link href={route('public.scoreboards.access', accessLink.code)}>
                                                    Exit
                                                </Link>
                                            </Button>
                                        )}
                                    </div>

                                    <Button
                                        type="submit"
                                        disabled={processing}
                                        className="bg-[#c44b36] hover:bg-[#a93d2b]"
                                    >
                                        {navigation.is_last_question ? 'Submit Assessment' : 'Save and Continue'}
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
