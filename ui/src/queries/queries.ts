import { useQuery, keepPreviousData } from '@tanstack/react-query';
import { fetchQueries } from '@/api/queries.ts';
import { PaginatedResponse, Query } from '@/api/types';

export const useQueriesQuery = ({ page, pageSize }: { page: number, pageSize: number }) => {
  return useQuery<PaginatedResponse<Query>, Error>({
    queryKey: ['queries', { page, pageSize }],
    queryFn: () => fetchQueries({ page, pageSize }),
    placeholderData: keepPreviousData,
  });
};