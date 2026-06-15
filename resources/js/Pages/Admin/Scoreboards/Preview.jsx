import { Badge } from '@/Components/ui/badge';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Textarea } from '@/Components/ui/textarea';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';
import { useMemo, useState } from 'react';

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

function buildQuestionMap(questions) {
    return new Map(questions.map((question) => [question.id, question]));
}

function resolveQuestionScore(question, draft) {
    const optionTypes = [
        'yes_no_maybe',
        'multiple_choice_buttons',
        'multiple_choice_checkboxes',
        'radio_buttons',
        'image_button',
    ];

    if (optionTypes.includes(question.question_type)) {
        return question.options
            .filter((option) => (draft.selected_option_ids ?? []).includes(option.id) && option.scoring_enabled)
            .reduce((total, option) => total + Number(option.score_value ?? 0), 0);
    }

    if (['numeric', 'sliding_scale', 'linear_scale', 'divided_scale'].includes(question.question_type)) {
        return draft.answer_number === '' || draft.answer_number === null || draft.answer_number === undefined
            ? 0
            : Number(draft.answer_number);
    }

    return 0;
}

function questionIsGradable(question) {
    return question.options?.some((option) => option.is_correct);
}

function answerIsCorrect(question, draft) {
    const correctIds = question.options
        .filter((option) => option.is_correct)
        .map((option) => option.id)
        .sort((left, right) => left - right);
    const selectedIds = [...(draft.selected_option_ids ?? [])].sort((left, right) => left - right);

    return JSON.stringify(correctIds) === JSON.stringify(selectedIds);
}

function resolveNextQuestionId(questions, question, draft) {
    const byId = buildQuestionMap(questions);

    if (question.question_type !== 'multiple_choice_checkboxes') {
        const firstJumpOption = question.options
            .filter((option) => (draft.selected_option_ids ?? []).includes(option.id) && option.jump_enabled)
            .sort((left, right) => left.sort_order - right.sort_order)[0];

        if (firstJumpOption?.jump_to_question_id && byId.has(firstJumpOption.jump_to_question_id)) {
            return firstJumpOption.jump_to_question_id;
        }
    }

    if (question.jump_enabled && question.jump_to_question_id && byId.has(question.jump_to_question_id)) {
        return question.jump_to_question_id;
    }

    const currentIndex = questions.findIndex((item) => item.id === question.id);

    return currentIndex >= 0 && currentIndex < questions.length - 1
        ? questions[currentIndex + 1].id
        : null;
}

function validateQuestion(question, draft) {
    const selectedCount = draft.selected_option_ids?.length ?? 0;

    if (['yes_no_maybe', 'radio_buttons'].includes(question.question_type)) {
        return !question.required || selectedCount === 1;
    }

    if (['multiple_choice_buttons', 'multiple_choice_checkboxes', 'image_button'].includes(question.question_type)) {
        if (question.required && selectedCount === 0) {
            return false;
        }

        if (!question.allow_multi_select && selectedCount > 1) {
            return false;
        }

        if (question.min_count && selectedCount < question.min_count) {
            return false;
        }

        if (question.max_count && selectedCount > question.max_count) {
            return false;
        }

        const selectedOtherOption = question.options.find(
            (option) => option.is_other_option && (draft.selected_option_ids ?? []).includes(option.id),
        );

        if (selectedOtherOption && !(draft.other_text ?? '').trim()) {
            return false;
        }

        return true;
    }

    if (['numeric', 'sliding_scale', 'linear_scale', 'divided_scale'].includes(question.question_type)) {
        if (question.required && (draft.answer_number === '' || draft.answer_number === null)) {
            return false;
        }

        if (draft.answer_number === '' || draft.answer_number === null) {
            return true;
        }

        const value = Number(draft.answer_number);

        if (!question.allow_decimals && !Number.isInteger(value)) {
            return false;
        }

        if (question.score_range_min !== null && question.score_range_min !== '' && value < Number(question.score_range_min)) {
            return false;
        }

        if (question.score_range_max !== null && question.score_range_max !== '' && value > Number(question.score_range_max)) {
            return false;
        }

        return true;
    }

    if (question.question_type === 'open_text') {
        if (question.required && !(draft.answer_text ?? '').trim()) {
            return false;
        }

        if (question.character_limit && (draft.answer_text ?? '').length > question.character_limit) {
            return false;
        }
    }

    return true;
}

