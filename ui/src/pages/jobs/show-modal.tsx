import { Job } from '@/api/types';
import { Button } from '@/components/ui/button';
import {
  DialogContent,
  DialogHeader,
  DialogTitle,
  DialogDescription,
} from '@/components/ui/dialog';
import { StatusBadge } from '@/components/app/status-badge';
import { useForgetJobMutation, useRetryJobMutation } from '@/queries/jobs';
import { RotateCcw, Trash2 } from 'lucide-react';
import { toast } from 'sonner';

interface JobShowModalProps {
  job: Job | null;
}

export function JobShowModal({ job }: JobShowModalProps) {
  const retryMutation = useRetryJobMutation();
  const forgetMutation = useForgetJobMutation();

  if (!job) return null;

  let payload = typeof job.payload === 'string' ? job.payload : JSON.stringify(job.payload, null, 2) ?? '';
  try {
    if (typeof job.payload === 'string') {
      const parsed = JSON.parse(job.payload);
      payload = JSON.stringify(parsed, null, 2);
    }
  } catch (e) {
    console.warn('Payload is not valid JSON, displaying as-is.',e);
  }

  const handleRetry = async () => {
    try {
      await retryMutation.mutateAsync(job.id);
      toast.success('Retry requested');
    } catch {
      toast.error('Failed to retry job');
    }
  };

  const handleForget = async () => {
    try {
      await forgetMutation.mutateAsync(job.id);
      toast.success('Failed job forgotten');
    } catch {
      toast.error('Failed to forget job');
    }
  };

  return (
    <DialogContent className="max-w-4xl max-h-[80vh] flex flex-col">
      <DialogHeader>
        <div className="flex items-start justify-between gap-4">
          <div className="min-w-0">
            <DialogTitle className="font-mono truncate">{job.name}</DialogTitle>
            <DialogDescription>
              <StatusBadge status={job.status} />
            </DialogDescription>
          </div>
          {job.status === 'failed' && (
            <div className="flex shrink-0 items-center gap-2">
              <Button size="sm" variant="outline" onClick={handleRetry} disabled={retryMutation.isPending}>
                <RotateCcw className="mr-2 h-4 w-4" />
                Retry
              </Button>
              <Button size="sm" variant="destructive" onClick={handleForget} disabled={forgetMutation.isPending}>
                <Trash2 className="mr-2 h-4 w-4" />
                Forget
              </Button>
            </div>
          )}
        </div>
      </DialogHeader>
      <div className="flex-grow overflow-y-auto pr-6 text-sm">
        {job.exception && (
            <div className="mb-4">
                <h3 className="font-bold mb-2">Exception</h3>
                <pre className="p-2 bg-muted rounded-md whitespace-pre-wrap break-words font-mono text-red-500">{job.exception}</pre>
            </div>
        )}
        <div>
            <h3 className="font-bold mb-2">Payload</h3>
            <pre className="p-2 bg-muted rounded-md whitespace-pre-wrap break-words font-mono">{payload}</pre>
        </div>
      </div>
    </DialogContent>
  );
}
