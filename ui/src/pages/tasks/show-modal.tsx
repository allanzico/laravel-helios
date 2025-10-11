import { ScheduledTask } from '@/api/types';
import {
  DialogContent,
  DialogHeader,
  DialogTitle,
  DialogDescription,
} from '../../components/ui/dialog';
import { StatusBadge } from '../../components/app/status-badge';

interface TaskShowModalProps {
  task: ScheduledTask | null;
}

export function TaskShowModal({ task }: TaskShowModalProps) {
  if (!task) return null;
  return (
    <DialogContent className="max-w-4xl max-h-[80vh] flex flex-col">
      <DialogHeader>
        <DialogTitle className="font-mono truncate">{task.command}</DialogTitle>
        <DialogDescription>
          <StatusBadge status={task.status} />
        </DialogDescription>
      </DialogHeader>
      <div className="flex-grow overflow-y-auto pr-6 text-sm">
        {task.output && (
            <div>
                <h3 className="font-bold mb-2">Output / Exception</h3>
                <pre className="p-2 bg-muted rounded-md whitespace-pre-wrap break-words font-mono">{task.output}</pre>
            </div>
        )}
      </div>
    </DialogContent>
  );
}