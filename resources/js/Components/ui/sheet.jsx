import { cn } from '@/lib/utils';
import {
    cloneElement,
    createContext,
    isValidElement,
    useContext,
    useMemo,
    useState,
} from 'react';
import { createPortal } from 'react-dom';

const SheetContext = createContext(null);

function useSheetContext() {
    const context = useContext(SheetContext);

    if (!context) {
        throw new Error('Sheet components must be used within <Sheet>.');
    }

    return context;
}

export function Sheet({ children }) {
    const [open, setOpen] = useState(false);
    const value = useMemo(() => ({ open, setOpen }), [open]);

    return (
        <SheetContext.Provider value={value}>{children}</SheetContext.Provider>
    );
}

export function SheetTrigger({ asChild = false, children }) {
    const { setOpen } = useSheetContext();

    if (asChild && isValidElement(children)) {
        return cloneElement(children, {
            onClick: (event) => {
                children.props?.onClick?.(event);
                setOpen(true);
            },
        });
    }

    return (
        <button type="button" onClick={() => setOpen(true)}>
            {children}
        </button>
    );
}

export function SheetContent({
    children,
    className,
    side = 'right',
}) {
    const { open, setOpen } = useSheetContext();

    if (!open || typeof document === 'undefined') {
        return null;
    }

    const sideClasses = {
        right: 'right-0 top-0 h-full',
        left: 'left-0 top-0 h-full',
        top: 'left-0 top-0 w-full',
        bottom: 'bottom-0 left-0 w-full',
    };

    return createPortal(
        <div className="fixed inset-0 z-50">
            <button
                type="button"
                aria-label="Close panel"
                className="absolute inset-0 bg-black/40"
                onClick={() => setOpen(false)}
            />
            <div
                className={cn(
                    'absolute bg-white shadow-2xl',
                    sideClasses[side],
                    className,
                )}
            >
                <button
                    type="button"
                    className="absolute right-4 top-4 text-sm font-medium text-slate-500 hover:text-slate-900"
                    onClick={() => setOpen(false)}
                >
                    Close
                </button>
                {children}
            </div>
        </div>,
        document.body,
    );
}

export function SheetHeader({ className, ...props }) {
    return <div className={cn('space-y-1.5', className)} {...props} />;
}

export function SheetTitle({ className, ...props }) {
    return (
        <h3
            className={cn('text-lg font-semibold tracking-tight text-slate-900', className)}
            {...props}
        />
    );
}

export function SheetDescription({ className, ...props }) {
    return (
        <p className={cn('text-sm leading-6 text-slate-500', className)} {...props} />
    );
}
