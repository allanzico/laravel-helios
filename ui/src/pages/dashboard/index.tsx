import { useDashboardStatsQuery } from '@/queries/dashboard';
import { StatCard } from '@/components/app/stat-card';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { StatusBadge } from '@/components/app/status-badge.tsx';
import { AlertTriangle, CheckCircle2, Clock, ServerCrash, Zap } from 'lucide-react';
import { RequestsChart } from '@/components/app/requests-chart.tsx';
import { Badge } from '@/components/ui/badge';
import { formatDistanceToNow } from 'date-fns';

const formatActionName = (action: string) => (
  action
    .replace(/_/g, ' ')
    .replace(/\b\w/g, letter => letter.toUpperCase())
);

const formatActionTarget = (targetType: string | null, targetId: string | null) => {
  if (!targetType && !targetId) {
    return 'No target';
  }

  return [
    targetType?.replace(/_/g, ' '),
    targetId,
  ].filter(Boolean).join(' ');
};

export function DashboardIndex() {
  const { data, isLoading, isError } = useDashboardStatsQuery();

  if (isLoading) return <p>Loading dashboard...</p>;
  if (isError) return <p className="text-destructive">Failed to load dashboard stats.</p>;

  return (
    <div className="flex flex-col gap-6">
      <Card className="subtle-shadow">
        <CardHeader className="pb-3">
          <div className="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
            <div className="flex items-center gap-3">
              {data?.health?.overall_status === 'ok' ? (
                <CheckCircle2 className="h-5 w-5 text-success" />
              ) : (
                <AlertTriangle className="h-5 w-5 text-warning" />
              )}
              <div>
                <CardTitle>Operational Status</CardTitle>
                <p className="text-sm text-muted-foreground">
                  {data?.health?.problem_count
                    ? `${data.health.problem_count} of ${data.health.total_checks} checks need attention.`
                    : `${data?.health?.total_checks ?? 0} checks healthy.`}
                </p>
              </div>
            </div>
            <Badge variant={data?.health?.overall_status === 'ok' ? 'default' : 'destructive'}>
              {data?.health?.overall_status ?? 'unknown'}
            </Badge>
          </div>
        </CardHeader>
        {data?.health?.problems && data.health.problems.length > 0 && (
          <CardContent className="pt-0">
            <div className="grid gap-2 md:grid-cols-2">
              {data.health.problems.map((problem) => (
                <div key={problem.check} className="flex items-start justify-between gap-3 rounded-md border px-3 py-2">
                  <div className="min-w-0">
                    <div className="truncate text-sm font-medium">{problem.check}</div>
                    <div className="text-xs text-muted-foreground">{problem.message}</div>
                  </div>
                  <StatusBadge status={problem.status} />
                </div>
              ))}
            </div>
          </CardContent>
        )}
      </Card>

      <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
        <StatCard title="Failed Jobs (24h)" value={data?.failed_jobs_24h ?? 0} icon={ServerCrash} />
        <StatCard title="Errors (24h)" value={data?.errors_24h ?? data?.http_errors_24h ?? 0} icon={AlertTriangle} />
        <StatCard title="Avg. Duration" value={`${data?.avg_duration_24h ?? 0}ms`} icon={Zap} />
        <StatCard title="Avg. Memory" value={`${data?.avg_memory_24h ?? 0} MB`} icon={Clock} />
      </div>
            <Card className="subtle-shadow">
        <CardHeader>
            <CardTitle>Requests Per Minute (Last Hour)</CardTitle>
        </CardHeader>
        <CardContent>
            <RequestsChart />
        </CardContent>
      </Card>

      <div className="grid gap-4 lg:grid-cols-3">
        {/* LATEST FAILED TASKS */}
        <Card className="subtle-shadow">
          <CardHeader><CardTitle>Latest Failed Tasks</CardTitle></CardHeader>
          <CardContent>
            <Table>
              <TableHeader><TableRow><TableHead>Task</TableHead><TableHead>Status</TableHead></TableRow></TableHeader>
              <TableBody>
                {data?.latest_failed_tasks.map((task, index) => (
                  <TableRow key={index}>
                    <TableCell className="font-mono">{task.command}</TableCell>
                    <TableCell><StatusBadge status={task.latest_run?.status || 'failed'} /></TableCell>
                  </TableRow>
                ))}
              </TableBody>
            </Table>
          </CardContent>
        </Card>

        <Card className="subtle-shadow">
          <CardHeader><CardTitle>Recent Actions</CardTitle></CardHeader>
          <CardContent>
            {data?.recent_actions?.length ? (
              <div className="grid gap-2">
                {data.recent_actions.map(action => (
                  <div key={action.id} className="flex items-start justify-between gap-3 rounded-md border px-3 py-2">
                    <div className="min-w-0">
                      <div className="truncate text-sm font-medium">{formatActionName(action.action)}</div>
                      <div className="truncate text-xs text-muted-foreground">
                        {formatActionTarget(action.target_type, action.target_id)}
                      </div>
                    </div>
                    <div className="shrink-0 text-right">
                      <StatusBadge status={action.status} />
                      <div className="mt-1 text-xs text-muted-foreground">
                        {formatDistanceToNow(new Date(action.created_at), { addSuffix: true })}
                      </div>
                    </div>
                  </div>
                ))}
              </div>
            ) : (
              <p className="text-sm text-muted-foreground">No manual actions recorded.</p>
            )}
          </CardContent>
        </Card>

        {/* LATEST SLOW QUERIES */}
        <Card className="subtle-shadow">
          <CardHeader><CardTitle>Latest Slow Queries</CardTitle></CardHeader>
          <CardContent>
            <Table>
              <TableHeader><TableRow><TableHead>Query</TableHead><TableHead className="text-right">Time</TableHead></TableRow></TableHeader>
              <TableBody>
                {data?.latest_slow_queries.map(query => (
                  <TableRow key={query.id}>
                    <TableCell className="font-mono truncate max-w-[200px]">{query.sql}</TableCell>
                    <TableCell className="text-right">{query.time_ms.toFixed(2)}ms</TableCell>
                  </TableRow>
                ))}
              </TableBody>
            </Table>
          </CardContent>
        </Card>
      </div>
    </div>
  );
}
