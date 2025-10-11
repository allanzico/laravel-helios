import { ChartDataPoint } from './types';

export const fetchRequestsPerMinute = async (): Promise<ChartDataPoint[]> => {
  const response = await fetch('/scout/api/requests-per-minute');
  if (!response.ok) throw new Error('Network response was not ok');
  return response.json();
};