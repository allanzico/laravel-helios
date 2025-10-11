import { useQuery, keepPreviousData } from '@tanstack/react-query';
import { fetchJobs } from '@/api/jobs';
import { Job, PaginatedResponse } from '@/api/types';

export const useJobsQuery = ({ page, pageSize }: { page: number, pageSize: number }) => {
  return useQuery<PaginatedResponse<Job>, Error>({ 
    queryKey: ['jobs', { page, pageSize }],
    queryFn: () => fetchJobs({ page, pageSize }),
    placeholderData: keepPreviousData, 
  });
};