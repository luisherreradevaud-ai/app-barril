import { db } from "../db";
import {
  users,
  type User,
  internalConversations,
  internalConversationComments,
  internalConversationFiles,
  NewInternalConversation,
  NewInternalConversationComment,
  NewInternalConversationFile,
} from "../db/schema";
import { listTagsFor, addTagsToEntity, deleteTagsForEntityByUserIds } from "./tagService";
import { and, eq, inArray } from "drizzle-orm";
import * as s3Service from "./S3Service";

/**
 * Simple helper to prevent cross-company access.
 */
function assertSameCompany(userCompanyId?: string | null, targetCompanyId?: string | null) {
  if (!userCompanyId || !targetCompanyId || userCompanyId !== targetCompanyId) {
    const e = new Error("Forbidden: cross-company access.");
    (e as any).statusCode = 403;
    throw e;
  }
}

/**
 * Some projects expose different S3 helpers. This wrapper tries two common ones.
 * Adjust the internals to match your S3Service implementation if needed.
 */

async function uploadToS3ReturnPublicUrl(
  file: Express.Multer.File,
  keyPrefix: string // kept for signature compatibility; not used in this quick path
): Promise<string> {
  // 1) Your existing API
  if ((s3Service as any).uploadFile) {
    const { url, key } = await (s3Service as any).uploadFile(file);
    // Ensure we always return a public URL:
    return url || s3Service.getPublicUrl(key);
  }

  // 2) Back-compat (if you later add any of these)
  if ((s3Service as any).uploadBufferAndGetPublicUrl) {
    const key = `${keyPrefix}/${Date.now()}_${file.originalname}`;
    return await (s3Service as any).uploadBufferAndGetPublicUrl(file.buffer, key, file.mimetype);
  }
  if ((s3Service as any).uploadFileFromMulter) {
    return await (s3Service as any).uploadFileFromMulter(file, keyPrefix);
  }

  throw new Error(
    "S3Service uploader not found. Expected uploadFile or uploadBufferAndGetPublicUrl or uploadFileFromMulter."
  );
}


// ──────────────────────────────────────────────────────────────────────────────
// Create a conversation
export async function createConversation(params: {
  user: User;
  viewName: string;
  entityId: string;
  status?: string | null;
  ownerId?: string | null;
}): Promise<Pick<NewInternalConversation, "id">> {
  if (!params.user?.companyId) throw new Error("Missing user company.");

  const [row] = await db
    .insert(internalConversations)
    .values({
      companyId: params.user.companyId,
      viewName: params.viewName,
      entityId: params.entityId,
      status: params.status ?? "open",
      ownerId: params.ownerId ?? params.user.id,
      createdBy: params.user.id,
      updatedBy: params.user.id,
    })
    .returning({ id: internalConversations.id });

  return row;
}

