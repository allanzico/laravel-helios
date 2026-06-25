import { DashboardStats } from './types';
import { heliosApi } from './client';

export const fetchDashboardStats = async (): Promise<DashboardStats> => {
  const response = await fetch(heliosApi('dashboard-stats'));
  if (!response.ok) throw new Error('Network response was not ok');
  return response.json();
};
