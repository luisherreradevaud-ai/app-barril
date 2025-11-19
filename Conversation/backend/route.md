// src/routes/internalConversations.ts
import express, { Request, Response } from "express";
import passport from "passport";
import multer from "multer";
import { asyncHandler } from "../middleware/asyncHandler";
import { ok, created, fail } from "../middleware/responseHelpers";
import * as svc from "../services/internalConversationService";
import { User, tags } from "../db/schema";
import { addTagsToEntity } from "../services/tagService"

const router = express.Router();
router.use(passport.authenticate("jwt", { session: false }));

interface AuthenticatedRequest extends Request {
  user?: User & { companyId?: string | null };
}

const upload = multer({
  storage: multer.memoryStorage(),
  limits: { fileSize: 20 * 1024 * 1024 }, // 20MB
});

/**
 * POST /internal-conversations
 * Body: { viewName, entityId, status?, ownerId? }
 */
router.post(
  "/",
  asyncHandler(async (req: AuthenticatedRequest, res: Response) => {
    const companyId = req.user?.companyId;
    const userId = req.user?.id;
    if (!companyId || !userId) return fail(res, 401, "Unauthenticated.");

    const { viewName, entityId, status, ownerId } = req.body ?? {};
    if (!viewName || !entityId) return fail(res, 400, "viewName and entityId are required.");

    try {
      const row = await svc.createConversation({
        user: req.user!,
        viewName,
        entityId,
        status,
        ownerId,
        
      });
      return created(res, row, "Conversation created.");
    } catch (err: any) {
      return fail(res, err.statusCode ?? 500, err.message || "Something went wrong!");
    }
  })
);

/**
 * GET /internal-conversations/:id
 * Returns conversation with comments and files
 */
router.get(
  "/:id",
  asyncHandler(async (req: AuthenticatedRequest, res: Response) => {
    const companyId = req.user?.companyId;
    const userId = req.user?.id;
    if (!companyId || !userId) return fail(res, 401, "Unauthenticated.");

    try {
      const data = await svc.getConversationById({
        user: req.user!,
        conversationId: req.params.id,
      });
      return ok(res, data);
    } catch (err: any) {
      return fail(res, err.statusCode ?? 500, err.message || "Something went wrong!");
    }
  })
);

/**
 * GET /internal-conversations/by?viewName=...&entityId=...
 */
router.get(
  "/by",
  asyncHandler(async (req: AuthenticatedRequest, res: Response) => {
    const companyId = req.user?.companyId;
    const userId = req.user?.id;
    if (!companyId || !userId) return fail(res, 401, "Unauthenticated.");

    const { viewName, entityId } = req.query as { viewName?: string; entityId?: string };
    if (!viewName || !entityId) return fail(res, 400, "viewName and entityId are required.");

    try {
      const data = await svc.getConversationsByViewEntity({
        user: req.user!,
        viewName,
        entityId,
      });
      return ok(res, data);
    } catch (err: any) {
      return fail(res, err.statusCode ?? 500, err.message || "Something went wrong!");
    }
  })
);

/**
 * POST /internal-conversations/:id/comments
 * Body: { content, status?, tags?, viewNameUrl? }
 */
router.post(
  "/:id/comments",
  asyncHandler(async (req: AuthenticatedRequest, res: Response) => {
    const companyId = req.user?.companyId;
    const userId = req.user?.id;
    if (!companyId || !userId) return fail(res, 401, "Unauthenticated.");

    const { content, status, tags: bodyTags, viewNameUrl } = req.body ?? {};
    if (!content) return fail(res, 400, "content is required.");

    try {
      const row = await svc.addComment({
        user: req.user!,
        conversationId: req.params.id,
        content,
        status,
        tags: Array.isArray(bodyTags) ? bodyTags : undefined,
        viewNameUrl,
      });
      return created(res, row, "Comment added.");
    } catch (err: any) {
      return fail(res, err.statusCode ?? 500, err.message || "Something went wrong!");
    }
  })
);

/**
 * POST /internal-conversations/comments/:commentId/files
 * multipart/form-data, field: file
 * Optional: name, description, status, metadata (JSON string)
 */
router.post(
  "/comments/:commentId/files",
  upload.single("file"),
  asyncHandler(async (req: AuthenticatedRequest, res: Response) => {
    const companyId = req.user?.companyId;
    const userId = req.user?.id;
    if (!companyId || !userId) return fail(res, 401, "Unauthenticated.");
    if (!req.file) return fail(res, 400, "file is required. Use form-field 'file'.");

    const { name, description, status } = req.body ?? {};
    const metadata =
      typeof req.body?.metadata === "string"
        ? (() => {
            try {
              return JSON.parse(req.body.metadata);
            } catch {
              return null;
            }
          })()
        : null;

    try {
      const row = await svc.addFileToComment({
        user: req.user!,
        commentId: req.params.commentId,
        file: req.file,
        name,
        description,
        status,
        metadata,
      });
      return created(res, row, "File attached.");
    } catch (err: any) {
      return fail(res, err.statusCode ?? 500, err.message || "Something went wrong!");
    }
  })
);

