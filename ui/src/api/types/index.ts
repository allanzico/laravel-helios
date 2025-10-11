
export interface LogFile {
  name: string;
  size: string;
  path: string;
}

export interface LogContent {
  file: string;
  content: string;
}

export interface Job {
  id: string;
  name: string;
  status: 'running' | 'processed' | 'failed';
  payload: string;
  exception: string | null;
  started_at: string;
  finished_at: string | null;
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
  failed_jobs_24h: number;
  errors_24h: number;
  avg_duration_24h: number;
  avg_memory_24h: number;
  latest_failed_tasks: ScheduledTask[];
  latest_slow_queries: Query[];
}

export interface ChartDataPoint {
  time: string;
  count: number;
}