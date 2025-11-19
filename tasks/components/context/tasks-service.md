// src/services/taskService.ts
import { db } from "../db";
import {
  tasks,
  labels,
  tasksLabels,
  tasksUsers,
  users,
  files,
  type Task,
  type Label,
  type NewTask,
  type NewLabel,
  type NewTaskLabel,
  type NewTaskUser,
} from "../db/schema";
import { and, eq, inArray, sql } from "drizzle-orm";
import * as fileService from "./fileService";

// ── Types ─────────────────────────────────────────────────────────────────────

export interface TaskWithRelations extends Task {
  users: Array<{
    id: string;
    fullName: string | null;
    email: string | null;
    avatarUrl: string | null;
  }>;
  labels: Array<{
    id: string;
    name: string;
    hexCode: string;
  }>;
  files: Array<{
    id: string;
    name: string;
    url: string;
    mimetype: string | null;
    size: number | null;
    uploadedBy: string;
    uploaderName: string | null;
    uploaderAvatarUrl: string | null;
    createdAt: Date;
  }>;
}

export interface TaskSummary {
  id: string;
  name: string;
  dueDate: Date | null;
  status: string | null;
  isOverdue: boolean;
  users: string[];
  labels: string[];
  fileCount: number;
  checkList: any;
}

export interface CreateTaskInput {
  name: string;
  description?: string;
  entityId: string;
  startDate?: Date | null;
  dueDate?: Date | null;
  dueDateReminder?: string | null;
  status?: string | null;
  checklist?: any;
  links?: any;
  users: string[];
  labels: string[];
  files?: Express.Multer.File[];
}

export interface UpdateTaskInput {
  name?: string;
  description?: string;
  startDate?: Date | null;
  dueDate?: Date | null;
  dueDateReminder?: string | null;
  status?: string | null;
  checklist?: any;
  links?: any;
  users?: string[];
  labels?: string[];
}

// ── Label Operations ─────────────────────────────────────────────────────────

export async function getAllLabels(companyId: string): Promise<Label[]> {
  return db
    .select()
    .from(labels)
    .where(eq(labels.companyId, companyId))
    .orderBy(labels.name);
}

export async function createLabel(
  companyId: string,
  data: { name: string; hexCode: string }
): Promise<Label> {
  const [inserted] = await db
    .insert(labels)
    .values({
      companyId,
      name: data.name,
      hexCode: data.hexCode,
    })
    .returning();
  return inserted;
}

export async function updateLabel(
  id: string,
  companyId: string,
  data: { name?: string; hexCode?: string }
): Promise<Label | null> {
  const [updated] = await db
    .update(labels)
    .set({
      ...data,
      updatedAt: new Date(),
    })
    .where(and(eq(labels.id, id), eq(labels.companyId, companyId)))
    .returning();
  return updated || null;
}

export async function deleteLabel(id: string, companyId: string): Promise<boolean> {
  const deleted = await db
    .delete(labels)
    .where(and(eq(labels.id, id), eq(labels.companyId, companyId)))
    .returning();
  return deleted.length > 0;
}

// ── Task Operations ──────────────────────────────────────────────────────────

export async function createTask(
  companyId: string,
  createdBy: string,
  data: CreateTaskInput
): Promise<TaskWithRelations> {
  // Create the task
  const [task] = await db
    .insert(tasks)
    .values({
      companyId,
      name: data.name,
      description: data.description,
      entityId: data.entityId,
      startDate: data.startDate,
      dueDate: data.dueDate,
      dueDateReminder: data.dueDateReminder,
      status: data.status ?? null,
      checklist: data.checklist,
      links: data.links,
      createdBy,
    })
    .returning();

  // Create task-user relationships
  if (data.users.length > 0) {
    await db.insert(tasksUsers).values(
      data.users.map((userId) => ({
        taskId: task.id,
        userId,
      }))
    );
  }

  // Create task-label relationships
  if (data.labels.length > 0) {
    await db.insert(tasksLabels).values(
      data.labels.map((labelId) => ({
        taskId: task.id,
        labelId,
      }))
    );
  }

  // Handle file uploads
  if (data.files && data.files.length > 0) {
    await Promise.all(
      data.files.map((file) =>
        fileService.uploadFile(
          "task",
          task.id,
          file,
          {
            name: file.originalname,
            description: null,
            metadata: {
              mimetype: file.mimetype,
              size: file.size,
            },
          },
          companyId,
          createdBy
        )
      )
    );
  }

  // Return the complete task with relations
  const taskWithRelations = await getTaskById(task.id, companyId);
  if (!taskWithRelations) {
    throw new Error("Failed to retrieve created task");
  }
  return taskWithRelations;
}

