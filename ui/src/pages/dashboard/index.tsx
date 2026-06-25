import { useDashboardStatsQuery } from '@/queries/dashboard';
import { StatCard } from '@/components/app/stat-card';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { StatusBadge } from '@/components/app/status-badge.tsx';
import { AlertTriangle, Clock, ServerCrash, Zap } from 'lucide-react';
import { RequestsChart } from '@/components/app/requests-chart.tsx';

export function DashboardIndex() {
  const { data, isLoading, isError } = useDashboardStatsQuery();

  if (isLoading) return <p>Loading dashboard...</p>;
  if (isError) return <p className="text-red-500">Failed to load dashboard stats.</p>;

  return (
    <div className="flex flex-col gap-6">
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

      <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-2">
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
