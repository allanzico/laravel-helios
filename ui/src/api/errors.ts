interface ErrorFilters {
  status?: string;
  level?: string;
  type?: string;
  search?: string;
  page?: number;
}

export const fetchErrors = async (filters: ErrorFilters = {}) => {
  const params = new URLSearchParams();
  
  if (filters.status) params.append('status', filters.status);
  if (filters.level) params.append('level', filters.level);
  if (filters.type) params.append('type', filters.type);
  if (filters.search) params.append('search', filters.search);
  if (filters.page) params.append('page', filters.page.toString());

  const response = await fetch(`/scout/api/errors?${params.toString()}`);
  if (!response.ok) throw new Error('Failed to fetch errors');
  return response.json();
};

export const fetchErrorStats = async () => {
  const response = await fetch('/scout/api/errors/stats');
  if (!response.ok) throw new Error('Failed to fetch error stats');
  return response.json();
};

export const fetchError = async (id: string) => {
  const response = await fetch(`/scout/api/errors/${id}`);
  if (!response.ok) throw new Error('Failed to fetch error');
  return response.json();
};

export const resolveError = async (id: string) => {
  const csrfToken = (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content;
  
  const response = await fetch(`/scout/api/errors/${id}/resolve`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': csrfToken,
    },
  });
  
  if (!response.ok) throw new Error('Failed to resolve error');
  return response.json();
};

export const ignoreError = async (id: string) => {
  const csrfToken = (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content;
  
  const response = await fetch(`/scout/api/errors/${id}/ignore`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': csrfToken,
    },
  });
  
  if (!response.ok) throw new Error('Failed to ignore error');
  return response.json();
};

export const unresolveError = async (id: string) => {
  const csrfToken = (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content;
  
  const response = await fetch(`/scout/api/errors/${id}/unresolve`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': csrfToken,
    },
  });
  
  if (!response.ok) throw new Error('Failed to unresolve error');
  return response.json();
};

export const deleteError = async (id: string) => {
  const csrfToken = (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content;
  
  const response = await fetch(`/scout/api/errors/${id}`, {
    method: 'DELETE',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': csrfToken,
    },
  });
  
  if (!response.ok) throw new Error('Failed to delete error');
  return response.json();
};