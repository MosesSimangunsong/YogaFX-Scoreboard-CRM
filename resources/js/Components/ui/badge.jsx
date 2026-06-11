import { cva } from 'class-variance-authority';
import { cn } from '@/lib/utils';

const badgeVariants = cva(
    'inline-flex items-center rounded-lg px-2.5 py-1 text-xs font-medium transition',
    {
        variants: {
            variant: {
                default: 'bg-[#203529] text-white',
                secondary: 'bg-emerald-100 text-emerald-800',
                outline: 'border border-slate-200 bg-white text-slate-600',
            },
        },
        defaultVariants: {
            variant: 'default',
        },
    },
);

export function Badge({ className, variant, ...props }) {
    return <div className={cn(badgeVariants({ variant }), className)} {...props} />;
}
