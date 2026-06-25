import { ChartDataPoint } from './types';
import { heliosApi } from './client';

export const fetchRequestsPerMinute = async (): Promise<ChartDataPoint[]> => {
  const response = await fetch(heliosApi('requests-per-minute'));
  if (!response.ok) throw new Error('Network response was not ok');
  return response.json();
};
