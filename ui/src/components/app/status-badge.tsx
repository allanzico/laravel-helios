import { Badge } from '@/components/ui/badge';
import { getBadgeStatusVariant } from '../../lib/utils'; 

interface StatusBadgeProps {
  status: string;
}

export function StatusBadge({ status }: StatusBadgeProps) {
    return <Badge variant={getBadgeStatusVariant(status)}>{status}</Badge>;
}