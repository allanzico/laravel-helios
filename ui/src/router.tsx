import {
    createRootRoute,
    createRoute,
    Router,
} from '@tanstack/react-router';
import App from './app';
import { LogIndex } from './pages/logs';
import { LogShow } from './pages/logs/show';
import { JobIndex } from './pages/jobs';
import { ScheduledTaskIndex } from './pages/tasks';
import { QueryIndex } from './pages/queries';
import { RequestIndex } from './pages/requests';
import { DashboardIndex } from './pages/dashboard';
import { HealthChecksIndex } from './pages/health';
import { HealthCheckSettings } from './pages/health/settings';
import { ErrorsIndex } from './pages/errors';
import { ErrorDetail } from './pages/errors/show';
import { heliosBasePath } from './api/client';

const rootRoute = createRootRoute({
    component: App,
});

// Connect the real page components to their respective routes
const indexRoute = createRoute({ getParentRoute: () => rootRoute, path: '/', component: DashboardIndex });
const logsRoute = createRoute({ getParentRoute: () => rootRoute, path: 'logs', component: LogIndex });
const logShowRoute = createRoute({ getParentRoute: () => rootRoute, path: 'logs/$fileName', component: LogShow });
const jobsRoute = createRoute({ getParentRoute: () => rootRoute, path: 'jobs', component: JobIndex });
const tasksRoute = createRoute({ getParentRoute: () => rootRoute, path: 'tasks', component: ScheduledTaskIndex });
const queriesRoute = createRoute({ getParentRoute: () => rootRoute, path: 'queries', component: QueryIndex });
const requestsRoute = createRoute({ getParentRoute: () => rootRoute, path: 'requests', component: RequestIndex });
const errorsRoute = createRoute({
  getParentRoute: () => rootRoute,
  path: 'errors',
  component: ErrorsIndex,
});

const errorDetailRoute = createRoute({
  getParentRoute: () => rootRoute,
  path: 'errors/$id',
  component: ErrorDetail,
});

const healthRoute = createRoute({
    getParentRoute: () => rootRoute,
    path: 'health',
    component: HealthChecksIndex
});

const healthSettingsRoute = createRoute({
    getParentRoute: () => rootRoute,
    path: 'health/settings',
    component: HealthCheckSettings
});


// Assemble the final route tree
const routeTree = rootRoute.addChildren([
    indexRoute,
    logsRoute,
    logShowRoute,
    jobsRoute,
    tasksRoute,
    queriesRoute,
    requestsRoute,
    healthRoute,
    healthSettingsRoute,
    errorsRoute,
    errorDetailRoute,
]);

// Create and export the router instance
export const router = new Router({
    routeTree,
    basepath: heliosBasePath(),
});

// Register the router's types for auto-completion and type-safety
declare module '@tanstack/react-router' {
    interface Register {
        router: typeof router;
    }
}
