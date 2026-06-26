import { Link } from '@tanstack/react-router';
import { useLogsQuery } from '@/queries/logs';
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from '@/components/ui/card';
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table';

export function LogIndex() {
  const { data: logs, isLoading, isError } = useLogsQuery();

  if (isLoading) return <p>Loading logs...</p>;
  if (isError) return <p className="text-destructive">Failed to fetch logs.</p>;

  return (
    <Card>
      <CardHeader>
        <CardTitle>Log Files</CardTitle>
        <CardDescription>
          Showing all log files found in the storage directory.
        </CardDescription>
      </CardHeader>
      <CardContent>
        <Table>
          <TableHeader>
            <TableRow>
              <TableHead>File Name</TableHead>
              <TableHead className="text-right">Size</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            {logs && logs.length > 0 ? (
              logs.map((log) => (
                <TableRow key={log.name} className="hover:bg-muted/50">
                  <TableCell className="font-medium">
                    <Link
                      to="/logs/$fileName"
                      params={{ fileName: log.name }}
                      className="hover:underline"
                    >
                      {log.name}
                    </Link>
                  </TableCell>
                  <TableCell className="text-right">{log.size}</TableCell>
                </TableRow>
              ))
            ) : (
              <TableRow>
                <TableCell colSpan={2} className="text-center">
                  No log files found.
                </TableCell>
              </TableRow>
            )}
          </TableBody>
        </Table>
      </CardContent>
    </Card>
  );
}