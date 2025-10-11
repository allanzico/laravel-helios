import { DashboardStats } from './types';

export const fetchDashboardStats = async (): Promise<DashboardStats> => {
  const response = await fetch('/helios/api/dashboard-stats');
  if (!response.ok) throw new Error('Network response was not ok');
  return response.json();
};