import { ScheduledTask } from './types';
import { heliosApi } from './client';

export const fetchDefinedTasks = async (): Promise<ScheduledTask[]> => {
  const response = await fetch(heliosApi('scheduled-tasks'));
  if (!response.ok) {
    throw new Error('Network response was not ok');
  }
  const data = await response.json();
  return data.tasks;
};
