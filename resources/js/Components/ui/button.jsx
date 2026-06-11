import { cva } from 'class-variance-authority';
import { cn } from '@/lib/utils';
import { cloneElement, isValidElement } from 'react';

const buttonVariants = cva(
    'inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-lg text-sm font-medium transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#203529]/30 disabled:pointer-events-none disabled:opacity-50',
    {
        variants: {
            variant: {
                default:
                    'bg-[#203529] text-white shadow-[0_12px_24px_rgba(32,53,41,0.18)] hover:bg-[#18281f]',
                outline:
                    'border border-slate-200 bg-white text-slate-700 hover:bg-slate-50 hover:text-slate-900',
                secondary:
                    'bg-slate-100 text-slate-700 hover:bg-slate-200',
                destructive:
                    'bg-rose-600 text-white hover:bg-rose-700',
                ghost: 'text-slate-600 hover:bg-slate-100 hover:text-slate-900',
            },
            size: {
                default: 'h-10 px-4 py-2',
                sm: 'h-9 px-3 py-2 text-xs',
                lg: 'h-11 px-5 py-3',
                icon: 'h-10 w-10',
            },
        },
        defaultVariants: {
            variant: 'default',
            size: 'default',
        },
    },
);

export function Button({
    className,
    variant,
    size,
    asChild = false,
    children,
    ...props
}) {
    const classes = cn(buttonVariants({ variant, size }), className);

    if (asChild && isValidElement(children)) {
        return cloneElement(children, {
            ...props,
            className: cn(classes, children.props.className),
        });
    }

    return (
        <button className={classes} {...props}>
            {children}
        </button>
    );
}

export { buttonVariants };
