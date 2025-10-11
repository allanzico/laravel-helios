import { Query, PaginatedResponse } from './types';

export const fetchQueries = async ({ page, pageSize }: { page: number, pageSize: number }): Promise<PaginatedResponse<Query>> => {
  const response = await fetch(`/scout/api/queries?page=${page}&per_page=${pageSize}`);
  if (!response.ok) {
    throw new Error('Network response was not ok');
  }
  const data = await response.json();
  return data.queries; 
};