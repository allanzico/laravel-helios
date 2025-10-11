import { DashboardStats } from './types';

export const fetchDashboardStats = async (): Promise<DashboardStats> => {
  const response = await fetch('/scout/api/dashboard-stats');
  if (!response.ok) throw new Error('Network response was not ok');
  return response.json();
};