import { LogFile, LogContent } from './types/index'; 
import { csrfToken, heliosApi } from './client';

export const fetchLogs = async (): Promise<LogFile[]> => {
  const response = await fetch(heliosApi('logs'));

  if (!response.ok) {
    throw new Error('Network response was not ok');
  }

  const data = await response.json();
  return data.logs;
};

export const fetchLogContent = async (fileName: string): Promise<LogContent> => {
  const response = await fetch(heliosApi(`logs/${fileName}`));
  
  if (!response.ok) {
    throw new Error('Network response was not ok');
  }

  return response.json();
};


export const clearLogFile = async (fileName: string): Promise<any> => {
  const response = await fetch(heliosApi(`logs/${fileName}`), {
    method: 'DELETE',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': csrfToken(),
    },
  });

  if (!response.ok) {
    throw new Error('Failed to clear log file');
  }

  return response.json();
};
