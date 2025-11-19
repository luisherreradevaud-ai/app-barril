import { fetchWithAuth } from "./common";

export type DueDateReminder =
  | 'atTimeDueDate'
  | '1 hour before'
  | '2 hours before'
  | '1 day before'
  | '2 days before';

export type TaskChecklistItem = {
  id: string;
  text: string;
  completed: boolean;
};

export type TaskChecklist = {
  id: string;
  title: string;
  items: TaskChecklistItem[];
}[];

export type TaskLink = {
  id: string;
  url: string;
  title?: string;
}[];

export type TaskFile = {
  id: string;
  name: string;
  url: string;
  size?: number;
  type?: string;
};

export type TaskUser = {
  userId: string;
  userName?: string;
};

export type TaskLabel = {
  id: string;
  name: string;
  hexCode: string;
};

export type Task = {
  id?: string;
  name: string;
  description?: string;
  entityId: string;
  startDate?: string | null;
  dueDate?: string | null;
  dueDateReminder?: DueDateReminder | null;
  checklist?: TaskChecklist;
  links?: TaskLink;
  users?: string[];
  labels?: string[];
  files?: TaskFile[];
  status?: string;
};

export type TaskResponse = {
  users: any[];
  labels: TaskLabel[];
  task: Task;
};

export type TaskListItem = {
  id: string;
  name: string;
  entityId: string;
  dueDate?: string | null;
  isOverdue: boolean;
  users: string[] | TaskUser[];
  fileCount: number;
  checkList?: TaskChecklist;
  status?: string;
};

export type TaskListUser = {
  id: string;
  fullName: string;
  email: string;
  avatarUrl?: string | null;
};

export type TaskListLabel = {
  id: string;
  name: string;
};

export type CreateTaskPayload = {
  name: string;
  description?: string;
  entityId: string;
  startDate?: string | null;
  dueDate?: string | null;
  dueDateReminder?: DueDateReminder | null;
  checklist?: TaskChecklist;
  links?: TaskLink;
  users: string[];
  labels: string[];
};

export type UpdateTaskPayload = {
  name?: string;
  description?: string;
  startDate?: string | null;
  dueDate?: string | null;
  dueDateReminder?: DueDateReminder | null;
  checklist?: TaskChecklist;
  links?: TaskLink;
  users?: string[];
  labels?: string[];
  status?: string;
};

// Validation functions
const isValidUUID = (id: string): boolean => {
  const uuidRegex = /^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i;
  return uuidRegex.test(id);
};

const isValidHexColor = (hex: string): boolean => {
  const hexRegex = /^#[0-9A-F]{6}$/i;
  return hexRegex.test(hex);
};

const isValidDueDateReminder = (reminder: string): reminder is DueDateReminder => {
  const validReminders: DueDateReminder[] = [
    'atTimeDueDate',
    '1 hour before',
    '2 hours before',
    '1 day before',
    '2 days before'
  ];
  return validReminders.includes(reminder as DueDateReminder);
};

const sanitizeString = (str: string, maxLength: number = 255): string => {
  return str.trim().slice(0, maxLength);
};

const validateTaskPayload = (data: CreateTaskPayload): { valid: boolean; errors: string[] } => {

  console.log('data',data)

  const errors: string[] = [];

  return {
    valid: errors.length === 0,
    errors
  };

  // Required fields
  if (!data.name || data.name.trim().length === 0) {
    errors.push('Task name is required');
  } else if (data.name.length > 255) {
    errors.push('Task name must not exceed 255 characters');
  }

  if (!data.entityId || !isValidUUID(data.entityId)) {
    errors.push('Valid entityId (UUID) is required');
  }

  // Date validation
  if (data.startDate && isNaN(Date.parse(data.startDate))) {
    errors.push('Invalid startDate format');
  }

  if (data.dueDate && isNaN(Date.parse(data.dueDate))) {
    errors.push('Invalid dueDate format');
  }

  if (data.startDate && data.dueDate && new Date(data.startDate) > new Date(data.dueDate)) {
    errors.push('Start date cannot be after due date');
  }

  // Reminder validation
  if (data.dueDateReminder && !isValidDueDateReminder(data.dueDateReminder)) {
    errors.push('Invalid dueDateReminder value');
  }

  // Users validation
  if (data.users && Array.isArray(data.users)) {
    data.users.forEach((userId, index) => {
      if (!isValidUUID(userId)) {
        errors.push(`Invalid UUID for user at index ${index}`);
      }
    });
  }

  if (data.labels && Array.isArray(data.labels)) {
    data.labels.forEach((labelId, index) => {
      if (!isValidUUID(labelId)) {
        errors.push(`Invalid UUID for label at index ${index}`);
      }
    });
  }

  return {
    valid: errors.length === 0,
    errors
  };
};

// API functions
export async function fetchTasksByEntity(entityId: string): Promise<{tasks: TaskListItem[], users: TaskListUser[], labels: TaskListLabel[]} | null> {
  if (!isValidUUID(entityId)) {
    console.error('Invalid entityId format');
    return null;
  }

  const apiUrl = `${process.env.NEXT_PUBLIC_API_BASE_URL}/tasks/by-entity/${entityId}`;
  try {
    const response = await fetchWithAuth(apiUrl, { method: "GET" });
    if (!response) {
      throw new Error(`Error fetching tasks for entity.`);
    }
    return response;
  } catch (error) {
    console.error('Error fetching tasks by entity:', error);
    return null;
  }
}

