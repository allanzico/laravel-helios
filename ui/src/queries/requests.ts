import { keepPreviousData, useQuery } from '@tanstack/react-query';
import { fetchRequests } from '../api/requests.ts';
import { PaginatedResponse, RequestType } from '@/api/types/index.ts';

export const useRequestsQuery = ({ page, pageSize }: { page: number, pageSize: number }) => {
  return useQuery<PaginatedResponse<RequestType>, Error>({
    queryKey: ['requests', { page, pageSize }],
    queryFn: () => fetchRequests({ page, pageSize }),
    placeholderData: keepPreviousData,
  });
};