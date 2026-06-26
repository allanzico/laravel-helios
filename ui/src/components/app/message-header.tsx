import { cva, type VariantProps } from 'class-variance-authority';
import { Info, AlertTriangle, CircleX } from 'lucide-react';

const headerVariants = cva('p-4 border-l-4', {
  variants: {
    variant: {
      default: 'border-transparent',
      info: 'bg-info/10 border-info',
      warning: 'bg-warning/10 border-warning',
      error: 'bg-destructive/10 border-destructive',
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
            info: 'text-info',
            warning: 'text-warning',
            error: 'text-destructive',
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