/**
 * PUT /internal-conversations/:id
 * Body: { status?, ownerId? }
 */
router.put(
  "/:id",
  asyncHandler(async (req: AuthenticatedRequest, res: Response) => {
    const companyId = req.user?.companyId;
    const userId = req.user?.id;
    if (!companyId || !userId) return fail(res, 401, "Unauthenticated.");

    const { status, ownerId } = req.body ?? {};

    try {
      await svc.updateConversation({
        user: req.user!,
        conversationId: req.params.id,
        patch: { status, ownerId },
      });
      return ok(res, null, "Conversation updated.");
    } catch (err: any) {
      return fail(res, err.statusCode ?? 500, err.message || "Something went wrong!");
    }
  })
);

/**
 * PUT /internal-conversations/comments/:id
 * Body: { status?, tags?, viewNameUrl? }
 */
router.put(
  "/comments/:id",
  asyncHandler(async (req: AuthenticatedRequest, res: Response) => {
    const companyId = req.user?.companyId;
    const userId = req.user?.id;
    if (!companyId || !userId) return fail(res, 401, "Unauthenticated.");

    const { status, viewNameUrl } = req.body ?? {};
    const tags = Array.isArray(req.body?.tags) ? (req.body.tags as string[]) : undefined;

    try {
      await svc.updateComment({
        user: req.user!,
        commentId: req.params.id,
        patch: { status, tags, viewNameUrl },
      });
      return ok(res, null, "Comment updated.");
    } catch (err: any) {
      return fail(res, err.statusCode ?? 500, err.message || "Something went wrong!");
    }
  })
);

/**
 * PUT /internal-conversations/files/:id
 * Body: { status?, name?, description?, metadata? }
 */
router.put(
  "/files/:id",
  asyncHandler(async (req: AuthenticatedRequest, res: Response) => {
    const companyId = req.user?.companyId;
    const userId = req.user?.id;
    if (!companyId || !userId) return fail(res, 401, "Unauthenticated.");

    const { status, name, description, metadata } = req.body ?? {};

    try {
      await svc.updateFile({
        user: req.user!,
        fileId: req.params.id,
        patch: {
          status,
          name,
          description,
          metadata:
            typeof metadata === "string"
              ? (() => {
                  try {
                    return JSON.parse(metadata);
                  } catch {
                    return undefined;
                  }
                })()
              : metadata,
        },
      });
      return ok(res, null, "File updated.");
    } catch (err: any) {
      return fail(res, err.statusCode ?? 500, err.message || "Something went wrong!");
    }
  })
);

/**
 * GET /internal-conversations/view/:viewName/:entityId
 * If a conversation exists -> return it.
 * If not -> create it and return it (same shape as GET /:id).
 */
router.get(
  "/view/:viewName/:entityId",
  asyncHandler(async (req: AuthenticatedRequest, res: Response) => {
    const companyId = req.user?.companyId;
    const userId    = req.user?.id;
    if (!companyId || !userId) return fail(res, 401, "Unauthenticated.");

    const { viewName, entityId } = req.params;
    if (!viewName || !entityId) return fail(res, 400, "viewName and entityId are required.");

    try {
      const data = await svc.getOrCreateConversationByViewEntity({
        user: req.user!,
        viewName,
        entityId,
      });

      // Always 200 for a GET; message tells whether it was created implicitly or not is optional.
      return ok(res, data, "Conversation ready.");
    } catch (err: any) {
      return fail(res, err.statusCode ?? 500, err.message || "Something went wrong!");
    }
  })
);

// ──────────────────────────────────────────────────────────────
// Create a comment + attach multiple files in one go
// multipart/form-data fields:
// 1) key: "comment", type: text, value: JSON: { content: "...", status?: "..." }
// 2) For each file i (1..N):
//    - key: "file<i>", type: text, value: JSON: { name?, description?, status?, metadata? }
//    - key: "file<i>", type: file, value: the actual file
// Files are processed in ascending <i> order.
// ──────────────────────────────────────────────────────────────

