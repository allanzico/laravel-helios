type HeliosRuntimeConfig = {
  basePath: string;
  apiPath: string;
  actions?: {
    purgeData?: boolean;
  };
};

declare global {
  interface Window {
    Helios?: HeliosRuntimeConfig;
  }
}

export const heliosBasePath = () => window.Helios?.basePath ?? '/helios';

export const heliosApi = (path = '') => {
  const apiPath = window.Helios?.apiPath ?? '/helios/api';
  const normalizedPath = path.replace(/^\/+/, '');

  return normalizedPath ? `${apiPath}/${normalizedPath}` : apiPath;
};

export const csrfToken = () => (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content ?? '';

export const heliosActionAllowed = (action: keyof NonNullable<HeliosRuntimeConfig['actions']>) => {
  return window.Helios?.actions?.[action] ?? false;
};

export {};