// ──────────────────────────────────────────────────────────────────────────────
// Get conversation with nested comments and files (by conversation id)
export async function getConversationById(params: {
  user: User;
  conversationId: string;
}) {
  const [conv] = await db
    .select()
    .from(internalConversations)
    .where(eq(internalConversations.id, params.conversationId))
    .limit(1);

  if (!conv) throw new Error("Conversation not found.");
  assertSameCompany(params.user.companyId, conv.companyId);

  const comments = await db
    .select({
      id: internalConversationComments.id,
      companyId: internalConversationComments.companyId,
      internalConversationId: internalConversationComments.internalConversationId,
      content: internalConversationComments.content,
      authorId: internalConversationComments.authorId,
      status: internalConversationComments.status,
      createdAt: internalConversationComments.createdAt,
      updatedAt: internalConversationComments.updatedAt,
      authorName: users.fullName,
      authorAvatarUrl: users.avatarUrl,
    })
    .from(internalConversationComments)
    .leftJoin(users, eq(internalConversationComments.authorId, users.id))
    .where(eq(internalConversationComments.internalConversationId, conv.id))
    .orderBy(internalConversationComments.createdAt);

  const commentIds = comments.map((c) => c.id);
  let files: any[] = [];
  if (commentIds.length) {
    files = await db
      .select()
      .from(internalConversationFiles)
      .where(eq(internalConversationFiles.companyId, conv.companyId!));
    // We’ll filter by the comment list to avoid leaking cross-company by mistake
    files = files.filter((f) => commentIds.includes(f.internalConversationCommentId!));
  }

  // Fetch likes user profiles in bulk
  let likesByComment: Record<string, Array<{userId:string; userName:string|null; userAvatarUrl:string|null}>> = {};
  if (commentIds.length) {
    // Get all likes arrays
    const raw = await db
      .select({ id: internalConversationComments.id, likes: internalConversationComments.likes })
      .from(internalConversationComments)
      .where(inArray(internalConversationComments.id, commentIds));

    // Flatten userIds and fetch profiles once
    const allUserIds = Array.from(new Set(
      raw.flatMap(r => (Array.isArray(r.likes) ? r.likes : [])).filter(Boolean)
    ));
    const userMap = new Map<string, {userName:string|null; userAvatarUrl:string|null}>();
    if (allUserIds.length) {
      const usersRows = await db
        .select({ id: users.id, fullName: users.fullName, avatarUrl: users.avatarUrl })
        .from(users)
        .where(inArray(users.id, allUserIds));
      usersRows.forEach(u => userMap.set(u.id, { userName: u.fullName ?? null, userAvatarUrl: u.avatarUrl ?? null }));
    }

    raw.forEach(r => {
      const ids: string[] = Array.isArray(r.likes) ? r.likes : [];
      likesByComment[r.id] = ids.map(uid => ({ userId: uid, ...(userMap.get(uid) ?? { userName: null, userAvatarUrl: null }) }));
    });
  }

  // Fetch tags for all comments
  const tagsByComment: Record<string, Array<{id:string; userId:string; userName:string|null; userAvatarUrl:string|null}>> = {};
  for (const c of commentIds) {
    const t = await listTagsFor({ user: params.user, entity: "internalConversationComment", entityId: c });
    tagsByComment[c] = t.map(x => ({ id: x.id, userId: x.userId, userName: x.userName ?? null, userAvatarUrl: x.userAvatarUrl ?? null }));
  }

  // Rebuild comments with likes/tags
  const commentsWithFiles = comments.map((c) => ({
    ...c,
    files: files.filter((f) => f.internalConversationCommentId === c.id),
    likes: likesByComment[c.id] ?? [],
    tags: tagsByComment[c.id] ?? [],
  }));

  return { conversation: conv, comments: commentsWithFiles };
}

// ──────────────────────────────────────────────────────────────────────────────
// Get conversation(s) by viewName + entityId (most of the time you’ll have 0 or 1)
export async function getConversationsByViewEntity(params: {
  user: User;
  viewName: string;
  entityId: string;
}) {
  const rows = await db
    .select()
    .from(internalConversations)
    .where(
      and(
        eq(internalConversations.companyId, params.user.companyId!),
        eq(internalConversations.viewName, params.viewName),
        eq(internalConversations.entityId, params.entityId),
      )
    );

  // Return each with nested comments + files
  const results = [];
  for (const conv of rows) {
    const nested = await getConversationById({ user: params.user, conversationId: conv.id });
    results.push(nested);
  }
  return results;
}

// ──────────────────────────────────────────────────────────────────────────────
// Add a comment
export async function addComment(params: {
  user: User;
  conversationId: string;
  content: string;
  status?: string | null;
  tags?: string[]; // NEW: array of userIds to tag
  viewNameUrl?: string | null; // Optional frontend URL for email notifications
}) {
  const [conv] = await db
    .select()
    .from(internalConversations)
    .where(eq(internalConversations.id, params.conversationId))
    .limit(1);

  if (!conv) throw new Error("Conversation not found.");
  assertSameCompany(params.user.companyId, conv.companyId);

  const [row] = await db
    .insert(internalConversationComments)
    .values({
      companyId: conv.companyId,
      internalConversationId: conv.id,
      content: params.content,
      authorId: params.user.id,
      status: params.status ?? "active",
    })
    .returning({ id: internalConversationComments.id });
    
  if (params.tags?.length) {
    await addTagsToEntity({
      actor: params.user,
      entity: "internalConversationComment",
      entityId: row.id,
      taggedUserIds: params.tags,
      conversationIdForLink: conv.id,
      viewNameUrl: params.viewNameUrl,
    });
  }

  // touch conversation.updatedBy/updatedAt
  await db
    .update(internalConversations)
    .set({ updatedBy: params.user.id, updatedAt: new Date() })
    .where(eq(internalConversations.id, conv.id));

  return row;
}

