import { Fragment, useState } from 'react';
import { useDefinedTasksQuery } from '@/queries/tasks.ts';
import { useQueryClient } from '@tanstack/react-query';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Button } from '@/components/ui/button';
import { StatusBadge } from '@/components/app/status-badge.tsx';
import { format, formatDistanceToNow, parseISO } from 'date-fns';
import { PlayCircle, ChevronDown, Clock, Terminal, CheckCircle2, XCircle, RefreshCw } from 'lucide-react';
import { ScheduledTask } from '@/api/types';
import { csrfToken, heliosApi } from '@/api/client';

export function ScheduledTaskIndex() {
  const queryClient = useQueryClient();
  const [runningCommand, setRunningCommand] = useState<string | null>(null);
  const [openOutputs, setOpenOutputs] = useState<Set<string>>(new Set());
  const [liveOutput, setLiveOutput] = useState<Map<string, string[]>>(new Map());

  // Fetch tasks with smart polling
  const { data: tasks, isLoading, isError, dataUpdatedAt, isFetching } = useDefinedTasksQuery({
    refetchInterval: (query) => {
      const data = query.state.data;
      // Check if any task is currently in "starting" status (running via scheduler)
      const hasRunningTasks = data?.some(task => task.latest_run?.status === 'starting');
      // Poll every 2 seconds if there are running tasks, otherwise every 10 seconds
      return hasRunningTasks ? 2000 : 10000;
    },
    refetchIntervalInBackground: false, // Only poll when tab is active
  });

  const handleRunTask = async (signature: string) => {
    setRunningCommand(signature);
    setLiveOutput(prev => new Map(prev).set(signature, []));
    
    // Auto-open the output section when running
    setOpenOutputs(prev => new Set(prev).add(signature));

    try {
      const response = await fetch(heliosApi('scheduled-tasks/run'), {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrfToken(),
        },
        body: JSON.stringify({ signature }),
      });

      if (!response.ok) {
        const errorText = await response.text();
        throw new Error(`Server error: ${response.status} ${response.statusText} - ${errorText}`);
      }

      const reader = response.body?.getReader();
      if (!reader) {
        throw new Error('Failed to get response reader');
      }

      const decoder = new TextDecoder();

      while (true) {
        const { done, value } = await reader.read();
        if (done) break;

        const chunk = decoder.decode(value, { stream: true });
        const messages = chunk.split('\n\n').filter(Boolean);

        messages.forEach(messageString => {
          if (messageString.startsWith('data: ')) {
            const jsonData = messageString.substring(6);
            const message = JSON.parse(jsonData);
            
            setLiveOutput(prev => {
              const map = new Map(prev);
              const current = map.get(signature) || [];
              map.set(signature, [...current, message]);
              return map;
            });
          }
        });
      }
    } catch (err) {
      const errorMessage = err instanceof Error ? err.message : String(err);
      setLiveOutput(prev => {
        const map = new Map(prev);
        const current = map.get(signature) || [];
        map.set(signature, [...current, `--- ERROR ---`, errorMessage]);
        return map;
      });
    } finally {
      setRunningCommand(null);
      // Refetch immediately to get updated data from database
      queryClient.invalidateQueries({ queryKey: ['definedTasks'] });
      // Clear live output after refetch completes
      setTimeout(() => {
        setLiveOutput(prev => {
          const map = new Map(prev);
          map.delete(signature);
          return map;
        });
      }, 2000);
    }
  };

  const toggleOutput = (signature: string) => {
    setOpenOutputs(prev => {
      const newSet = new Set(prev);
      if (newSet.has(signature)) {
        newSet.delete(signature);
      } else {
        newSet.add(signature);
      }
      return newSet;
    });
  };

  const getTaskOutput = (task: ScheduledTask): string[] => {
    // If task is currently running, show live output
    if (runningCommand === task.signature && liveOutput.has(task.signature)) {
      return liveOutput.get(task.signature) || [];
    }
    
    // Otherwise, show output from database (latest_run)
    if (task.latest_run?.output) {
      return task.latest_run.output.split('\n').filter(line => line.trim() !== '');
    }
    
    // If task has been run but has no output, show a default message
    if (task.latest_run) {
      return ['(No output produced by this command)'];
    }
    
    return [];
  };

  const getTaskRuntime = (task: ScheduledTask): string => {
    const ms = task.latest_run?.runtime_ms;
    if (!ms) return '0s';
    if (ms < 1) return `${ms.toFixed(2)}ms`;
    if (ms < 1000) return `${Math.round(ms)}ms`;
    return `${(ms / 1000).toFixed(2)}s`;
  };

  const formatDate = (dateString: string | null | undefined) => {
    if (!dateString) return <span className="text-muted-foreground">Never</span>;
    
    const date = parseISO(dateString);
    const now = new Date();
    const diffInHours = Math.abs(now.getTime() - date.getTime()) / (1000 * 60 * 60);
    
    if (diffInHours < 24) {
      return (
        <span title={format(date, 'PPpp')} className="cursor-help">
          {formatDistanceToNow(date, { addSuffix: true })}
        </span>
      );
    }
    
    return <span>{format(date, 'MMM d, yyyy HH:mm:ss')}</span>;
  };

  const getLastRunDate = (task: ScheduledTask): string | null | undefined => {
    return task.latest_run?.finished_at;
  };

  const getTaskStatus = (task: ScheduledTask): string | null => {
    return task.latest_run?.status || null;
  };

  if (isLoading) return <p>Loading scheduled tasks...</p>;
  if (isError) return <p className="text-red-500">Failed to fetch tasks.</p>;

  return (
    <Card className="subtle-shadow">
      <CardHeader>
        <div className="flex items-center justify-between">
          <div>
            <CardTitle>Scheduled Tasks</CardTitle>
            <CardDescription>All tasks defined in your application's schedule.</CardDescription>
          </div>
          <div className="flex items-center gap-2 text-xs text-muted-foreground">
            {isFetching && (
              <RefreshCw className="h-3 w-3 animate-spin" />
            )}
            <span>
              {dataUpdatedAt ? `Updated ${formatDistanceToNow(dataUpdatedAt, { addSuffix: true })}` : 'Loading...'}
            </span>
          </div>
        </div>
      </CardHeader>
      <CardContent>
        <div className="rounded-md border">
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>Status</TableHead>
                <TableHead>Command</TableHead>
                <TableHead>Expression</TableHead>
                <TableHead>Last Run</TableHead>
                <TableHead>Next Run</TableHead>
                <TableHead className="text-right">Actions</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              {tasks?.map((task: ScheduledTask) => {
                const taskOutput = getTaskOutput(task);
                const isOutputOpen = openOutputs.has(task.signature);
                const isRunning = runningCommand === task.signature;
                
                // Show output section if: currently running OR has been run at least once (has latest_run)
                const showOutputSection = isRunning || task.latest_run !== null;

                return (
                  <Fragment key={task.command}>
                    <TableRow>
                      <TableCell>
                        {getTaskStatus(task) ? <StatusBadge status={getTaskStatus(task)!} /> : <span className="text-muted-foreground">-</span>}
                      </TableCell>
                      <TableCell>
                        <div className="font-mono">{task.signature}</div>
                        <div className="text-xs text-muted-foreground">{task.description}</div>
                      </TableCell>
                      <TableCell className="font-mono">{task.expression}</TableCell>
                      <TableCell>{formatDate(getLastRunDate(task))}</TableCell>
                      <TableCell>{formatDate(task.next_run_at)}</TableCell>
                      <TableCell className="text-right">
                        <Button 
                          variant="outline" 
                          size="sm" 
                          onClick={() => handleRunTask(task.signature)} 
                          disabled={!!runningCommand || !task.can_run}
                          title={task.can_run ? 'Run this scheduled task' : 'Manual runs are disabled or this task is not allowlisted'}
                        >
                          <PlayCircle className="mr-2 h-4 w-4" />
                          {task.can_run ? 'Run' : 'Disabled'}
                        </Button>
                      </TableCell>
                    </TableRow>
                    
                    {/* Output Section - Always show if there's output or if running */}
                    {showOutputSection && (
                      <TableRow>
                        <TableCell colSpan={6} className="p-0">
                          <div className="border-t">
                            {/* Collapsible Header with Metrics */}
                            <button
                              onClick={() => toggleOutput(task.signature)}
                              className="w-full px-4 py-3 flex items-center justify-between hover:bg-muted/50 transition-colors"
                            >
                              <div className="flex items-center gap-6">
                                <div className="flex items-center gap-2">
                                  <Terminal className="h-4 w-4 text-muted-foreground" />
                                  <span className="font-medium">Task Output</span>
                                </div>
                                
                                {/* Metrics */}
                                <div className="flex items-center gap-4 text-sm">
                                  <div className="flex items-center gap-1.5">
                                    <Clock className="h-3.5 w-3.5 text-blue-500" />
                                    <span className="text-muted-foreground">Runtime:</span>
                                    <span className="font-mono font-medium">{getTaskRuntime(task)}</span>
                                  </div>
                                  
                                  {task.latest_run?.exit_code !== null && task.latest_run?.exit_code !== undefined && (
                                    <div className="flex items-center gap-1.5">
                                      {task.latest_run.exit_code === 0 ? (
                                        <>
                                          <CheckCircle2 className="h-3.5 w-3.5 text-green-500" />
                                          <span className="text-green-600 dark:text-green-400 font-medium">Success</span>
                                        </>
                                      ) : (
                                        <>
                                          <XCircle className="h-3.5 w-3.5 text-red-500" />
                                          <span className="text-red-600 dark:text-red-400 font-medium">
                                            Failed (Exit: {task.latest_run.exit_code})
                                          </span>
                                        </>
                                      )}
                                    </div>
                                  )}
                                  
                                  {isRunning && (
                                    <div className="flex items-center gap-1.5">
                                      <div className="h-2 w-2 bg-blue-500 rounded-full animate-pulse" />
                                      <span className="text-blue-600 dark:text-blue-400 font-medium">Running...</span>
                                    </div>
                                  )}
                                </div>
                              </div>
                              
                              <ChevronDown 
                                className={`h-4 w-4 text-muted-foreground transition-transform ${
                                  isOutputOpen ? 'transform rotate-180' : ''
                                }`}
                              />
                            </button>

                            {/* Collapsible Output */}
                            {isOutputOpen && (
                              <div className="px-4 pb-4 pt-2">
                                <div className="bg-muted rounded-md p-4 font-mono text-xs whitespace-pre-wrap max-h-96 overflow-y-auto">
                                  {taskOutput.map((line, i) => (
                                    <div key={i} className="leading-relaxed">{line}</div>
                                  ))}
                                  {isRunning && (
                                    <div className="mt-2 text-blue-500 animate-pulse">▊</div>
                                  )}
                                </div>
                              </div>
                            )}
                          </div>
                        </TableCell>
                      </TableRow>
                    )}
                  </Fragment>
                );
              })}
            </TableBody>
          </Table>
        </div>
      </CardContent>
    </Card>
  );
}
