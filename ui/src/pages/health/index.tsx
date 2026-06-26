
import { useHealthChecksQuery } from '@/queries/health';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { StatusBadge } from '@/components/app/status-badge';
import { CheckCircle2, XCircle, AlertTriangle, Skull, RefreshCw, Settings } from 'lucide-react';
import { formatDistanceToNow } from 'date-fns';
import { Link } from '@tanstack/react-router';
import type { LucideIcon } from 'lucide-react';

type HealthStatus = 'ok' | 'warning' | 'failed' | 'crashed';

const statusIcons: Record<HealthStatus, LucideIcon> = {
  ok: CheckCircle2,
  warning: AlertTriangle,
  failed: XCircle,
  crashed: Skull,
};

const statusIconColors: Record<HealthStatus, string> = {
  ok: 'text-success',
  warning: 'text-warning',
  failed: 'text-destructive',
  crashed: 'text-destructive',
};

export function HealthChecksIndex() {
  const { data, isLoading, isError, refetch, isFetching, dataUpdatedAt } = useHealthChecksQuery({
    refetchInterval: 30000,
    refetchIntervalInBackground: false,
  });

  if (isLoading) return <p>Loading health checks...</p>;
  if (isError) return <p className="text-destructive">Failed to load health checks.</p>;

  const overallStatus = (data?.overall_status ?? 'ok') as HealthStatus;
  const OverallIcon = statusIcons[overallStatus] ?? CheckCircle2;

  const statusCounts = {
    ok: data?.checks.filter(c => c.status === 'ok').length ?? 0,
    warning: data?.checks.filter(c => c.status === 'warning').length ?? 0,
    failed: data?.checks.filter(c => c.status === 'failed').length ?? 0,
    crashed: data?.checks.filter(c => c.status === 'crashed').length ?? 0,
  };

  return (
    <div className="space-y-6">
      <Card className="subtle-shadow">
        <CardHeader>
          <div className="flex items-center justify-between">
            <div className="flex items-center gap-4">
              <div className="p-3 rounded-full bg-muted">
                <OverallIcon className={`h-8 w-8 ${statusIconColors[overallStatus]}`} />
              </div>
              <div>
                <CardTitle>System Health</CardTitle>
                <CardDescription>
                  Overall system status is <span className="font-medium capitalize">{data?.overall_status}</span>
                </CardDescription>
                <div className="flex items-center gap-2 mt-2">
                  {statusCounts.ok > 0 && (
                    <Badge variant="success">{statusCounts.ok} OK</Badge>
                  )}
                  {statusCounts.warning > 0 && (
                    <Badge variant="warning">{statusCounts.warning} Warning</Badge>
                  )}
                  {statusCounts.failed > 0 && (
                    <Badge variant="destructive">{statusCounts.failed} Failed</Badge>
                  )}
                  {statusCounts.crashed > 0 && (
                    <Badge variant="destructive">{statusCounts.crashed} Crashed</Badge>
                  )}
                </div>
              </div>
            </div>
            <div className="flex items-center gap-2">
              <Link to="/health/settings">
                <Button variant="outline" size="sm">
                  <Settings className="mr-2 h-4 w-4" />
                  Settings
                </Button>
              </Link>
              <Button variant="outline" size="sm" onClick={() => refetch()} disabled={isFetching}>
                <RefreshCw className={`mr-2 h-4 w-4 ${isFetching ? 'animate-spin' : ''}`} />
                Refresh
              </Button>
            </div>
          </div>
          {dataUpdatedAt && (
            <p className="text-xs text-muted-foreground mt-2">
              Last checked {formatDistanceToNow(dataUpdatedAt, { addSuffix: true })}
            </p>
          )}
        </CardHeader>
      </Card>

      <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
        {data?.checks.map((check) => {
          const status = check.status as HealthStatus;
          const Icon = statusIcons[status] ?? CheckCircle2;

          return (
            <Card key={check.check} className="subtle-shadow">
              <CardHeader className="pb-3">
                <div className="flex items-start justify-between">
                  <div className="flex items-center gap-3">
                    <Icon className={`h-5 w-5 ${statusIconColors[status]}`} />
                    <div>
                      <CardTitle className="text-base">{check.label}</CardTitle>
                      {check.short_summary && (
                        <p className="text-xs text-muted-foreground mt-0.5">
                          {check.short_summary}
                        </p>
                      )}
                    </div>
                  </div>
                  <StatusBadge status={check.status} />
                </div>
              </CardHeader>
              <CardContent>
                <p className="text-sm text-muted-foreground">{check.message}</p>

                {Object.keys(check.meta).length > 0 && (
                  <div className="mt-3 pt-3 border-t">
                    <p className="text-xs font-medium mb-2">Details:</p>
                    <dl className="space-y-1">
                      {Object.entries(check.meta).map(([key, value]) => (
                        <div key={key} className="flex justify-between text-xs">
                          <dt className="text-muted-foreground">
                            {key.replace(/_/g, ' ').replace(/\b\w/g, (l) => l.toUpperCase())}:
                          </dt>
                          <dd className="font-mono font-medium">
                            {typeof value === 'boolean' ? (value ? 'Yes' : 'No') : String(value)}
                          </dd>
                        </div>
                      ))}
                    </dl>
                  </div>
                )}
              </CardContent>
            </Card>
          );
        })}
      </div>
    </div>
  );
}
