import { Job, PaginatedResponse } from './types';

export const fetchJobs = async ({ page, pageSize }: { page: number, pageSize: number }): Promise<PaginatedResponse<Job>> => {
  const response = await fetch(`/scout/api/jobs?page=${page}&per_page=${pageSize}`);

  if (!response.ok) {
    throw new Error('Network response was not ok');
  }
  
  const responseData = await response.json();
  return responseData.jobs; 
};