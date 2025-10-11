import { useQuery } from '@tanstack/react-query';
import { fetchDashboardStats } from '@/api/dashboard.ts';

export const useDashboardStatsQuery = () => {
  return useQuery({
    queryKey: ['dashboardStats'],
    queryFn: fetchDashboardStats,
  });
};