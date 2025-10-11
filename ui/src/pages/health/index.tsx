
import { useHealthChecksQuery } from '@/queries/health';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { CheckCircle2, XCircle, AlertTriangle, Skull, RefreshCw, Settings } from 'lucide-react';
import { formatDistanceToNow } from 'date-fns';
import { Link } from '@tanstack/react-router';

const statusConfig = {
  ok: { icon: CheckCircle2, color: 'text-green-500', bgColor: 'bg-green-50 dark:bg-green-950', variant: 'default' as const, badge: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' },
  warning: { icon: AlertTriangle, color: 'text-yellow-500', bgColor: 'bg-yellow-50 dark:bg-yellow-950', variant: 'warning' as const, badge: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' },
  failed: { icon: XCircle, color: 'text-red-500', bgColor: 'bg-red-50 dark:bg-red-950', variant: 'destructive' as const, badge: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' },
  crashed: { icon: Skull, color: 'text-red-700', bgColor: 'bg-red-100 dark:bg-red-900', variant: 'destructive' as const, badge: 'bg-red-200 text-red-900 dark:bg-red-800 dark:text-red-100' },
};

export function HealthChecksIndex() {
  const { data, isLoading, isError, refetch, isFetching, dataUpdatedAt } = useHealthChecksQuery({
    refetchInterval: 30000, // Poll every 30 seconds
    refetchIntervalInBackground: false,
  });

  if (isLoading) return <p>Loading health checks...</p>;
  if (isError) return <p className="text-red-500">Failed to load health checks.</p>;

  const overallConfig = statusConfig[data?.overall_status || 'ok'];
  const OverallIcon = overallConfig.icon;

  // Count checks by status
  const statusCounts = {
    ok: data?.checks.filter(c => c.status === 'ok').length || 0,
    warning: data?.checks.filter(c => c.status === 'warning').length || 0,
    failed: data?.checks.filter(c => c.status === 'failed').length || 0,
    crashed: data?.checks.filter(c => c.status === 'crashed').length || 0,
  };

  return (
    <div className="space-y-6">
      {/* Overall Status Card */}
      <Card className="subtle-shadow">
        <CardHeader>
          <div className="flex items-center justify-between">
            <div className="flex items-center gap-4">
              <div className={`p-3 rounded-full ${overallConfig.bgColor}`}>
                <OverallIcon className={`h-8 w-8 ${overallConfig.color}`} />
              </div>
              <div>
                <CardTitle>System Health</CardTitle>
                <CardDescription>
                  Overall system status is <span className="font-medium capitalize">{data?.overall_status}</span>
                </CardDescription>
                <div className="flex items-center gap-2 mt-2">
                  {statusCounts.ok > 0 && (
                    <Badge className="bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                      {statusCounts.ok} OK
                    </Badge>
                  )}
                  {statusCounts.warning > 0 && (
                    <Badge className="bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                      {statusCounts.warning} Warning
                    </Badge>
                  )}
                  {statusCounts.failed > 0 && (
                    <Badge className="bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                      {statusCounts.failed} Failed
                    </Badge>
                  )}
                  {statusCounts.crashed > 0 && (
                    <Badge className="bg-red-200 text-red-900 dark:bg-red-800 dark:text-red-100">
                      {statusCounts.crashed} Crashed
                    </Badge>
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

      {/* Individual Health Checks */}
      <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
        {data?.checks.map((check) => {
          const config = statusConfig[check.status];
          const Icon = config.icon;

          return (
            <Card key={check.check} className="subtle-shadow">
              <CardHeader className="pb-3">
                <div className="flex items-start justify-between">
                  <div className="flex items-center gap-3">
                    <Icon className={`h-5 w-5 ${config.color}`} />
                    <div>
                      <CardTitle className="text-base">{check.label}</CardTitle>
                      {check.short_summary && (
                        <p className="text-xs text-muted-foreground mt-0.5">
                          {check.short_summary}
                        </p>
                      )}
                    </div>
                  </div>
                  <Badge className={config.badge}>
                    {check.status}
                  </Badge>
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