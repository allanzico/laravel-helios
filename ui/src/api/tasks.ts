import { ScheduledTask } from './types';

export const fetchDefinedTasks = async (): Promise<ScheduledTask[]> => {
  const response = await fetch('/helios/api/scheduled-tasks');
  if (!response.ok) {
    throw new Error('Network response was not ok');
  }
  const data = await response.json();
  return data.tasks;
};