import { cva, type VariantProps } from 'class-variance-authority';
import { Info, AlertTriangle, CircleX } from 'lucide-react';

const headerVariants = cva('p-4 border-l-4', {
  variants: {
    variant: {
      default: 'border-transparent',
      info: 'bg-blue-50 border-blue-400 dark:bg-blue-950 dark:border-blue-700',
      warning: 'bg-yellow-50 border-yellow-400 dark:bg-yellow-950 dark:border-yellow-700',
      error: 'bg-red-50 border-red-500 dark:bg-red-950 dark:border-red-700',
    },
  },
  defaultVariants: {
    variant: 'default',
  },
});

const iconVariants = cva('h-6 w-6 mr-3', {
    variants: {
        variant: {
            default: 'hidden',
            info: 'text-blue-500',
            warning: 'text-yellow-600',
            error: 'text-red-600',
        }
    },
    defaultVariants: {
        variant: 'default',
    }
});

interface MessageHeaderProps extends VariantProps<typeof headerVariants> {
  title: string;
  description: string;
}

const Icons = {
    info: <Info />,
    warning: <AlertTriangle />,
    error: <CircleX />,
    default: null
}

export function MessageHeader({ title, description, variant }: MessageHeaderProps) {
    const icon = Icons[variant || 'default'];

    return (
        <div className={headerVariants({ variant })}>
            <div className="flex items-center">
                <div className={iconVariants({ variant })}>
                    {icon}
                </div>
                <div>
                    <h2 className="text-xl font-bold">{title}</h2>
                    <p className="text-sm text-muted-foreground">{description}</p>
                </div>
            </div>
        </div>
    );
}