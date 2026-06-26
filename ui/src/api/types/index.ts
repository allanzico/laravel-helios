
export interface LogFile {
  name: string;
  size: string;
  path: string;
}

export interface LogContent {
  file: string;
  content: string;
  can_clear?: boolean;
}

export interface Job {
  id: string;
  name: string;
  status: 'running' | 'processed' | 'failed' | 'retried';
  payload: unknown;
  exception: string | null;
  started_at: string;
  finished_at: string | null;
  can_retry?: boolean;
  can_forget?: boolean;
}

export interface QueueSummary {
  pending_jobs: number;
  failed_jobs: number;
  queue_actions?: QueueActionCapabilities;
}

export interface QueueActionCapabilities {
  failed_driver: string | null;
  failed_provider: string | null;
  uses_helios_job_ids: boolean;
  retry_supported: boolean;
  forget_supported: boolean;
}

export interface JobsResponse {
  jobs: PaginatedResponse<Job>;
  summary: QueueSummary;
}

export interface LatestRun {
  id: string;
  status: 'starting' | 'finished' | 'failed';
  started_at: string | null;
  finished_at: string | null;
  runtime_ms: number | null;
  output: string | null;
  exit_code: number | null;
  triggered_by: 'manual' | 'scheduler' | null;
}

export interface ScheduledTask {
  command: string;
  signature: string;
  expression: string;
  description: string | null;
  next_run_at: string;
  can_run?: boolean;
  latest_run: LatestRun | null;
}

export interface Query {
  id: string;
  connection_name: string;
  sql: string;
  bindings: (string | number | boolean | null)[];
  time_ms: number;
  created_at: string;
}

export interface RequestType {
  id: string;
  method: string;
  uri: string;
  status_code: number;
  duration_ms: number;
  memory_mb: number;
  created_at: string;
}

export interface PaginatedResponse<T> {
  current_page: number;
  data: T[];
  first_page_url: string;
  from: number;
  last_page: number;
  last_page_url: string;
  links: { url: string | null; label: string; active: boolean }[];
  next_page_url: string | null;
  path: string;
  per_page: number;
  prev_page_url: string | null;
  to: number;
  total: number;
}

export interface DashboardStats {
  health?: DashboardHealthSummary;
  failed_jobs_24h: number;
  errors_24h: number;
  http_errors_24h: number;
  avg_duration_24h: number;
  avg_memory_24h: number;
  latest_failed_tasks: ScheduledTask[];
  latest_slow_queries: Query[];
  recent_actions: RecentAction[];
}

export interface DashboardHealthSummary {
  overall_status: 'ok' | 'warning' | 'failed';
  total_checks: number;
  problem_count: number;
  problems: DashboardHealthProblem[];
}

export interface DashboardHealthProblem {
  check: string;
  status: string;
  message: string;
  short_summary: string | null;
}

export interface RecentAction {
  id: string;
  action: string;
  target_type: string | null;
  target_id: string | null;
  status: string;
  created_at: string;
}

export interface ChartDataPoint {
  time: string;
  count: number;
}