export async function getTaskById(id: string, companyId: string): Promise<TaskWithRelations | null> {
  // Get the task
  const [task] = await db
    .select()
    .from(tasks)
    .where(and(eq(tasks.id, id), eq(tasks.companyId, companyId)))
    .limit(1);

  if (!task) return null;

  // Get assigned user IDs only
  const taskUserIds = await db
    .select({
      userId: tasksUsers.userId,
    })
    .from(tasksUsers)
    .where(eq(tasksUsers.taskId, id));

  // Get label IDs only
  const taskLabelIds = await db
    .select({
      labelId: tasksLabels.labelId,
    })
    .from(tasksLabels)
    .where(eq(tasksLabels.taskId, id));

  // Get files
  const taskFiles = await fileService.getAllFiles("task", id, companyId);

  return {
    ...task,
    checklist: task.checklist as any,
    links: task.links as any,
    users: taskUserIds.map(u => u.userId) as any,
    labels: taskLabelIds.map(l => l.labelId) as any,
    files: taskFiles.map((file) => ({
      id: file.id,
      name: file.name,
      url: file.url,
      mimetype: (file.metadata as any)?.mimetype || null,
      size: (file.metadata as any)?.size || null,
      uploadedBy: file.uploadedBy,
      uploaderName: file.uploaderName,
      uploaderAvatarUrl: file.uploaderAvatarUrl,
      createdAt: file.createdAt,
    })),
  };
}

export async function getTasksByEntity(entityId: string, companyId: string): Promise<TaskSummary[]> {
  const taskRows = await db
    .select({
      id: tasks.id,
      name: tasks.name,
      dueDate: tasks.dueDate,
      status: tasks.status,
      checklist: tasks.checklist,
      isOverdue: sql<boolean>`
        CASE WHEN ${tasks.dueDate} IS NOT NULL
           AND ${tasks.dueDate} < date_trunc('day', now())
        THEN true ELSE false END
      `,
    })
    .from(tasks)
    .where(and(eq(tasks.entityId, entityId), eq(tasks.companyId, companyId)))
    .orderBy(tasks.dueDate, tasks.createdAt);

  // Get users, labels, and file counts for each task
  const taskIds = taskRows.map((t) => t.id);
  if (taskIds.length === 0) return [];

  // Get user IDs for all tasks
  const taskUsers = await db
    .select({
      taskId: tasksUsers.taskId,
      userId: tasksUsers.userId,
    })
    .from(tasksUsers)
    .where(inArray(tasksUsers.taskId, taskIds));

  // Get label IDs for all tasks
  const taskLabels = await db
    .select({
      taskId: tasksLabels.taskId,
      labelId: tasksLabels.labelId,
    })
    .from(tasksLabels)
    .where(inArray(tasksLabels.taskId, taskIds));

  // Get file counts for all tasks
  const fileCounts = await db
    .select({
      entityId: files.entityId,
      count: sql<number>`COUNT(*)`,
    })
    .from(files)
    .where(
      and(
        eq(files.entity, "task"),
        inArray(files.entityId, taskIds),
        eq(files.companyId, companyId)
      )
    )
    .groupBy(files.entityId);

  // Group user IDs by task
  const usersByTask = new Map<string, string[]>();
  taskUsers.forEach((tu) => {
    if (!usersByTask.has(tu.taskId)) {
      usersByTask.set(tu.taskId, []);
    }
    usersByTask.get(tu.taskId)!.push(tu.userId);
  });

  // Group label IDs by task
  const labelsByTask = new Map<string, string[]>();
  taskLabels.forEach((tl) => {
    if (!labelsByTask.has(tl.taskId)) {
      labelsByTask.set(tl.taskId, []);
    }
    labelsByTask.get(tl.taskId)!.push(tl.labelId);
  });

  // Group file counts by task
  const fileCountsByTask = new Map<string, number>();
  fileCounts.forEach((fc) => {
    fileCountsByTask.set(fc.entityId, Number(fc.count));
  });

  // Build result
  return taskRows.map((task) => ({
    id: task.id,
    name: task.name,
    dueDate: task.dueDate,
    status: task.status,
    isOverdue: task.isOverdue,
    users: usersByTask.get(task.id) || [],
    labels: labelsByTask.get(task.id) || [],
    fileCount: fileCountsByTask.get(task.id) || 0,
    checkList: task.checklist,
  }));
}