export async function fetchTasksAssignedToMe(): Promise<{tasks: TaskListItem[], users: TaskListUser[], labels: TaskListLabel[]} | null> {
  const apiUrl = `${process.env.NEXT_PUBLIC_API_BASE_URL}/tasks/assigned-to-me`;
  try {
    const response = await fetchWithAuth(apiUrl, { method: "GET" });
    if (!response) {
      throw new Error(`Error fetching tasks assigned to me.`);
    }
    return response;
  } catch (error) {
    console.error('Error fetching tasks assigned to me:', error);
    return null;
  }
}

export async function fetchTaskById(id: string): Promise<TaskResponse | null> {
  if (!isValidUUID(id)) {
    console.error('Invalid task ID format');
    return null;
  }

  const apiUrl = `${process.env.NEXT_PUBLIC_API_BASE_URL}/tasks/${id}`;
  try {
    const response = await fetchWithAuth(apiUrl, { method: "GET" });
    if (!response) {
      throw new Error(`Error fetching task ${id}.`);
    }
    return response;
  } catch (error) {
    console.error(`Failed to fetch task ${id}:`, error);
    return null;
  }
}

export async function createTask(
  data: CreateTaskPayload,
  files?: File[]
): Promise<Task | null> {
  // Validate payload
  const validation = validateTaskPayload(data);
  if (!validation.valid) {
    console.error(`Validation errors: ${validation.errors.join(', ')}`);
    return null;
  }

  // Sanitize string inputs
  const sanitizedData: CreateTaskPayload = {
    ...data,
    name: sanitizeString(data.name, 255),
    description: data.description ? sanitizeString(data.description, 5000) : undefined,
    users: data.users || [],
    labels: data.labels || [],
  };

  const apiUrl = `${process.env.NEXT_PUBLIC_API_BASE_URL}/tasks`;

  try {
    let response: Task;

    if (files && files.length > 0) {
      // Handle file upload with FormData
      const formData = new FormData();

      // Append each field separately for multipart/form-data
      formData.append('name', sanitizedData.name);
      if (sanitizedData.description) formData.append('description', sanitizedData.description);
      formData.append('entityId', sanitizedData.entityId);
      if (sanitizedData.startDate) formData.append('startDate', sanitizedData.startDate);
      if (sanitizedData.dueDate) formData.append('dueDate', sanitizedData.dueDate);
      if (sanitizedData.dueDateReminder) formData.append('dueDateReminder', sanitizedData.dueDateReminder);
      if (sanitizedData.checklist) formData.append('checklist', JSON.stringify(sanitizedData.checklist));
      if (sanitizedData.links) formData.append('links', JSON.stringify(sanitizedData.links));

      // Append users and labels as JSON arrays
      sanitizedData.users.forEach(userId => formData.append('users', userId));
      sanitizedData.labels.forEach(labelId => formData.append('labels', labelId));

      // Append files
      files.forEach((file) => {
        formData.append('files', file);
      });

      response = await fetchWithAuth(apiUrl, {
        method: 'POST',
        body: formData,
      });
    } else {
      // Standard JSON request
      response = await fetchWithAuth(apiUrl, {
        method: 'POST',
        body: JSON.stringify(sanitizedData),
      });
    }

    if (!response) {
      throw new Error(`Error creating task.`);
    }
    return response;
  } catch (error) {
    console.error('Failed to create task:', error);
    return null;
  }
}

export async function updateTask(
  taskId: string,
  data: UpdateTaskPayload
): Promise<Task | null> {
  if (!isValidUUID(taskId)) {
    console.error('Invalid task ID format');
    return null;
  }

  // Sanitize string inputs
  const sanitizedData: UpdateTaskPayload = {
    ...data,
    name: data.name ? sanitizeString(data.name, 255) : undefined,
    description: data.description ? sanitizeString(data.description, 5000) : undefined,
  };

  const apiUrl = `${process.env.NEXT_PUBLIC_API_BASE_URL}/tasks/${taskId}`;

  console.log('sanitizedData',sanitizedData)

  try {
    const response = await fetchWithAuth(apiUrl, {
      method: 'PUT',
      body: JSON.stringify(sanitizedData),
    });

    if (!response) {
      throw new Error(`Error updating task.`);
    }
    return response;
  } catch (error) {
    console.error('Failed to update task:', error);
    return null;
  }
}

export async function deleteTask(
  id: string
): Promise<boolean | null> {

  if (!isValidUUID(id)) {
    console.error('Invalid task ID format');
    return null;
  }

  const apiUrl = `${process.env.NEXT_PUBLIC_API_BASE_URL}/tasks/${id}`;
  try {
    const response = await fetchWithAuth(apiUrl, { method: "DELETE" });
    if (!response) {
      return true;
    }
    return response;
  } catch (error) {
    console.error(`Failed to delete task ${id}:`, error);
    return null;
  }
}