export default function ScoreboardPreview({ scoreboard, questions, resultRanges }) {
    const orderedQuestions = useMemo(
        () => [...questions].sort((left, right) => left.sort_order - right.sort_order),
        [questions],
    );
    const [currentQuestionId, setCurrentQuestionId] = useState(orderedQuestions[0]?.id ?? null);
    const [visitedQuestionIds, setVisitedQuestionIds] = useState([]);
    const [drafts, setDrafts] = useState({});
    const [validationMessage, setValidationMessage] = useState('');
    const [result, setResult] = useState(null);

    const currentQuestion = orderedQuestions.find((question) => question.id === currentQuestionId) ?? null;
    const currentIndex = Math.max(
        0,
        [...visitedQuestionIds, currentQuestionId].filter(Boolean).indexOf(currentQuestionId),
    );
    const activePath = [...visitedQuestionIds, currentQuestionId].filter(Boolean);
    const draft = drafts[currentQuestionId] ?? {
        selected_option_ids: [],
        answer_text: '',
        answer_number: '',
        other_text: '',
    };

    const setDraftValue = (key, value) => {
        setDrafts((current) => ({
            ...current,
            [currentQuestionId]: {
                selected_option_ids: [],
                answer_text: '',
                answer_number: '',
                other_text: '',
                ...(current[currentQuestionId] ?? {}),
                [key]: value,
            },
        }));
    };

    const toggleOption = (optionId) => {
        const selected = draft.selected_option_ids ?? [];
        const isMultiSelect =
            currentQuestion.question_type === 'multiple_choice_checkboxes' ||
            currentQuestion.allow_multi_select;

        if (isMultiSelect) {
            setDraftValue(
                'selected_option_ids',
                selected.includes(optionId)
                    ? selected.filter((value) => value !== optionId)
                    : [...selected, optionId],
            );

            return;
        }

        setDraftValue('selected_option_ids', [optionId]);
    };

    const previousQuestionId = activePath[currentIndex - 1] ?? null;

    const finishPreview = () => {
        const totalScore = orderedQuestions.reduce(
            (total, question) => total + resolveQuestionScore(question, drafts[question.id] ?? {}),
            0,
        );
        const gradableQuestions = orderedQuestions.filter(questionIsGradable);
        const correctAnswersCount = gradableQuestions.filter((question) =>
            answerIsCorrect(question, drafts[question.id] ?? {}),
        ).length;
        const percentage = gradableQuestions.length > 0
            ? Number(((correctAnswersCount / gradableQuestions.length) * 100).toFixed(2))
            : null;
        const matchedRange = resultRanges.find((range) => {
            const minPass = range.min_score === null || totalScore >= Number(range.min_score);
            const maxPass = range.max_score === null || totalScore <= Number(range.max_score);

            return minPass && maxPass;
        }) ?? null;

        setResult({
            totalScore,
            correctAnswersCount,
            gradableQuestionsCount: gradableQuestions.length,
            percentage,
            matchedRange,
        });
    };

    const advance = () => {
        if (!validateQuestion(currentQuestion, draft)) {
            setValidationMessage('This preview step is invalid. Complete the required input to continue.');
            return;
        }

        setValidationMessage('');

        const nextQuestionId = resolveNextQuestionId(orderedQuestions, currentQuestion, draft);

        setVisitedQuestionIds((current) => [...new Set([...current, currentQuestion.id])]);

        if (!nextQuestionId) {
            finishPreview();
            setCurrentQuestionId(null);
            return;
        }

        setCurrentQuestionId(nextQuestionId);
    };

    const resetPreview = () => {
        setCurrentQuestionId(orderedQuestions[0]?.id ?? null);
        setVisitedQuestionIds([]);
        setDrafts({});
        setValidationMessage('');
        setResult(null);
    };

    return (
        <AuthenticatedLayout
            header={
                <div className="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                    <div>
                        <div className="flex flex-wrap items-center gap-2">
                            <Badge variant="outline">Scoreboards</Badge>
                            <Badge variant="outline">Preview</Badge>
                        </div>
                        <h2 className="mt-3 text-2xl font-semibold text-slate-900">
                            {scoreboard.title} Preview
                        </h2>
                        <p className="mt-1 text-sm text-slate-500">
                            Non-persisted admin simulation for validation, jump flow, and final result behavior.
                        </p>
                    </div>
                    <div className="flex flex-wrap gap-3">
                        <Button asChild variant="outline">
                            <Link href={route('admin.scoreboards.builder', scoreboard.id)}>
                                Back to Builder
                            </Link>
                        </Button>
                        <Button variant="outline" onClick={resetPreview}>
                            Restart Preview
                        </Button>
                    </div>
                </div>
            }
        >
            <Head title={`${scoreboard.title} Preview`} />

            <div className="py-12">
                <div className="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
                    {result ? (
                        <div className="rounded-[32px] border border-slate-200 bg-white p-8 shadow-sm">
                            <div className="text-xs font-semibold uppercase tracking-[0.22em] text-slate-500">
                                Preview Result
                            </div>
                            <h3 className="mt-4 text-3xl font-semibold text-slate-900">
                                Simulation Complete
                            </h3>
                            <div className="mt-6 grid gap-4 md:grid-cols-2">
                                <div className="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                                    <div className="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">
                                        Points
                                    </div>
                                    <div className="mt-2 text-2xl font-semibold text-slate-900">
                                        {result.totalScore}
                                    </div>
                                </div>
                                <div className="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                                    <div className="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">
                                        Correct Answers
                                    </div>
                                    <div className="mt-2 text-2xl font-semibold text-slate-900">
                                        {result.gradableQuestionsCount > 0
                                            ? `${result.correctAnswersCount} / ${result.gradableQuestionsCount}`
                                            : 'Not applicable'}
                                    </div>
                                </div>
                                <div className="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                                    <div className="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">
                                        Percentage
                                    </div>
                                    <div className="mt-2 text-2xl font-semibold text-slate-900">
                                        {result.percentage === null ? 'Not applicable' : `${result.percentage}%`}
                                    </div>
                                </div>
                                <div className="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                                    <div className="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">
                                        Result Range
                                    </div>
                                    <div className="mt-2 text-2xl font-semibold text-slate-900">
                                        {result.matchedRange?.title || 'Raw score fallback'}
                                    </div>
                                </div>
                            </div>
                            {result.matchedRange?.description ? (
                                <p className="mt-6 text-sm leading-7 text-slate-600">
                                    {result.matchedRange.description}
                                </p>
                            ) : null}
                        </div>
                    ) : currentQuestion ? (
                        <div className="grid gap-6 xl:grid-cols-[320px_minmax(0,1fr)]">
                            <aside className="rounded-[28px] border border-slate-200 bg-white p-6 shadow-sm">
                                <div className="text-xs font-semibold uppercase tracking-[0.22em] text-slate-500">
                                    Preview Progress
                                </div>
                                <h1 className="mt-4 text-2xl font-semibold text-slate-900">
                                    {scoreboard.title}
                                </h1>
                                <p className="mt-3 text-sm leading-7 text-slate-500">
                                    This simulation follows the scoreboard player without saving any real attempt.
                                </p>
                                {scoreboard.show_progress_bar ? (
                                    <div className="mt-6">
                                        <div className="flex items-center justify-between text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500">
                                            <span>Step {currentIndex + 1}</span>
                                            <span>{questions.length}</span>
                                        </div>
                                        <div className="mt-3 h-2 rounded-full bg-slate-100">
                                            <div
                                                className="h-2 rounded-full bg-[#c44b36]"
                                                style={{
                                                    width: `${Math.max(
                                                        8,
                                                        Math.round(((currentIndex + 1) / questions.length) * 100),
                                                    )}%`,
                                                }}
                                            />
                                        </div>
                                    </div>
                                ) : null}
                            </aside>

                            <div className="rounded-[32px] border border-slate-200 bg-white p-8 shadow-sm">
                                <div className="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">
                                    Screen Preview
                                </div>
                                <h2 className="mt-4 text-3xl font-semibold text-slate-900">
                                    {currentQuestion.title || currentQuestion.question_text || `Question ${currentQuestion.sort_order}`}
                                </h2>
                                {currentQuestion.question_text && currentQuestion.title ? (
                                    <p className="mt-4 text-base leading-8 text-slate-600">
                                        {currentQuestion.question_text}
                                    </p>
                                ) : null}
                                {currentQuestion.show_instruction && currentQuestion.instruction_text ? (
                                    <p className="mt-4 text-sm leading-7 text-[#9a6a2b]">
                                        {currentQuestion.instruction_text}
                                    </p>
                                ) : null}

                                <div className="mt-8 space-y-6">
                                    {['yes_no_maybe', 'radio_buttons', 'multiple_choice_buttons', 'multiple_choice_checkboxes', 'image_button'].includes(currentQuestion.question_type) ? (
                                        <div className="grid gap-3">
                                            {currentQuestion.options.map((option) => {
                                                const active = (draft.selected_option_ids ?? []).includes(option.id);

                                                return (
                                                    <button
                                                        key={option.id}
                                                        type="button"
                                                        onClick={() => toggleOption(option.id)}
                                                        className={[
                                                            'rounded-2xl border px-4 py-4 text-left transition',
                                                            active
                                                                ? 'border-[#c44b36] bg-[#c44b36]/10 text-slate-900'
                                                                : 'border-slate-200 bg-slate-50 text-slate-700',
                                                        ].join(' ')}
                                                    >
                                                        {option.label || option.internal_value || `Option ${option.id}`}
                                                    </button>
                                                );
                                            })}
                                        </div>
                                    ) : null}

                                    {currentQuestion.options.some(
                                        (option) =>
                                            option.is_other_option && (draft.selected_option_ids ?? []).includes(option.id),
                                    ) ? (
                                        <Input
                                            value={draft.other_text}
                                            onChange={(event) => setDraftValue('other_text', event.target.value)}
                                            placeholder="Share your custom answer"
                                        />
                                    ) : null}

                                    {currentQuestion.question_type === 'numeric' ? (
                                        <Input
                                            type="number"
                                            step={currentQuestion.allow_decimals ? '0.01' : '1'}
                                            value={draft.answer_number}
                                            onChange={(event) => setDraftValue('answer_number', event.target.value)}
                                        />
                                    ) : null}

                                    {currentQuestion.question_type === 'sliding_scale' ? (
                                        <Input
                                            type="range"
                                            min={currentQuestion.score_range_min ?? 0}
                                            max={currentQuestion.score_range_max ?? 10}
                                            step={currentQuestion.allow_decimals ? '0.01' : '1'}
                                            value={draft.answer_number || currentQuestion.starting_score || currentQuestion.score_range_min || 0}
                                            onChange={(event) => setDraftValue('answer_number', event.target.value)}
                                            className="h-12 border-none bg-transparent px-0"
                                        />
                                    ) : null}

                                    {currentQuestion.question_type === 'linear_scale' ? (
                                        <div className="flex flex-wrap gap-3">
                                            {buildScaleValues(currentQuestion).map((item) => (
                                                <button
                                                    key={item}
                                                    type="button"
                                                    onClick={() => setDraftValue('answer_number', String(item))}
                                                    className={[
                                                        'inline-flex min-w-12 items-center justify-center rounded-2xl border px-4 py-3 text-sm font-semibold transition',
                                                        String(draft.answer_number) === String(item)
                                                            ? 'border-[#c44b36] bg-[#c44b36]/10 text-slate-900'
                                                            : 'border-slate-200 bg-slate-50 text-slate-700',
                                                    ].join(' ')}
                                                >
                                                    {item}
                                                </button>
                                            ))}
                                        </div>
                                    ) : null}

                                    {currentQuestion.question_type === 'divided_scale' ? (
                                        <div
                                            className="grid gap-3"
                                            style={{
                                                gridTemplateColumns: `repeat(${Math.min(Math.max(Number(currentQuestion.section_count || 2), 1), Math.max(buildScaleValues(currentQuestion).length, 1))}, minmax(0, 1fr))`,
                                            }}
                                        >
                                            {buildScaleValues(currentQuestion).map((item) => (
                                                <button
                                                    key={item}
                                                    type="button"
                                                    onClick={() => setDraftValue('answer_number', String(item))}
                                                    className={[
                                                        'inline-flex min-h-12 items-center justify-center rounded-2xl border px-4 py-3 text-sm font-semibold transition',
                                                        String(draft.answer_number) === String(item)
                                                            ? 'border-[#c44b36] bg-[#c44b36]/10 text-slate-900'
                                                            : 'border-slate-200 bg-slate-50 text-slate-700',
                                                    ].join(' ')}
                                                >
                                                    {item}
                                                </button>
                                            ))}
                                        </div>
                                    ) : null}

                                    {currentQuestion.question_type === 'open_text' ? (
                                        <Textarea
                                            value={draft.answer_text}
                                            onChange={(event) => setDraftValue('answer_text', event.target.value)}
                                            className="min-h-40"
                                            placeholder="Write your answer here"
                                        />
                                    ) : null}

                                    {currentQuestion.question_type === 'info_screen' ? (
                                        <div className="rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4 text-sm leading-7 text-slate-600">
                                            This screen is informational only. Continue when you are ready.
                                        </div>
                                    ) : null}

                                    {validationMessage ? (
                                        <div className="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-900">
                                            {validationMessage}
                                        </div>
                                    ) : null}

                                    <div className="flex flex-wrap items-center justify-between gap-3 border-t border-slate-200 pt-6">
                                        <div>
                                            {scoreboard.allow_back_navigation && previousQuestionId ? (
                                                <Button
                                                    type="button"
                                                    variant="outline"
                                                    onClick={() => setCurrentQuestionId(previousQuestionId)}
                                                >
                                                    Back
                                                </Button>
                                            ) : null}
                                        </div>
                                        <Button type="button" onClick={advance} className="bg-[#c44b36] hover:bg-[#a93d2b]">
                                            Next
                                        </Button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    ) : null}
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
