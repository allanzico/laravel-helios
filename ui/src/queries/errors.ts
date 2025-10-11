import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { fetchErrors, fetchErrorStats, fetchError, resolveError, ignoreError, unresolveError, deleteError } from '../api/errors';

export const useErrorsQuery = (filters: any = {}) => {
  return useQuery({
    queryKey: ['errors', filters],
    queryFn: () => fetchErrors(filters),
  });
};

export const useErrorStatsQuery = () => {
  return useQuery({
    queryKey: ['errorStats'],
    queryFn: fetchErrorStats,
    refetchInterval: 30000, // Refetch every 30 seconds
  });
};

export const useErrorQuery = (id: string) => {
  return useQuery({
    queryKey: ['error', id],
    queryFn: () => fetchError(id),
    enabled: !!id,
  });
};

export const useResolveErrorMutation = () => {
  const queryClient = useQueryClient();
  
  return useMutation({
    mutationFn: resolveError,
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['errors'] });
      queryClient.invalidateQueries({ queryKey: ['errorStats'] });
    },
  });
};

export const useIgnoreErrorMutation = () => {
  const queryClient = useQueryClient();
  
  return useMutation({
    mutationFn: ignoreError,
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['errors'] });
      queryClient.invalidateQueries({ queryKey: ['errorStats'] });
    },
  });
};

export const useUnresolveErrorMutation = () => {
  const queryClient = useQueryClient();
  
  return useMutation({
    mutationFn: unresolveError,
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['errors'] });
      queryClient.invalidateQueries({ queryKey: ['errorStats'] });
    },
  });
};

export const useDeleteErrorMutation = () => {
  const queryClient = useQueryClient();
  
  return useMutation({
    mutationFn: deleteError,
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['errors'] });
      queryClient.invalidateQueries({ queryKey: ['errorStats'] });
    },
  });
};