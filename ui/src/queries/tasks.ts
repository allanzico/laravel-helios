import { useQuery, UseQueryOptions } from '@tanstack/react-query';
import { fetchDefinedTasks } from '@/api/tasks';
import { ScheduledTask } from '@/api/types';

export const useDefinedTasksQuery = (options?: Omit<UseQueryOptions<ScheduledTask[], Error>, 'queryKey' | 'queryFn'>) => {
  return useQuery({
    queryKey: ['definedTasks'],
    queryFn: fetchDefinedTasks,
    ...options,
  });
};