import { LogFile, LogContent } from './types/index'; 

export const fetchLogs = async (): Promise<LogFile[]> => {
  const response = await fetch('/helios/api/logs');

  if (!response.ok) {
    throw new Error('Network response was not ok');
  }

  const data = await response.json();
  return data.logs;
};

export const fetchLogContent = async (fileName: string): Promise<LogContent> => {
  const response = await fetch(`/helios/api/logs/${fileName}`);
  
  if (!response.ok) {
    throw new Error('Network response was not ok');
  }

  return response.json();
};


export const clearLogFile = async (fileName: string): Promise<any> => {
  const csrfToken = (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content;

  const response = await fetch(`/helios/api/logs/${fileName}`, {
    method: 'DELETE',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': csrfToken,
    },
  });

  if (!response.ok) {
    throw new Error('Failed to clear log file');
  }

  return response.json();
};