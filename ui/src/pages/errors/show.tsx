import { useParams, useNavigate } from '@tanstack/react-router';
import { toast } from 'sonner';
import { useErrorQuery, useResolveErrorMutation, useIgnoreErrorMutation, useUnresolveErrorMutation, useDeleteErrorMutation } from '../../queries/errors';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '../../components/ui/card';
import { Badge } from '../../components/ui/badge';
import { Button } from '../../components/ui/button';
import { ArrowLeft, CheckCircle, EyeOff, RotateCcw, Trash2, AlertCircle, Clock, User, Globe, Monitor } from 'lucide-react';
import { format } from 'date-fns';

const statusConfig = {
  unresolved: { label: 'Unresolved', color: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' },
  resolved: { label: 'Resolved', color: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' },
  ignored: { label: 'Ignored', color: 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200' },
};

export function ErrorDetail() {
  const { id } = useParams({ from: '/errors/$id' });
  const navigate = useNavigate();
  const { data: error, isLoading } = useErrorQuery(id);
  const resolveMutation = useResolveErrorMutation();
  const ignoreMutation = useIgnoreErrorMutation();
  const unresolveMutation = useUnresolveErrorMutation();
  const deleteMutation = useDeleteErrorMutation();

  if (isLoading) return <p>Loading error details...</p>;
  if (!error) return <p>Error not found</p>;

  const statusCfg = statusConfig[error.status as keyof typeof statusConfig];

  const handleResolve = async () => {
    try {
      await resolveMutation.mutateAsync(error.id);
      toast.success('Error marked as resolved', {
        description: 'This error has been successfully resolved.'
      });
    } catch (err) {
      toast.error('Failed to resolve error', {
        description: 'An error occurred while marking this error as resolved.'
      });
    }
  };

  const handleIgnore = async () => {
    try {
      await ignoreMutation.mutateAsync(error.id);
      toast.success('Error ignored', {
        description: 'This error has been successfully ignored.'
      });
    } catch (err) {
      toast.error('Failed to ignore error', {
        description: 'An error occurred while marking this error as ignored.'
      });
    }
  };

  const handleUnresolve = async () => {
    try {
      await unresolveMutation.mutateAsync(error.id);
      toast.info('Error marked as unresolved', {
        description: 'This error has been marked as unresolved.'
      });
    } catch (err) {
      toast.error('Failed to unresolve error', {
        description: 'An error occurred while marking this error as unresolved.'
      });
    }
  };

  const handleDelete = async () => {
    if (confirm('Are you sure you want to delete this error?')) {
      try {
        await deleteMutation.mutateAsync(error.id);
        toast.success('Error deleted', {
          description: 'This error has been permanently deleted.'
        });
        navigate({ to: '/errors' });
      } catch (err) {
        toast.error('Failed to delete error', {
          description: 'An error occurred while deleting this error.'
        });
      }
    }
  };

  let requestData = {};
  try {
    requestData = error.request_data ? JSON.parse(error.request_data) : {};
  } catch (e) {
    // Invalid JSON
  }

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="flex items-center justify-between">
        <Button variant="ghost" onClick={() => navigate({ to: '/errors' })}>
          <ArrowLeft className="mr-2 h-4 w-4" />
          Back to Errors
        </Button>
        
        <div className="flex items-center gap-2">
          {error.status === 'unresolved' && (
            <>
              <Button variant="outline" size="sm" onClick={handleResolve}>
                <CheckCircle className="mr-2 h-4 w-4" />
                Mark as Resolved
              </Button>
              <Button variant="outline" size="sm" onClick={handleIgnore}>
                <EyeOff className="mr-2 h-4 w-4" />
                Ignore
              </Button>
            </>
          )}
          
          {error.status !== 'unresolved' && (
            <Button variant="outline" size="sm" onClick={handleUnresolve}>
              <RotateCcw className="mr-2 h-4 w-4" />
              Mark as Unresolved
            </Button>
          )}
          
          <Button variant="destructive" size="sm" onClick={handleDelete}>
            <Trash2 className="mr-2 h-4 w-4" />
            Delete
          </Button>
        </div>
      </div>

      {/* Error Overview */}
      <Card className={
        error.status === 'resolved'
          ? "border-green-200 dark:border-green-800 bg-green-50/50 dark:bg-green-950/20"
          : error.status === 'ignored'
          ? "border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/20"
          : ""
      }>
        <CardHeader>
          <div className="flex items-start justify-between">
            <div className="flex-1">
              <div className="flex items-center gap-2 mb-2">
                <AlertCircle className="h-5 w-5 text-red-500" />
                <CardTitle>{error.type.split('\\').pop()}</CardTitle>
                <Badge className={statusCfg.color}>{statusCfg.label}</Badge>
                <Badge variant="outline" className="capitalize">{error.level}</Badge>
                {error.occurrences > 1 && (
                  <Badge variant="secondary">{error.occurrences} occurrences</Badge>
                )}
              </div>
              <CardDescription className="text-base">{error.message}</CardDescription>
            </div>
          </div>
        </CardHeader>
        <CardContent>
          <div className="grid grid-cols-2 gap-4 text-sm">
            <div className="flex items-center gap-2">
              <Clock className="h-4 w-4 text-muted-foreground" />
              <span className="text-muted-foreground">First seen:</span>
              <span className="font-medium">{format(new Date(error.first_seen_at), 'PPpp')}</span>
            </div>
            <div className="flex items-center gap-2">
              <Clock className="h-4 w-4 text-muted-foreground" />
              <span className="text-muted-foreground">Last seen:</span>
              <span className="font-medium">{format(new Date(error.last_seen_at), 'PPpp')}</span>
            </div>
            {error.user_id && (
              <div className="flex items-center gap-2">
                <User className="h-4 w-4 text-muted-foreground" />
                <span className="text-muted-foreground">User ID:</span>
                <span className="font-medium">{error.user_id}</span>
              </div>
            )}
            <div className="flex items-center gap-2">
              <Globe className="h-4 w-4 text-muted-foreground" />
              <span className="text-muted-foreground">IP Address:</span>
              <span className="font-medium">{error.ip_address}</span>
            </div>
            <div className="flex items-center gap-2 col-span-2">
              <Monitor className="h-4 w-4 text-muted-foreground" />
              <span className="text-muted-foreground">Environment:</span>
              <span className="font-medium">{error.environment}</span>
            </div>
          </div>
        </CardContent>
      </Card>

      {/* Location */}
      <Card>
        <CardHeader>
          <CardTitle>Location</CardTitle>
        </CardHeader>
        <CardContent>
          <div className="space-y-2">
            <div>
              <span className="text-sm text-muted-foreground">File:</span>
              <p className="font-mono text-sm">{error.file}</p>
            </div>
            <div>
              <span className="text-sm text-muted-foreground">Line:</span>
              <p className="font-mono text-sm">{error.line}</p>
            </div>
            {error.url && (
              <div>
                <span className="text-sm text-muted-foreground">URL:</span>
                <p className="font-mono text-sm break-all">{error.url}</p>
              </div>
            )}
            {error.method && (
              <div>
                <span className="text-sm text-muted-foreground">Method:</span>
                <Badge variant="outline">{error.method}</Badge>
              </div>
            )}
          </div>
        </CardContent>
      </Card>

      {/* Stack Trace */}
      <Card>
        <CardHeader>
          <CardTitle>Stack Trace</CardTitle>
        </CardHeader>
        <CardContent>
          <pre className="bg-muted p-4 rounded-lg overflow-x-auto text-xs font-mono whitespace-pre-wrap">
            {error.trace}
          </pre>
        </CardContent>
      </Card>

      {/* Request Data */}
      {Object.keys(requestData).length > 0 && (
        <Card>
          <CardHeader>
            <CardTitle>Request Data</CardTitle>
          </CardHeader>
          <CardContent>
            <pre className="bg-muted p-4 rounded-lg overflow-x-auto text-xs font-mono">
              {JSON.stringify(requestData, null, 2)}
            </pre>
          </CardContent>
        </Card>
      )}

      {/* User Agent */}
      {error.user_agent && (
        <Card>
          <CardHeader>
            <CardTitle>User Agent</CardTitle>
          </CardHeader>
          <CardContent>
            <p className="text-sm font-mono">{error.user_agent}</p>
          </CardContent>
        </Card>
      )}
    </div>
  );
}