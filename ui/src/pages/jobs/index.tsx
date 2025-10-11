import { useState, useMemo } from 'react';
import { useJobsQuery } from '../../queries/jobs.ts';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '../../components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '../../components/ui/table';
import { Pagination } from '../../components/ui/pagination';
import { Button } from '../../components/ui/button';
import { Dialog } from '../../components/ui/dialog';
import { JobShowModal } from './show-modal.tsx';
import { formatDistanceToNow } from 'date-fns';
import { Job } from '../../api/types';
import { RefreshCw } from 'lucide-react';
import {
  useReactTable,
  getCoreRowModel,
  flexRender,
  ColumnDef,
  PaginationState,
} from '@tanstack/react-table';
import { StatusBadge } from '../../components/app/status-badge.tsx';

const columns: ColumnDef<Job>[] = [
  {
    accessorKey: 'status',
    header: 'Status',
    cell: ({ row }) => <StatusBadge status={row.original.status} />,
  },
  {
    accessorKey: 'name',
    header: 'Job',
    cell: ({ row }) => <span className="font-mono">{row.original.name}</span>,
  },
  {
    accessorKey: 'started_at',
    header: 'Processed',
    cell: ({ row }) => formatDistanceToNow(new Date(row.original.started_at), { addSuffix: true }),
  },
];

export function JobIndex() {
    const [{ pageIndex, pageSize }, setPagination] = useState<PaginationState>({
    pageIndex: 0,
    pageSize: 15,
  });
  
  const { data, isLoading, isError, refetch, isFetching, dataUpdatedAt } = useJobsQuery({
    page: pageIndex + 1,
    pageSize,
  });
  
  const [selectedJob, setSelectedJob] = useState<Job | null>(null);
  const defaultData = useMemo(() => [] as Job[], []);

 const table = useReactTable({
    data: data?.data ?? defaultData,
    columns,
    pageCount: data?.last_page ?? -1,
    state: { pagination: { pageIndex, pageSize } },
    onPaginationChange: setPagination,
    getCoreRowModel: getCoreRowModel(),
    manualPagination: true,
  });

  if (isError) return <p className="text-red-500">Failed to fetch jobs.</p>;

  return (
    <Dialog open={!!selectedJob} onOpenChange={(isOpen) => !isOpen && setSelectedJob(null)}>
      <Card className="subtle-shadow">
        <CardHeader>
          <div className="flex items-center justify-between">
            <div>
              <CardTitle>Queued Jobs</CardTitle>
              <CardDescription>Showing background jobs processed by your application.</CardDescription>
            </div>
            <div className="flex items-center gap-2">
                <span className="text-xs text-muted-foreground">
                    Last updated: {dataUpdatedAt ? formatDistanceToNow(new Date(dataUpdatedAt), { addSuffix: true }) : '...'}
                </span>
                <Button variant="outline" size="sm" onClick={() => refetch()} disabled={isFetching}>
                    <RefreshCw className={`mr-2 h-4 w-4 ${isFetching ? 'animate-spin' : ''}`} />
                    Refresh
                </Button>
            </div>
          </div>
        </CardHeader>
        <CardContent>
          <div className="rounded-md border">
            <Table>
              <TableHeader>
                {table.getHeaderGroups().map(headerGroup => (
                  <TableRow key={headerGroup.id}>
                    {headerGroup.headers.map(header => <TableHead key={header.id}>{flexRender(header.column.columnDef.header, header.getContext())}</TableHead>)}
                  </TableRow>
                ))}
              </TableHeader>
              <TableBody>
                {isLoading && !data ? (
                  <TableRow><TableCell colSpan={columns.length} className="h-24 text-center">Loading...</TableCell></TableRow>
                ) : table.getRowModel().rows.map(row => (
                  <TableRow 
                    key={row.id} 
                    className="cursor-pointer" 
                    onClick={() => setSelectedJob(row.original)}
                  >
                    {row.getVisibleCells().map(cell => (
                      <TableCell key={cell.id}>{flexRender(cell.column.columnDef.cell, cell.getContext())}</TableCell>
                    ))}
                  </TableRow>
                ))}
              </TableBody>
            </Table>
          </div>
          {data && (
            <Pagination
              currentPage={data.current_page}
              lastPage={data.last_page}
              total={data.total}
              perPage={data.per_page}
              onPageChange={(page) => setPagination({ pageIndex: page - 1, pageSize })}
            />
          )}
        </CardContent>
      </Card>
      <JobShowModal job={selectedJob} />
    </Dialog>
  );
}