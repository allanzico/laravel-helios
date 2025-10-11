import { Job } from '@/api/types';
import {
  DialogContent,
  DialogHeader,
  DialogTitle,
  DialogDescription,
} from '@/components/ui/dialog';
import { StatusBadge } from '@/components/app/status-badge';

interface JobShowModalProps {
  job: Job | null;
}

export function JobShowModal({ job }: JobShowModalProps) {
  if (!job) return null;

  let payload = job.payload;
  try {
    // Try to pretty-print the payload if it's a JSON string
    const parsed = JSON.parse(job.payload);
    payload = JSON.stringify(parsed, null, 2);
  } catch (e) {
    console.warn('Payload is not valid JSON, displaying as-is.',e);
    // Not valid JSON, leave as is
  }

  return (
    <DialogContent className="max-w-4xl max-h-[80vh] flex flex-col">
      <DialogHeader>
        <DialogTitle className="font-mono truncate">{job.name}</DialogTitle>
        <DialogDescription>
          <StatusBadge status={job.status} />
        </DialogDescription>
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