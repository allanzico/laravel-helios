import { JobsResponse } from './types';
import { csrfToken, heliosApi } from './client';

export const fetchJobs = async ({ page, pageSize }: { page: number, pageSize: number }): Promise<JobsResponse> => {
  const response = await fetch(heliosApi(`jobs?page=${page}&per_page=${pageSize}`));

  if (!response.ok) {
    throw new Error('Network response was not ok');
  }
  
  return response.json(); 
};

export const retryJob = async (id: string): Promise<void> => {
  const response = await fetch(heliosApi(`jobs/${id}/retry`), {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': csrfToken(),
    },
  });

  if (!response.ok) {
    throw new Error('Failed to retry job');
  }
};

export const forgetJob = async (id: string): Promise<void> => {
  const response = await fetch(heliosApi(`jobs/${id}`), {
    method: 'DELETE',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': csrfToken(),
    },
  });

  if (!response.ok) {
    throw new Error('Failed to forget job');
  }
};
