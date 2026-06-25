import { RequestType, PaginatedResponse } from './types';
import { heliosApi } from './client';


export const fetchRequests = async ({ page, pageSize }: { page: number, pageSize: number }): Promise<PaginatedResponse<RequestType>> => {
  const response = await fetch(heliosApi(`requests?page=${page}&per_page=${pageSize}`));

  if (!response.ok) {
    throw new Error('Network response was not ok');
  }

  const data = await response.json();
  
  return data.requests;
};