router.post(
  "/:id/comments-with-files",
  upload.any(),
  asyncHandler(async (req: AuthenticatedRequest, res: Response) => {
    const companyId = req.user?.companyId;
    const userId = req.user?.id;
    if (!companyId || !userId) return fail(res, 401, "Unauthenticated.");

    // Helper: parse JSON if it's a JSON-looking string
    const parseMaybeJson = <T = any>(v: any): T => {
      if (typeof v !== "string") return v as T;
      const s = v.trim();
      if (!s) return s as unknown as T;
      if ((s.startsWith("{") && s.endsWith("}")) || (s.startsWith("[") && s.endsWith("]"))) {
        try { return JSON.parse(s) as T; } catch {}
      }
      return v as T;
    };

    // 1) comment JSON
    const rawComment = (req.body?.comment ?? "").toString();
    if (!rawComment) return fail(res, 400, "Field 'comment' (JSON) is required.");
    const comment = parseMaybeJson<{ content?: string; status?: string | null }>(rawComment);
    if (!comment?.content || typeof comment.content !== "string" || !comment.content.trim()) {
      return fail(res, 400, "comment.content is required.");
    }

    const commentTags = Array.isArray((comment as any)?.tags)
    ? ((comment as any).tags as string[])
    : undefined;

    // Extract viewNameUrl from form-data (can be sent as a text field)
    const viewNameUrl = req.body?.viewNameUrl ? req.body.viewNameUrl.toString() : undefined;

    // 2) collect "file<i>" pairs (meta text + file)
    type Pending = { idx: number; meta?: any; file?: Express.Multer.File };
    const map = new Map<number, Pending>();

    // 2a) scan text fields named file<i>
    for (const [key, val] of Object.entries(req.body || {})) {
      const m = key.match(/^file(\d+)$/i);
      if (!m) continue;
      const idx = Number(m[1]);
      if (!map.has(idx)) map.set(idx, { idx });
      const pending = map.get(idx)!;
      pending.meta = parseMaybeJson(val);
    }

    // 2b) scan file uploads named file<i>
    const filesIn = (req.files || []) as Express.Multer.File[];
    for (const f of filesIn) {
      const m = f.fieldname?.match(/^file(\d+)$/i);
      if (!m) continue;
      const idx = Number(m[1]);
      if (!map.has(idx)) map.set(idx, { idx });
      map.get(idx)!.file = f;
    }

    // 2c) build ordered array; allow zero files
    const orderedIdx = Array.from(map.keys()).sort((a, b) => a - b);
    const toAttach: Array<{ file: Express.Multer.File; meta?: any }> = [];

    for (const idx of orderedIdx) {
      const item = map.get(idx)!;
      if (!item.file) {
        return fail(res, 400, `Missing file payload for 'file${idx}'. Provide both a text JSON and a file part.`);
      }
      // meta is optional; if provided and a string, we attempted to parse above
      toAttach.push({ file: item.file, meta: item.meta });
    }

    try {
      const result = await svc.createCommentWithFilesInOneGo({
        user: req.user!,
        conversationId: req.params.id,
        comment: {
          content: comment.content.trim(),
          status: typeof comment.status === "string" ? comment.status : undefined,
        },
        files: toAttach,
      });

      if (commentTags?.length) {
        await addTagsToEntity({
          actor: req.user!,
          entity: "internalConversationComment",
          entityId: result.commentId,
          taggedUserIds: commentTags,
          conversationIdForLink: req.params.id,
          viewNameUrl,
        });
      }

      return created(res, result, "Comment created and files attached.");
    } catch (err: any) {
      return fail(res, err.statusCode ?? 500, err.message || "Something went wrong!");
    }
  })
);



/**
 * DELETE /internal-conversations/files/:id
 * Deletes a single file record (DB). (Does not remove the S3 object.)
 */
router.delete(
  "/files/:id",
  asyncHandler(async (req: AuthenticatedRequest, res: Response) => {
    const companyId = req.user?.companyId;
    const userId = req.user?.id;
    if (!companyId || !userId) return fail(res, 401, "Unauthenticated.");

    try {
      const deleted = await svc.deleteFileById({
        user: req.user!,
        fileId: req.params.id,
      });

      if (!deleted) return fail(res, 404, "File not found.");
      return ok(res, null, "File deleted.");
    } catch (err: any) {
      return fail(res, err.statusCode ?? 500, err.message || "Something went wrong!");
    }
  })
);

/**
 * DELETE /internal-conversations/comments/:id
 * Deletes the comment and all its files.
 */
router.delete(
  "/comments/:id",
  asyncHandler(async (req: AuthenticatedRequest, res: Response) => {
    const companyId = req.user?.companyId;
    const userId = req.user?.id;
    if (!companyId || !userId) return fail(res, 401, "Unauthenticated.");

    try {
      const { deletedFiles, deletedComment } = await svc.deleteCommentWithFiles({
        user: req.user!,
        commentId: req.params.id,
      });

      if (!deletedComment) return fail(res, 404, "Comment not found.");
      return ok(res, { deletedFiles }, "Comment and files deleted.");
    } catch (err: any) {
      return fail(res, err.statusCode ?? 500, err.message || "Something went wrong!");
    }
  })
);

// src/routes/internalConversations.ts (add below other routes)
router.put(
  "/:internalConversationId/comments/:commentId/likes",
  asyncHandler(async (req: AuthenticatedRequest, res: Response) => {
    const companyId = req.user?.companyId;
    if (!companyId || !req.user?.id) return fail(res, 401, "Unauthenticated.");

    const likes = Array.isArray(req.body?.likes) ? (req.body.likes as string[]) : null;
    if (!likes) return fail(res, 400, "likes must be an array of userIds.");

    try {
      await svc.updateCommentLikes({
        user: req.user!,
        conversationId: req.params.internalConversationId,
        commentId: req.params.commentId,
        likes,
      });
      return ok(res, null, "Likes updated.");
    } catch (err: any) {
      return fail(res, err.statusCode ?? 500, err.message || "Something went wrong!");
    }
  })
);

export default router;