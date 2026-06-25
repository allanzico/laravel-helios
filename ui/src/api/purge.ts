import { csrfToken, heliosApi } from './client';

export const purgeTable = async (table: string): Promise<void> => {
  const response = await fetch(heliosApi('purge'), {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': csrfToken(),
    },
    body: JSON.stringify({ table }),
  });

  if (!response.ok) {
    throw new Error('Failed to purge records');
  }
};
