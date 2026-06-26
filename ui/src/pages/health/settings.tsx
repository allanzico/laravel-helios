import { useState, useEffect } from 'react';
import { useAvailableHealthChecksQuery, useHealthCheckSettingsQuery, useUpdateHealthCheckSettingsMutation } from '@/queries/health';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { ArrowLeft, Save } from 'lucide-react';
import { Link } from '@tanstack/react-router';
import { useQueryClient } from '@tanstack/react-query';

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

export function HealthCheckSettings() {
  const queryClient = useQueryClient();
  const { data: availableChecks, isLoading: loadingAvailable } = useAvailableHealthChecksQuery();
  const { data: settings, isLoading: loadingSettings } = useHealthCheckSettingsQuery();
  const updateMutation = useUpdateHealthCheckSettingsMutation();

  const [enabledChecks, setEnabledChecks] = useState<string[]>([]);

  // Debug logging
  console.log('Available checks:', availableChecks);
  console.log('Settings:', settings);

  useEffect(() => {
    if (settings && settings.length > 0) {
      const enabled = settings
        .filter((s: HealthCheckSetting) => s.enabled)
        .map((s: HealthCheckSetting) => s.check_class);
      setEnabledChecks(enabled);
    } else if (Array.isArray(availableChecks) && availableChecks.length > 0 && Array.isArray(settings) && settings.length === 0) {
      // If no settings exist, enable all checks by default
      setEnabledChecks(availableChecks.map((c: AvailableCheck) => c.class));
    }
  }, [settings, availableChecks]);

  const handleToggle = (checkClass: string) => {
    setEnabledChecks((prev) =>
      prev.includes(checkClass)
        ? prev.filter((c) => c !== checkClass)
        : [...prev, checkClass]
    );
  };

  const handleSave = async () => {
    try {
      // Always send the array, even if empty
      await updateMutation.mutateAsync({ 
        enabled_checks: enabledChecks 
      });
      queryClient.invalidateQueries({ queryKey: ['healthChecks'] });
      queryClient.invalidateQueries({ queryKey: ['healthCheckSettings'] });
    } catch (error) {
      console.error('Failed to save settings:', error);
    }
  };

  if (loadingAvailable || loadingSettings) {
    return <p>Loading settings...</p>;
  }

  // Ensure availableChecks is an array
  const checksArray = Array.isArray(availableChecks) ? availableChecks : [];

  console.log('Checks array:', checksArray);

  // Group checks by category
  const groupedChecks = {
    'System & Server': checksArray.filter((c: AvailableCheck) => 
      ['ApplicationHealthCheck', 'HttpHealthCheck', 'SchedulerHealthCheck'].includes(c.name)
    ),
    'Infrastructure': checksArray.filter((c: AvailableCheck) => 
      ['DatabaseHealthCheck', 'RedisHealthCheck', 'CacheHealthCheck', 'StorageHealthCheck'].includes(c.name)
    ),
    'Resources': checksArray.filter((c: AvailableCheck) => 
      ['DiskSpaceHealthCheck', 'QueueHealthCheck'].includes(c.name)
    ),
    'Configuration': checksArray.filter((c: AvailableCheck) => 
      ['EnvironmentHealthCheck'].includes(c.name)
    ),
  };

  console.log('Grouped checks:', groupedChecks);

  return (
    <div className="space-y-6">
      <Card className="subtle-shadow">
        <CardHeader>
          <div className="flex items-center justify-between">
            <div>
              <CardTitle>Health Check Settings</CardTitle>
              <CardDescription>
                Choose which health checks to run on your system
              </CardDescription>
            </div>
            <Link to="/health">
              <Button variant="outline" size="sm">
                <ArrowLeft className="mr-2 h-4 w-4" />
                Back to Health
              </Button>
            </Link>
          </div>
        </CardHeader>
        <CardContent className="space-y-6">
          {Object.entries(groupedChecks).map(([category, checks]) => (
            checks.length > 0 && (
              <div key={category}>
                <h3 className="text-sm font-semibold mb-3 text-muted-foreground">{category}</h3>
                <div className="space-y-2">
                  {checks.map((check: AvailableCheck) => (
                    <div
                      key={check.class}
                      className="flex items-center space-x-3 p-4 rounded-lg border hover:bg-muted/50 transition-colors"
                    >
                      <Checkbox
                        id={check.class}
                        checked={enabledChecks.includes(check.class)}
                        onCheckedChange={() => handleToggle(check.class)}
                      />
                      <label
                        htmlFor={check.class}
                        className="flex-1 cursor-pointer"
                      >
                        <div className="font-medium">{check.label}</div>
                        <div className="text-xs text-muted-foreground font-mono mt-0.5">
                          {check.class.split('\\').pop()}
                        </div>
                      </label>
                    </div>
                  ))}
                </div>
              </div>
            )
          ))}

          <div className="pt-4 flex justify-between items-center border-t">
            <div className="text-sm text-muted-foreground">
              {enabledChecks.length} of {checksArray.length} checks enabled
            </div>
            <Button
              onClick={handleSave}
              disabled={updateMutation.isPending}
            >
              <Save className="mr-2 h-4 w-4" />
              {updateMutation.isPending ? 'Saving...' : 'Save Settings'}
            </Button>
          </div>

          {updateMutation.isSuccess && (
            <p className="text-sm text-success">
              Settings saved successfully!
            </p>
          )}

          {updateMutation.isError && (
            <p className="text-sm text-destructive">
              Failed to save settings. Please try again.
            </p>
          )}
        </CardContent>
      </Card>
    </div>
  );
}