// ──────────────────────────────────────────────────────────────────────────────
// Attach a file to a comment (stores PUBLIC URL in fileId)
export async function addFileToComment(params: {
  user: User;
  commentId: string;
  file: Express.Multer.File;
  name?: string | null;
  description?: string | null;
  status?: string | null;
  metadata?: Record<string, unknown> | null;
}) {
  const [comment] = await db
    .select()
    .from(internalConversationComments)
    .where(eq(internalConversationComments.id, params.commentId))
    .limit(1);

  if (!comment) throw new Error("Comment not found.");

  const [conv] = await db
    .select()
    .from(internalConversations)
    .where(eq(internalConversations.id, comment.internalConversationId!))
    .limit(1);

  if (!conv) throw new Error("Conversation not found.");
  assertSameCompany(params.user.companyId, conv.companyId);

  const keyPrefix = `companies/${conv.companyId}/internal-conversations/${conv.id}/comments/${comment.id}`;
  const publicUrl = await uploadToS3ReturnPublicUrl(params.file, keyPrefix);

  const [row] = await db
    .insert(internalConversationFiles)
    .values({
      companyId: conv.companyId,
      internalConversationCommentId: comment.id,
      fileId: publicUrl, // public URL ready to click
      name: params.name ?? params.file.originalname,
      status: params.status ?? "active",
      description: params.description ?? null,
      metadata: params.metadata ?? { mimetype: params.file.mimetype, size: params.file.size },
      uploadedBy: params.user.id,
    })
    .returning({ id: internalConversationFiles.id });

  // touch conversation
  await db
    .update(internalConversations)
    .set({ updatedBy: params.user.id, updatedAt: new Date() })
    .where(eq(internalConversations.id, conv.id));

  return row;
}

// ──────────────────────────────────────────────────────────────────────────────
// Updates (only non-key attributes)
export async function updateConversation(params: {
  user: User;
  conversationId: string;
  patch: { status?: string | null; ownerId?: string | null };
}) {
  const [conv] = await db
    .select()
    .from(internalConversations)
    .where(eq(internalConversations.id, params.conversationId))
    .limit(1);

  if (!conv) throw new Error("Conversation not found.");
  assertSameCompany(params.user.companyId, conv.companyId);

  // Do NOT allow updating viewName, entityId, companyId, id, createdBy
  const payload: any = {
    updatedBy: params.user.id,
    updatedAt: new Date(),
  };
  if (typeof params.patch.status !== "undefined") payload.status = params.patch.status;
  if (typeof params.patch.ownerId !== "undefined") payload.ownerId = params.patch.ownerId;

  await db.update(internalConversations).set(payload).where(eq(internalConversations.id, conv.id));
}

export async function updateComment(params: {
  user: User;
  commentId: string;
  patch: { status?: string | null; tags?: string[]; viewNameUrl?: string | null };
}) {
  const [comment] = await db
    .select()
    .from(internalConversationComments)
    .where(eq(internalConversationComments.id, params.commentId))
    .limit(1);

  if (!comment) throw new Error("Comment not found.");

  const [conv] = await db
    .select()
    .from(internalConversations)
    .where(eq(internalConversations.id, comment.internalConversationId!))
    .limit(1);

  if (!conv) throw new Error("Conversation not found.");
  assertSameCompany(params.user.companyId, conv.companyId);

  const payload: any = {};
  if (typeof params.patch.status !== "undefined") payload.status = params.patch.status;

  await db
    .update(internalConversationComments)
    .set({ ...payload, updatedAt: new Date() })
    .where(eq(internalConversationComments.id, comment.id));
  
      // ---- Tags upsert/diff (optional) ----
  if (Array.isArray(params.patch.tags)) {
    // Normalize
    const next = Array.from(new Set(params.patch.tags.filter(Boolean)));
    // Current tags
    const current = await listTagsFor({
      user: params.user,
      entity: "internalConversationComment",
      entityId: comment.id,
    });
    const currentUserIds = new Set(current.map(t => t.userId));
    const nextSet = new Set(next);

    const toAdd = next.filter(u => !currentUserIds.has(u));
    const toRemove = [...currentUserIds].filter(u => !nextSet.has(u));

    if (toAdd.length) {
      await addTagsToEntity({
        actor: params.user,
        entity: "internalConversationComment",
        entityId: comment.id,
        taggedUserIds: toAdd,
        conversationIdForLink: conv.id, // to include CTA in emails
        viewNameUrl: params.patch.viewNameUrl,
      });
    }
    if (toRemove.length) {
      await deleteTagsForEntityByUserIds({
        userCompanyId: params.user.companyId!,
        entity: "internalConversationComment",
        entityId: comment.id,
        userIds: toRemove,
      });
    }
  }
  }



