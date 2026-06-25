import { useMutation, useQuery, useQueryClient, keepPreviousData } from '@tanstack/react-query';
import { fetchJobs, forgetJob, retryJob } from '@/api/jobs';
import { JobsResponse } from '@/api/types';

export const useJobsQuery = ({ page, pageSize }: { page: number, pageSize: number }) => {
  return useQuery<JobsResponse, Error>({ 
    queryKey: ['jobs', { page, pageSize }],
    queryFn: () => fetchJobs({ page, pageSize }),
    placeholderData: keepPreviousData, 
  });
};

export const useRetryJobMutation = () => {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: retryJob,
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['jobs'] });
      queryClient.invalidateQueries({ queryKey: ['dashboardStats'] });
    },
  });
};

export const useForgetJobMutation = () => {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: forgetJob,
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['jobs'] });
      queryClient.invalidateQueries({ queryKey: ['dashboardStats'] });
    },
  });
};
