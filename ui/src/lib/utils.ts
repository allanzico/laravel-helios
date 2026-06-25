import { type ClassValue, clsx } from "clsx"
import { twMerge } from "tailwind-merge"
import { badgeVariants } from "@/components/ui/badge";
import { buttonVariants } from "@/components/ui/button";
import { type VariantProps } from 'class-variance-authority';

export function cn(...inputs: ClassValue[]) {
  return twMerge(clsx(inputs))
}

// Helper for Badges
type BadgeVariant = VariantProps<typeof badgeVariants>['variant'];
const badgeStatusMap: Record<string, BadgeVariant> = {
  processed: 'default',
  finished: 'default',
  info: 'default',
  running: 'secondary',
  starting: 'secondary',
  retried: 'secondary',
  warning: 'secondary', 
  failed: 'destructive',
  error: 'destructive',
  critical: 'destructive',
  skipped: 'outline',
  debug: 'outline',
};

export const getBadgeStatusVariant = (status: string): BadgeVariant => {
  return badgeStatusMap[status.toLowerCase()] || 'outline';
};

// Helper for Buttons
type ButtonVariant = VariantProps<typeof buttonVariants>['variant'];
const buttonStatusMap: Record<string, ButtonVariant> = {
    error: 'destructive',
    warning: 'warning',
    info: 'default',
    debug: 'outline'
};

export const getButtonStatusVariant = (status: string): ButtonVariant => {
    return buttonStatusMap[status.toLowerCase()] || 'outline';
};