export async function getTasksByOwner(ownerId: string, companyId: string): Promise<TaskSummary[]> {
  const taskRows = await db
    .select({
      id: tasks.id,
      name: tasks.name,
      dueDate: tasks.dueDate,
      status: tasks.status,
      checklist: tasks.checklist,
      isOverdue: sql<boolean>`
        CASE WHEN ${tasks.dueDate} IS NOT NULL
           AND ${tasks.dueDate} < date_trunc('day', now())
        THEN true ELSE false END
      `,
    })
    .from(tasks)
    .where(and(eq(tasks.createdBy, ownerId), eq(tasks.companyId, companyId)))
    .orderBy(tasks.dueDate, tasks.createdAt);

  // Get users, labels, and file counts for each task
  const taskIds = taskRows.map((t) => t.id);
  if (taskIds.length === 0) return [];

  // Get user IDs for all tasks
  const taskUsers = await db
    .select({
      taskId: tasksUsers.taskId,
      userId: tasksUsers.userId,
    })
    .from(tasksUsers)
    .where(inArray(tasksUsers.taskId, taskIds));

  // Get label IDs for all tasks
  const taskLabels = await db
    .select({
      taskId: tasksLabels.taskId,
      labelId: tasksLabels.labelId,
    })
    .from(tasksLabels)
    .where(inArray(tasksLabels.taskId, taskIds));

  // Get file counts for all tasks
  const fileCounts = await db
    .select({
      entityId: files.entityId,
      count: sql<number>`COUNT(*)`,
    })
    .from(files)
    .where(
      and(
        eq(files.entity, "task"),
        inArray(files.entityId, taskIds),
        eq(files.companyId, companyId)
      )
    )
    .groupBy(files.entityId);

  // Group user IDs by task
  const usersByTask = new Map<string, string[]>();
  taskUsers.forEach((tu) => {
    if (!usersByTask.has(tu.taskId)) {
      usersByTask.set(tu.taskId, []);
    }
    usersByTask.get(tu.taskId)!.push(tu.userId);
  });

  // Group label IDs by task
  const labelsByTask = new Map<string, string[]>();
  taskLabels.forEach((tl) => {
    if (!labelsByTask.has(tl.taskId)) {
      labelsByTask.set(tl.taskId, []);
    }
    labelsByTask.get(tl.taskId)!.push(tl.labelId);
  });

  // Group file counts by task
  const fileCountsByTask = new Map<string, number>();
  fileCounts.forEach((fc) => {
    fileCountsByTask.set(fc.entityId, Number(fc.count));
  });

  // Build result
  return taskRows.map((task) => ({
    id: task.id,
    name: task.name,
    dueDate: task.dueDate,
    status: task.status,
    isOverdue: task.isOverdue,
    users: usersByTask.get(task.id) || [],
    labels: labelsByTask.get(task.id) || [],
    fileCount: fileCountsByTask.get(task.id) || 0,
    checkList: task.checklist,
  }));
}

