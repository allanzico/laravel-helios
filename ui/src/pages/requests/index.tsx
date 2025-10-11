import { useState, useMemo } from 'react';
import { useRequestsQuery } from '../../queries/requests.ts';
import { usePurgeMutation } from '../../queries/purge.ts';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '../../components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '../../components/ui/table';
import { Pagination } from '../../components/ui/pagination';
import { Button } from '../../components/ui/button';
import { Dialog } from '../../components/ui/dialog';
import { AlertDialog, AlertDialogAction, AlertDialogCancel, AlertDialogContent, AlertDialogDescription, AlertDialogFooter, AlertDialogHeader, AlertDialogTitle, AlertDialogTrigger } from '../../components/ui/alert-dialog';
import { RequestShowModal } from './show-modal.tsx';
import { RequestType } from '../../api/types';
import { RefreshCw, Trash2 } from 'lucide-react';
import {
  useReactTable,
  getCoreRowModel,
  flexRender,
  ColumnDef,
  PaginationState,
} from '@tanstack/react-table';
import { StatusCodeBadge } from '../../components/app/status-code-badge.tsx';
import { Badge } from '../../components/ui/badge';
import { formatDistanceToNow } from 'date-fns';

const columns: ColumnDef<RequestType>[] = [
  {
    accessorKey: 'status_code',
    header: 'Status',
    cell: ({ row }) => <StatusCodeBadge code={row.original.status_code} />,
  },
  {
    accessorKey: 'uri',
    header: 'Request',
    cell: ({ row }) => (
      <div className="flex items-center gap-2">
        <Badge variant="outline">{row.original.method}</Badge>
        <span className="font-mono truncate">/{row.original.uri}</span>
      </div>
    ),
  },
  {
    accessorKey: 'duration_ms',
    header: () => <div className="text-right">Duration</div>,
    cell: ({ row }) => <div className="text-right">{row.original.duration_ms.toFixed(2)}ms</div>,
  },
  {
      accessorKey: 'created_at',
      header: 'Ran',
      cell: ({ row }) => formatDistanceToNow(new Date(row.original.created_at), { addSuffix: true }),
  },
];

export function RequestIndex() {
  const [{ pageIndex, pageSize }, setPagination] = useState<PaginationState>({
    pageIndex: 0,
    pageSize: 15,
  });
  const { data, isLoading, isError, refetch, isFetching, dataUpdatedAt } = useRequestsQuery({
    page: pageIndex + 1,
    pageSize,
  });
  const purgeMutation = usePurgeMutation(['requests']);
  const [selectedRequest, setSelectedRequest] = useState<RequestType | null>(null);
  const defaultData = useMemo(() => [] as RequestType[], []);

   const table = useReactTable({
    data: data?.data ?? defaultData,
    columns,
    pageCount: data?.last_page ?? -1,
    state: { pagination: { pageIndex, pageSize } },
    onPaginationChange: setPagination,
    getCoreRowModel: getCoreRowModel(),
    manualPagination: true,
  });


  if (isError) return <p className="text-red-500">Failed to fetch requests.</p>;

  return (
    <Dialog open={!!selectedRequest} onOpenChange={(isOpen) => !isOpen && setSelectedRequest(null)}>
      <Card className="subtle-shadow">
        <CardHeader>
          <div className="flex items-center justify-between">
            <div>
              <CardTitle>Requests</CardTitle>
              <CardDescription>Showing incoming web requests to your application.</CardDescription>
            </div>
            <div className="flex items-center gap-2">
                <AlertDialog>
                  <AlertDialogTrigger asChild><Button variant="destructive" size="sm"><Trash2 className="mr-2 h-4 w-4" /> Purge</Button></AlertDialogTrigger>
                  <AlertDialogContent>
                    <AlertDialogHeader><AlertDialogTitle>Are you sure?</AlertDialogTitle><AlertDialogDescription>This will permanently delete all recorded request history.</AlertDialogDescription></AlertDialogHeader>
                    <AlertDialogFooter><AlertDialogCancel>Cancel</AlertDialogCancel><AlertDialogAction onClick={() => purgeMutation.mutate('helios_requests')}>Continue</AlertDialogAction></AlertDialogFooter>
                  </AlertDialogContent>
                </AlertDialog>
            </div>
          </div>
        </CardHeader>
        <CardContent>
            <div className="flex items-center justify-end text-xs text-muted-foreground mb-2">
                Last updated: {dataUpdatedAt ? formatDistanceToNow(new Date(dataUpdatedAt), { addSuffix: true }) : '...'}
                <Button variant="ghost" size="sm" className="ml-2 h-auto p-1" onClick={() => refetch()} disabled={isFetching}><RefreshCw className={`h-3 w-3 ${isFetching ? 'animate-spin' : ''}`} /></Button>
            </div>
          <div className="rounded-md border">
            <Table>
              <TableHeader>
                {table.getHeaderGroups().map(headerGroup => (
                  <TableRow key={headerGroup.id}>{headerGroup.headers.map(header => <TableHead key={header.id}>{flexRender(header.column.columnDef.header, header.getContext())}</TableHead>)}</TableRow>
                ))}
              </TableHeader>
              <TableBody>
                {isLoading && !data ? (
                  <TableRow><TableCell colSpan={columns.length} className="h-24 text-center">Loading...</TableCell></TableRow>
                ) : table.getRowModel().rows.map(row => (
                  <TableRow key={row.id} className="cursor-pointer" onClick={() => setSelectedRequest(row.original)}>
                    {row.getVisibleCells().map(cell => <TableCell key={cell.id}>{flexRender(cell.column.columnDef.cell, cell.getContext())}</TableCell>)}
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
      <RequestShowModal request={selectedRequest} />
    </Dialog>
  );
}