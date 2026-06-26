import { useState } from 'react';
import { useErrorsQuery, useErrorStatsQuery } from '@/queries/errors';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge, type badgeVariants } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Pagination } from '@/components/ui/pagination';
import { AlertCircle, AlertTriangle, XCircle, Clock, Search } from 'lucide-react';
import { formatDistanceToNow } from 'date-fns';
import { useNavigate } from '@tanstack/react-router';
import type { VariantProps } from 'class-variance-authority';
import type { LucideIcon } from 'lucide-react';

type BadgeVariant = VariantProps<typeof badgeVariants>['variant'];

const statusBadgeVariants: Record<string, BadgeVariant> = {
  unresolved: 'destructive',
  resolved: 'success',
  ignored: 'secondary',
};

const statusLabels: Record<string, string> = {
  unresolved: 'Unresolved',
  resolved: 'Resolved',
  ignored: 'Ignored',
};

const levelIcons: Record<string, LucideIcon> = {
  error: AlertCircle,
  critical: XCircle,
  warning: AlertTriangle,
};

const levelIconColors: Record<string, string> = {
  error: 'text-destructive',
  critical: 'text-destructive',
  warning: 'text-warning',
};

export function ErrorsIndex() {
  const navigate = useNavigate();
  const [filters, setFilters] = useState({
    status: '',
    level: '',
    search: '',
    page: 1,
  });

  const { data: stats } = useErrorStatsQuery();
  const { data: errorsData, isLoading } = useErrorsQuery(filters);

  const errors = errorsData?.data || [];
  const pagination = errorsData?.meta || errorsData;

  return (
    <div className="space-y-6">
      {/* Stats Cards */}
      <div className="grid gap-4 md:grid-cols-4">
        <Card>
          <CardHeader className="pb-2">
            <CardDescription>Total Errors</CardDescription>
            <CardTitle className="text-3xl">{stats?.total_errors || 0}</CardTitle>
          </CardHeader>
        </Card>
        
        <Card>
          <CardHeader className="pb-2">
            <CardDescription>Unresolved</CardDescription>
            <CardTitle className="text-3xl text-destructive">{stats?.unresolved || 0}</CardTitle>
          </CardHeader>
        </Card>
        
        <Card>
          <CardHeader className="pb-2">
            <CardDescription>Last 24h</CardDescription>
            <CardTitle className="text-3xl">{stats?.last_24h || 0}</CardTitle>
          </CardHeader>
        </Card>
        
        <Card>
          <CardHeader className="pb-2">
            <CardDescription>Critical</CardDescription>
            <CardTitle className="text-3xl text-destructive">{stats?.critical || 0}</CardTitle>
          </CardHeader>
        </Card>
      </div>

      {/* Filters */}
      <Card>
        <CardHeader>
          <CardTitle>Error Tracking</CardTitle>
          <CardDescription>Monitor and manage application errors</CardDescription>
        </CardHeader>
        <CardContent>
          <div className="flex gap-4 mb-6">
            <div className="flex-1">
              <div className="relative">
                <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                <Input
                  placeholder="Search errors..."
                  value={filters.search}
                  onChange={(e) => setFilters({ ...filters, search: e.target.value })}
                  className="pl-10"
                />
              </div>
            </div>
            
            <Select value={filters.status || "all"} onValueChange={(value) => setFilters({ ...filters, status: value === "all" ? "" : value })}>
              <SelectTrigger className="w-[180px]">
                <SelectValue placeholder="Status" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="all">All Status</SelectItem>
                <SelectItem value="unresolved">Unresolved</SelectItem>
                <SelectItem value="resolved">Resolved</SelectItem>
                <SelectItem value="ignored">Ignored</SelectItem>
              </SelectContent>
            </Select>
            
            <Select value={filters.level || "all"} onValueChange={(value) => setFilters({ ...filters, level: value === "all" ? "" : value })}>
              <SelectTrigger className="w-[180px]">
                <SelectValue placeholder="Level" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="all">All Levels</SelectItem>
                <SelectItem value="error">Error</SelectItem>
                <SelectItem value="critical">Critical</SelectItem>
                <SelectItem value="warning">Warning</SelectItem>
              </SelectContent>
            </Select>
          </div>

          {/* Error Table */}
          {isLoading && <p className="text-muted-foreground">Loading errors...</p>}

          {!isLoading && errors.length === 0 && (
            <p className="text-muted-foreground text-center py-8">No errors found</p>
          )}

          {!isLoading && errors.length > 0 && (
            <>
              <div className="rounded-md border">
                <Table>
                  <TableHeader>
                    <TableRow>
                      <TableHead className="w-[50px]"></TableHead>
                      <TableHead>Error Type</TableHead>
                      <TableHead>Message</TableHead>
                      <TableHead>Location</TableHead>
                      <TableHead>Status</TableHead>
                      <TableHead>Occurrences</TableHead>
                      <TableHead>Last Seen</TableHead>
                    </TableRow>
                  </TableHeader>
                  <TableBody>
                    {errors.map((error: any) => {
                      const LevelIcon = levelIcons[error.level] ?? AlertCircle;

                      return (
                        <TableRow
                          key={error.id}
                          className="cursor-pointer"
                          onClick={() => navigate({ to: '/errors/$id', params: { id: error.id } })}
                        >
                          <TableCell>
                            <LevelIcon className={`h-5 w-5 ${levelIconColors[error.level] ?? 'text-muted-foreground'}`} />
                          </TableCell>
                          <TableCell className="font-medium">
                            <div className="flex flex-col">
                              <span className="truncate max-w-xs">{error.type.split('\\').pop()}</span>
                              <span className="text-xs text-muted-foreground truncate max-w-xs">{error.type}</span>
                            </div>
                          </TableCell>
                          <TableCell>
                            <div className="max-w-md truncate" title={error.message}>
                              {error.message}
                            </div>
                          </TableCell>
                          <TableCell>
                            <div className="text-sm text-muted-foreground truncate max-w-xs" title={error.file}>
                              {error.file}:{error.line}
                            </div>
                          </TableCell>
                          <TableCell>
                            <Badge variant={statusBadgeVariants[error.status] ?? 'outline'}>
                              {statusLabels[error.status] ?? error.status}
                            </Badge>
                          </TableCell>
                          <TableCell>
                            {error.occurrences > 1 ? (
                              <Badge variant="outline">{error.occurrences}x</Badge>
                            ) : (
                              <span className="text-muted-foreground">1</span>
                            )}
                          </TableCell>
                          <TableCell>
                            <div className="flex items-center gap-1 text-sm">
                              <Clock className="h-3 w-3" />
                              {formatDistanceToNow(new Date(error.last_seen_at), { addSuffix: true })}
                            </div>
                          </TableCell>
                        </TableRow>
                      );
                    })}
                  </TableBody>
                </Table>
              </div>

              {pagination && (
                <Pagination
                  currentPage={pagination.current_page || filters.page}
                  lastPage={pagination.last_page || 1}
                  total={pagination.total || errors.length}
                  perPage={pagination.per_page || 20}
                  onPageChange={(page) => setFilters({ ...filters, page })}
                />
              )}
            </>
          )}
        </CardContent>
      </Card>
    </div>
  );
}