export async function updateTask(
  id: string,
  companyId: string,
  data: UpdateTaskInput
): Promise<TaskWithRelations | null> {
  // Update the task
  const [updated] = await db
    .update(tasks)
    .set({
      name: data.name,
      description: data.description,
      startDate: data.startDate,
      dueDate: data.dueDate,
      dueDateReminder: data.dueDateReminder,
      status: data.status,
      checklist: data.checklist,
      links: data.links,
      updatedAt: new Date(),
    })
    .where(and(eq(tasks.id, id), eq(tasks.companyId, companyId)))
    .returning();

  if (!updated) return null;

  // Update user assignments if provided
  if (data.users !== undefined) {
    // Delete existing assignments
    await db.delete(tasksUsers).where(eq(tasksUsers.taskId, id));
    
    // Create new assignments
    if (data.users.length > 0) {
      await db.insert(tasksUsers).values(
        data.users.map((userId) => ({
          taskId: id,
          userId,
        }))
      );
    }
  }

  // Update label assignments if provided
  if (data.labels !== undefined) {
    // Delete existing assignments
    await db.delete(tasksLabels).where(eq(tasksLabels.taskId, id));
    
    // Create new assignments
    if (data.labels.length > 0) {
      await db.insert(tasksLabels).values(
        data.labels.map((labelId) => ({
          taskId: id,
          labelId,
        }))
      );
    }
  }

  return getTaskById(id, companyId);
}

export async function deleteTask(id: string, companyId: string): Promise<boolean> {
  // Delete the task (cascade will handle related records)
  const deleted = await db
    .delete(tasks)
    .where(and(eq(tasks.id, id), eq(tasks.companyId, companyId)))
    .returning();
  
  return deleted.length > 0;
}

// ── File Management ──────────────────────────────────────────────────────────

export async function addFilesToTask(
  taskId: string,
  companyId: string,
  uploadedBy: string,
  files: Express.Multer.File[]
): Promise<void> {
  // Verify task exists and belongs to company
  const [task] = await db
    .select()
    .from(tasks)
    .where(and(eq(tasks.id, taskId), eq(tasks.companyId, companyId)))
    .limit(1);

  if (!task) {
    throw new Error("Task not found");
  }

  // Upload all files
  await Promise.all(
    files.map((file) =>
      fileService.uploadFile(
        "task",
        taskId,
        file,
        {
          name: file.originalname,
          description: null,
          metadata: {
            mimetype: file.mimetype,
            size: file.size,
          },
        },
        companyId,
        uploadedBy
      )
    )
  );
}

export async function deleteFileFromTask(
  taskId: string,
  fileId: string,
  companyId: string
): Promise<boolean> {
  // Verify task exists and belongs to company
  const [task] = await db
    .select()
    .from(tasks)
    .where(and(eq(tasks.id, taskId), eq(tasks.companyId, companyId)))
    .limit(1);

  if (!task) {
    throw new Error("Task not found");
  }

  // Verify file belongs to this task
  const fileToDelete = await db
    .select()
    .from(files)
    .where(
      and(
        eq(files.id, fileId),
        eq(files.entity, "task"),
        eq(files.entityId, taskId),
        eq(files.companyId, companyId)
      )
    )
    .limit(1);

  if (fileToDelete.length === 0) {
    return false; // File not found or doesn't belong to this task
  }

  // Delete the file
  return await fileService.deleteFile(fileId, companyId);
}

// ── Utility Functions ────────────────────────────────────────────────────────

export async function getAllUsers(companyId: string) {
  return db
    .select({
      id: users.id,
      fullName: users.fullName,
      email: users.email,
      avatarUrl: users.avatarUrl,
    })
    .from(users)
    .where(eq(users.companyId, companyId))
    .orderBy(users.fullName, users.email);
}