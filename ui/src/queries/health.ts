import { useQuery, useMutation, UseQueryOptions } from '@tanstack/react-query';
import { fetchHealthChecks, fetchAvailableHealthChecks, fetchHealthCheckSettings, updateHealthCheckSettings } from '@/api/health';

interface HealthCheck {
  check: string;
  label: string;
  status: 'ok' | 'warning' | 'failed' | 'crashed';
  message: string;
  short_summary: string | null;
  meta: Record<string, any>;
}

interface HealthCheckResponse {
  checks: HealthCheck[];
  overall_status: 'ok' | 'warning' | 'failed';
}

interface AvailableCheck {
  class: string;
  name: string;
  label: string;
}

interface HealthCheckSetting {
  id: number;
  check_class: string;
  enabled: boolean;
  config: any | null;
}

export const useHealthChecksQuery = (options?: Omit<UseQueryOptions<HealthCheckResponse, Error>, 'queryKey' | 'queryFn'>) => {
  return useQuery<HealthCheckResponse, Error>({
    queryKey: ['healthChecks'],
    queryFn: fetchHealthChecks,
    ...options,
  });
};

export const useAvailableHealthChecksQuery = () => {
  return useQuery<AvailableCheck[], Error>({
    queryKey: ['availableHealthChecks'],
    queryFn: fetchAvailableHealthChecks,
  });
};

export const useHealthCheckSettingsQuery = () => {
  return useQuery<HealthCheckSetting[], Error>({
    queryKey: ['healthCheckSettings'],
    queryFn: fetchHealthCheckSettings,
  });
};

export const useUpdateHealthCheckSettingsMutation = () => {
  return useMutation({
    mutationFn: updateHealthCheckSettings,
  });
};