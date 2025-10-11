import { Badge, badgeVariants } from '../../components/ui/badge'; 
import { type VariantProps } from 'class-variance-authority';

// Define the type for our badge variants
type BadgeVariant = VariantProps<typeof badgeVariants>['variant'];

interface StatusCodeBadgeProps {
  code: number;
}

export function StatusCodeBadge({ code }: StatusCodeBadgeProps) {
  const getVariant = (): BadgeVariant => {
    if (code >= 500) return 'destructive';
    if (code >= 400) return 'destructive';
    if (code >= 300) return 'secondary';
    if (code >= 200) return 'success'; 
    return 'outline';
  };

  return <Badge variant={getVariant()}>{code}</Badge>;
}