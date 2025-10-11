export const purgeTable = async (table: string): Promise<void> => {
  const csrfToken = (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content;

  const response = await fetch('/scout/api/purge', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': csrfToken,
    },
    body: JSON.stringify({ table }),
  });

  if (!response.ok) {
    throw new Error('Failed to purge records');
  }
};