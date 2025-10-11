import { RequestType } from '@/api/types';
import {
  DialogContent,
  DialogHeader,
  DialogTitle,
  DialogDescription,
} from '@/components/ui/dialog';
import { StatusCodeBadge } from '@/components/app/status-code-badge';

interface RequestShowModalProps {
  request: RequestType | null;
}

export function RequestShowModal({ request }: RequestShowModalProps) {
  if (!request) return null;

  return (
    <DialogContent className="max-w-2xl">
      <DialogHeader>
        <DialogTitle className="font-mono truncate">{request.method} /{request.uri}</DialogTitle>
        <DialogDescription>
          <StatusCodeBadge code={request.status_code} />
        </DialogDescription>
      </DialogHeader>
      <div className="text-sm space-y-2">
        <div className="flex justify-between">
            <span className="text-muted-foreground">Duration</span>
            <span>{request.duration_ms.toFixed(2)}ms</span>
        </div>
        <div className="flex justify-between">
            <span className="text-muted-foreground">Memory</span>
            <span>{request.memory_mb.toFixed(2)} MB</span>
        </div>
        <div className="flex justify-between">
            <span className="text-muted-foreground">Time</span>
            <span>{new Date(request.created_at).toLocaleString()}</span>
        </div>
      </div>
    </DialogContent>
  );
}