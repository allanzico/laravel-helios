import { useQuery } from '@tanstack/react-query';
import { fetchRequestsPerMinute } from '@/api/charts.ts';

export const useRequestsPerMinuteQuery = () => {
  return useQuery({
    queryKey: ['requestsPerMinute'],
    queryFn: fetchRequestsPerMinute,
  });
};