export async function updateFile(params: {
  user: User;
  fileId: string;
  patch: { status?: string | null; name?: string | null; description?: string | null; metadata?: Record<string, unknown> | null };
}) {
  const [fileRow] = await db
    .select()
    .from(internalConversationFiles)
    .where(eq(internalConversationFiles.id, params.fileId))
    .limit(1);

  if (!fileRow) throw new Error("File not found.");

  const [comment] = await db
    .select()
    .from(internalConversationComments)
    .where(eq(internalConversationComments.id, fileRow.internalConversationCommentId!))
    .limit(1);

  if (!comment) throw new Error("Parent comment not found.");

  const [conv] = await db
    .select()
    .from(internalConversations)
    .where(eq(internalConversations.id, comment.internalConversationId!))
    .limit(1);

  if (!conv) throw new Error("Conversation not found.");
  assertSameCompany(params.user.companyId, conv.companyId);

  const payload: any = {};
  if (typeof params.patch.status !== "undefined") payload.status = params.patch.status;
  if (typeof params.patch.name !== "undefined") payload.name = params.patch.name;
  if (typeof params.patch.description !== "undefined") payload.description = params.patch.description;
  if (typeof params.patch.metadata !== "undefined") payload.metadata = params.patch.metadata;

  await db
    .update(internalConversationFiles)
    .set({ ...payload, updatedAt: new Date() })
    .where(eq(internalConversationFiles.id, fileRow.id));
}

// Get (or create if missing) a conversation by viewName + entityId, then return with nested comments/files
export async function getOrCreateConversationByViewEntity(params: {
  user: User;
  viewName: string;
  entityId: string;
  defaultStatus?: string | null;
  defaultOwnerId?: string | null;
}) {
  if (!params.user?.companyId) throw new Error("Missing user company.");

  // Try to find one (scoped to company)
  const [existing] = await db
    .select()
    .from(internalConversations)
    .where(
      and(
        eq(internalConversations.companyId, params.user.companyId),
        eq(internalConversations.viewName, params.viewName),
        eq(internalConversations.entityId, params.entityId),
      )
    )
    .limit(1);

  let convId: string;

  if (existing) {
    convId = existing.id;
  } else {
    // Create if not found
    const [created] = await db
      .insert(internalConversations)
      .values({
        companyId: params.user.companyId,
        viewName: params.viewName,
        entityId: params.entityId,
        status: params.defaultStatus ?? "open",
        ownerId: params.defaultOwnerId ?? params.user.id,
        createdBy: params.user.id,
        updatedBy: params.user.id,
      })
      .returning({ id: internalConversations.id });

    convId = created.id;
  }

  // Return same shape as getConversationById
  return await getConversationById({ user: params.user, conversationId: convId });
}

// Create a comment and attach N files (ordered), atomically-ish.
// Expects: comment content/status + array of { file, meta }
export async function createCommentWithFilesInOneGo(params: {
  user: User;
  conversationId: string;
  comment: { content: string; status?: string | null };
  files: Array<{
    file: Express.Multer.File;
    meta?: { name?: string | null; description?: string | null; status?: string | null; metadata?: Record<string, unknown> | null };
  }>;
}) {
  // 1) Create the comment (re-using existing addComment for company guard + touches)
  const { id: commentId } = await addComment({
    user: params.user,
    conversationId: params.conversationId,
    content: params.comment.content,
    status: params.comment.status ?? "active",
  });

  // 2) Attach files in the same order they were passed
  const fileResults: { id: string }[] = [];
  for (const item of params.files || []) {
    const { file, meta } = item;
    const row = await addFileToComment({
      user: params.user,
      commentId,
      file,
      name: meta?.name ?? file.originalname,
      description: meta?.description ?? null,
      status: meta?.status ?? "active",
      metadata: meta?.metadata ?? { mimetype: file.mimetype, size: file.size },
    });
    fileResults.push(row);
  }

  // 3) Return the created comment id + created file ids
  return {
    commentId,
    files: fileResults, // each is { id }
  };
}



/**
 * Delete a single file by id (DB only), touching conversation updatedAt/updatedBy.
 * Returns true if deleted.
 */
