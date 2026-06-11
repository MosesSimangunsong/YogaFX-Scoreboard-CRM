import { cn } from '@/lib/utils';

export function Textarea({ className, ...props }) {
    return (
        <textarea
            className={cn(
                'flex min-h-[96px] w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm transition placeholder:text-slate-400 focus:border-[#203529] focus:outline-none focus:ring-2 focus:ring-[#203529]/10',
                className,
            )}
            {...props}
        />
    );
}
