export const fetchHealthChecks = async () => {
  const response = await fetch('/scout/api/health-checks');
  if (!response.ok) throw new Error('Failed to fetch health checks');
  return response.json();
};

export const fetchAvailableHealthChecks = async () => {
  const response = await fetch('/scout/api/health-checks/available');
  if (!response.ok) throw new Error('Failed to fetch available checks');
  const data = await response.json();
  return data.checks;
};

export const fetchHealthCheckSettings = async () => {
  const response = await fetch('/scout/api/health-checks/settings');
  if (!response.ok) throw new Error('Failed to fetch settings');
  const data = await response.json();
  return data.settings;
};

export const updateHealthCheckSettings = async (enabledChecks: { enabled_checks: string[] }) => {
  const csrfToken = (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content;

  const response = await fetch('/scout/api/health-checks/settings', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': csrfToken,
    },
    body: JSON.stringify(enabledChecks),
  });

  if (!response.ok) throw new Error('Failed to update settings');
  return response.json();
};