export async function deleteFileById(params: {
  user: User;
  fileId: string;
}): Promise<boolean> {
  // 1) find file row
  const [fileRow] = await db
    .select()
    .from(internalConversationFiles)
    .where(eq(internalConversationFiles.id, params.fileId))
    .limit(1);

  if (!fileRow) return false;

  // 2) find parent comment
  const [comment] = await db
    .select()
    .from(internalConversationComments)
    .where(eq(internalConversationComments.id, fileRow.internalConversationCommentId!))
    .limit(1);

  if (!comment) {
    // orphan safety: allow delete anyway (no touch)
    await db
      .delete(internalConversationFiles)
      .where(eq(internalConversationFiles.id, params.fileId));
    return true;
  }

  // 3) find parent conversation + guard company
  const [conv] = await db
    .select()
    .from(internalConversations)
    .where(eq(internalConversations.id, comment.internalConversationId!))
    .limit(1);

  if (!conv) {
    await db
      .delete(internalConversationFiles)
      .where(eq(internalConversationFiles.id, params.fileId));
    return true;
  }

  assertSameCompany(params.user.companyId, conv.companyId);

  // 4) delete file row
  await db
    .delete(internalConversationFiles)
    .where(eq(internalConversationFiles.id, params.fileId));

  // 5) touch conversation
  await db
    .update(internalConversations)
    .set({ updatedBy: params.user.id, updatedAt: new Date() })
    .where(eq(internalConversations.id, conv.id));

  return true;
}

/**
 * Delete a comment and ALL its files, touching conversation.
 * Returns counts.
 */
export async function deleteCommentWithFiles(params: {
  user: User;
  commentId: string;
}): Promise<{ deletedFiles: number; deletedComment: boolean }> {
  // 1) find comment
  const [comment] = await db
    .select()
    .from(internalConversationComments)
    .where(eq(internalConversationComments.id, params.commentId))
    .limit(1);

  if (!comment) {
    return { deletedFiles: 0, deletedComment: false };
  }

  // 2) find conversation + guard
  const [conv] = await db
    .select()
    .from(internalConversations)
    .where(eq(internalConversations.id, comment.internalConversationId!))
    .limit(1);

  if (!conv) {
    // just delete the comment + files without touch (shouldn't normally happen)
    const filesDel = await db
      .delete(internalConversationFiles)
      .where(eq(internalConversationFiles.internalConversationCommentId, comment.id))
      .returning({ id: internalConversationFiles.id });

    const commentDel = await db
      .delete(internalConversationComments)
      .where(eq(internalConversationComments.id, comment.id))
      .returning({ id: internalConversationComments.id });

    return { deletedFiles: filesDel.length, deletedComment: commentDel.length > 0 };
  }

  assertSameCompany(params.user.companyId, conv.companyId);

  // 3) delete all files for this comment
  const filesDel = await db
    .delete(internalConversationFiles)
    .where(eq(internalConversationFiles.internalConversationCommentId, comment.id))
    .returning({ id: internalConversationFiles.id });

  // 4) delete the comment
  const commentDel = await db
    .delete(internalConversationComments)
    .where(eq(internalConversationComments.id, comment.id))
    .returning({ id: internalConversationComments.id });

  // 5) touch conversation
  await db
    .update(internalConversations)
    .set({ updatedBy: params.user.id, updatedAt: new Date() })
    .where(eq(internalConversations.id, conv.id));

  return { deletedFiles: filesDel.length, deletedComment: commentDel.length > 0 };
}

export async function updateCommentLikes(params: {
  user: User;
  conversationId: string;
  commentId: string;
  likes: string[];
}) {
  // Validate conversation & company guard
  const [conv] = await db.select().from(internalConversations).where(eq(internalConversations.id, params.conversationId)).limit(1);
  if (!conv) throw new Error("Conversation not found.");
  assertSameCompany(params.user.companyId, conv.companyId);

  // Validate comment belongs to conv
  const [comment] = await db.select().from(internalConversationComments).where(eq(internalConversationComments.id, params.commentId)).limit(1);
  if (!comment || comment.internalConversationId !== conv.id) throw new Error("Comment not found in conversation.");

  // Dedup likes
  const unique = Array.from(new Set(params.likes)).filter(Boolean);

  await db.update(internalConversationComments)
    .set({ likes: unique, updatedAt: new Date() })
    .where(eq(internalConversationComments.id, comment.id));
}