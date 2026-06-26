import { useState, useMemo } from 'react';
import { useQueriesQuery } from '@/queries/queries.ts';
import { usePurgeMutation } from '@/queries/purge.ts';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Pagination } from '@/components/ui/pagination';
import { Button } from '@/components/ui/button';
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
} from '@/components/ui/alert-dialog';
import {
  useReactTable,
  getCoreRowModel,
  flexRender,
  ColumnDef,
  PaginationState,
} from '@tanstack/react-table';
import { Query } from '@/api/types';
import { heliosActionAllowed } from '@/api/client';
import { formatDistanceToNow } from 'date-fns';
import { Trash2 } from 'lucide-react';

const columns: ColumnDef<Query>[] = [
    {
        accessorKey: 'sql',
        header: 'Query',
        cell: ({ row }) => <span className="font-mono truncate">{row.original.sql}</span>,
    },
    {
        accessorKey: 'time_ms',
        header: () => <div className="text-right">Time</div>,
        cell: ({ row }) => <div className="text-right">{row.original.time_ms.toFixed(2)}ms</div>,
    },
    {
        accessorKey: 'created_at',
        header: 'Ran',
        cell: ({ row }) => formatDistanceToNow(new Date(row.original.created_at), { addSuffix: true }),
    },
];


export function QueryIndex() {
  const [{ pageIndex, pageSize }, setPagination] = useState<PaginationState>({ pageIndex: 0, pageSize: 25 });
  const { data, isLoading, isError } = useQueriesQuery({ page: pageIndex + 1, pageSize });
  const purgeMutation = usePurgeMutation(['queries']);
  const canPurge = heliosActionAllowed('purgeData');

  const defaultData = useMemo(() => [] as Query[], []);

  const table = useReactTable({
    data: data?.data ?? defaultData,
    columns,
    pageCount: data?.last_page ?? -1,
    state: { pagination: { pageIndex, pageSize } },
    onPaginationChange: setPagination,
    getCoreRowModel: getCoreRowModel(),
    manualPagination: true,
  });

  if (isError) return <p className="text-destructive">Failed to fetch queries.</p>;

  return (
    <Card className="subtle-shadow">
      <CardHeader className="flex flex-row items-center justify-between">
        <div>
            <CardTitle>Database Queries</CardTitle>
            <CardDescription>Showing executed database queries.</CardDescription>
        </div>
        <AlertDialog>
          <AlertDialogTrigger asChild>
            <Button
              variant="destructive"
              size="sm"
              disabled={!canPurge}
              title={canPurge ? 'Purge recorded queries' : 'Purge actions are disabled'}
            >
              <Trash2 className="mr-2 h-4 w-4" /> Purge
            </Button>
          </AlertDialogTrigger>
          <AlertDialogContent>
            <AlertDialogHeader>
              <AlertDialogTitle>Are you sure?</AlertDialogTitle>
              <AlertDialogDescription>
                This action cannot be undone. This will permanently delete all
                recorded queries.
              </AlertDialogDescription>
            </AlertDialogHeader>
            <AlertDialogFooter>
              <AlertDialogCancel>Cancel</AlertDialogCancel>
              <AlertDialogAction onClick={() => purgeMutation.mutate('helios_queries')}>Continue</AlertDialogAction>
            </AlertDialogFooter>
          </AlertDialogContent>
        </AlertDialog>
      </CardHeader>
      <CardContent>
        <div className="rounded-md border">
            <Table>
                <TableHeader>
                    {table.getHeaderGroups().map(headerGroup => (
                        <TableRow key={headerGroup.id}>
                        {headerGroup.headers.map(header => (
                            <TableHead key={header.id}>
                            {flexRender(header.column.columnDef.header, header.getContext())}
                            </TableHead>
                        ))}
                        </TableRow>
                    ))}
                </TableHeader>
                <TableBody>
                    {isLoading && !data ? (
                        <TableRow><TableCell colSpan={columns.length} className="h-24 text-center">Loading...</TableCell></TableRow>
                    ) : table.getRowModel().rows.length > 0 ? (
                        table.getRowModel().rows.map(row => (
                        <TableRow key={row.id}>
                            {row.getVisibleCells().map(cell => (
                            <TableCell key={cell.id}>
                                {flexRender(cell.column.columnDef.cell, cell.getContext())}
                            </TableCell>
                            ))}
                        </TableRow>
                        ))
                    ) : (
                        <TableRow><TableCell colSpan={columns.length} className="h-24 text-center">No results found.</TableCell></TableRow>
                    )}
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
  );
}
