import { useState, useMemo } from 'react';
import { useParams } from '@tanstack/react-router';
import { useLogContentQuery, useClearLogMutation } from '../../queries/logs';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '../../components/ui/card';
import { Button } from '../../components/ui/button';
import { LogEntry } from '../../components/app/log-entry.tsx';
import {
  AlertDialog,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
  AlertDialogTrigger,
} from '../../components/ui/alert-dialog';
import { Trash2 } from 'lucide-react';
import { getButtonStatusVariant } from '../../lib/utils';

const LOG_LEVELS = ['ERROR', 'WARNING', 'INFO', 'DEBUG'];

export function LogShow() {
  const { fileName } = useParams({ from: '/logs/$fileName' });
  const { data, isLoading, isError } = useLogContentQuery(fileName);
  const clearLogMutation = useClearLogMutation(fileName);
  const [filterLevel, setFilterLevel] = useState<string | null>(null);

  const logLines = useMemo(() => {
    if (!data?.content) return [];
    return data.content.split(/(?=\[\d{4}-\d{2}-\d{2}\s\d{2}:\d{2}:\d{2}\])/).filter(line => line.trim() !== '');
  }, [data]);

  const filteredLogs = useMemo(() => {
    if (!filterLevel) return logLines;
    return logLines.filter(line => line.includes(`.${filterLevel}:`));
  }, [logLines, filterLevel]);

  if (isLoading) return <p>Loading log content...</p>;
  if (isError) return <p className="text-red-500">Failed to fetch content for {fileName}.</p>;

  return (
    <Card className="subtle-shadow">
      <CardHeader>
        <div className="flex justify-between items-start">
            <div>
                <CardTitle>{data?.file}</CardTitle>
                <CardDescription>Displaying {filteredLogs.length} of {logLines.length} entries.</CardDescription>
            </div>
            <AlertDialog>
              <AlertDialogTrigger asChild>
                <Button variant="destructive" size="sm"><Trash2 className="mr-2 h-4 w-4" /> Clear Log</Button>
              </AlertDialogTrigger>
              <AlertDialogContent>
                <AlertDialogHeader>
                  <AlertDialogTitle>Are you sure?</AlertDialogTitle>
                  <AlertDialogDescription>
                    This will permanently clear the contents of <strong>{data?.file}</strong>. This action cannot be undone.
                  </AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                  <AlertDialogCancel>Cancel</AlertDialogCancel>
                  <AlertDialogAction onClick={() => clearLogMutation.mutate()}>Continue</AlertDialogAction>
                </AlertDialogFooter>
              </AlertDialogContent>
            </AlertDialog>
        </div>
        <div className="flex items-center space-x-2 pt-4">
            <Button variant={filterLevel === null ? 'default' : 'outline'} size="sm" onClick={() => setFilterLevel(null)}>All</Button>
            {LOG_LEVELS.map(level => (
                <Button key={level} variant={filterLevel === level ? getButtonStatusVariant(level) : 'outline'} size="sm" onClick={() => setFilterLevel(level)}>
                    {level}
                </Button>
            ))}
        </div>
      </CardHeader>
      <CardContent>
        <div className="bg-background rounded-md">
            {filteredLogs.map((log, index) => <LogEntry key={index} log={log} />)}
        </div>
      </CardContent>
    </Card>
  );
}