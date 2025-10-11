import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { fetchLogs, fetchLogContent, clearLogFile } from '../api/logs';

export const useLogsQuery = () => {
  return useQuery({
    queryKey: ['logs'],
    queryFn: fetchLogs,
  });
};

export const useLogContentQuery = (fileName: string) => {
  return useQuery({
    queryKey: ['logs', fileName],
    queryFn: () => fetchLogContent(fileName),
    enabled: !!fileName,
  });
}

export const useClearLogMutation = (fileName: string) => {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: () => clearLogFile(fileName),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['logs', fileName] });
      queryClient.invalidateQueries({ queryKey: ['logs'] });
    },
  });
};