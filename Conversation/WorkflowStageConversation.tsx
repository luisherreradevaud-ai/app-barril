'use client'
import React, { useState, useEffect } from 'react'
import { updateStatusDocumentStage, uploadWorkflowStageDocument } from '../../api/workflows'
import { showToast } from '../../functions/toast'
import ConversationPostBoxWithUploads from './ConversationPostBoxWithUploads'
import ConversationComment from './ConversationComment'
import { fetchUsers } from '../../api/users'
import LoadingSpinnerDark from '../LoadingSpinnerDark'

function sortByDateDesc(arr, key) {
  return [...arr].sort((a, b) => {
    const dateA = new Date(a[key] as string).getTime();
    const dateB = new Date(b[key] as string).getTime();
    return dateB - dateA;
  });
}

type WorkflowStageConversationProps = {
  workflowId: string
  workflowStageId: string
  workflowStageData: any
}

/**
 * WorkflowStageConversation displays workflow stage documents
 * with approval functionality (Accept/Reject buttons for pending docs)
 */
export default function WorkflowStageConversation({
  workflowId,
  workflowStageId,
  workflowStageData
}: WorkflowStageConversationProps) {

  const [documents, setDocuments] = useState<any[]>([])
  const [users, setUsers] = useState<any[]>([])
  const [tick, setTick] = useState(0)

  // Load documents from workflowStageData
  useEffect(() => {
    if (workflowStageData?.stageDocuments) {
      // Transform workflow documents to conversation comment format
      const transformedDocs = workflowStageData.stageDocuments.map((doc: any) => ({
        id: doc.id,
        content: doc.description || '',
        authorName: doc.uploaderName,
        authorAvatarUrl: doc.uploaderAvatarUrl,
        createdAt: doc.createdAt,
        status: doc.status, // Pending, Approved, Rejected
        files: doc.fileId ? [{
          fileId: doc.url || doc.fileId,
          id: doc.fileId,
          name: doc.name,
          url: doc.url,
          type: doc.type,
          size: doc.size
        }] : []
      }))
      setDocuments(transformedDocs)
    }
  }, [workflowStageData?.stageDocuments])

  // Load users for mentions
  useEffect(() => {
    const loadUsers = async () => {
      try {
        const apiResponseUsers = await fetchUsers()
        if (apiResponseUsers) {
          setUsers(apiResponseUsers)
        }
      } catch (error) {
        console.error('Failed to fetch users:', error)
      }
    }
    loadUsers()
  }, [])

  // Auto-refresh every 60 seconds
  useEffect(() => {
    const id = setInterval(() => setTick(t => t + 1), 60_000)
    return () => clearInterval(id)
  }, [])

  const handleApprove = async (documentId: string) => {
    try {
      const response = await updateStatusDocumentStage(
        workflowId,
        workflowStageId,
        documentId,
        'Approved'
      )

      if (response) {
        // Reload workflow stage data
        if (workflowStageData?.loadData) {
          await workflowStageData.loadData()
        }
      }
    } catch (error) {
      console.error('Failed to approve document:', error)
      throw error
    }
  }

  const handleReject = async (documentId: string) => {
    try {
      const response = await updateStatusDocumentStage(
        workflowId,
        workflowStageId,
        documentId,
        'Rejected'
      )

      if (response) {
        // Reload workflow stage data
        if (workflowStageData?.loadData) {
          await workflowStageData.loadData()
        }
      }
    } catch (error) {
      console.error('Failed to reject document:', error)
      throw error
    }
  }

  const handlePending = async (documentId: string) => {
    try {
      const response = await updateStatusDocumentStage(
        workflowId,
        workflowStageId,
        documentId,
        'Pending'
      )

      if (response) {
        // Reload workflow stage data
        if (workflowStageData?.loadData) {
          await workflowStageData.loadData()
        }
      }
    } catch (error) {
      console.error('Failed to change status to pending:', error)
      throw error
    }
  }

  const handlePostComment = async (files: any[], content: string, taggedUsers: any[]) => {
    try {
      // Convert plain @Name mentions to @Name{id} format for backend storage
      // This allows RenderedMentions component to render them with purple styling and links
      let contentWithIds = content
      
      // Get user objects from IDs to access fullName
      const taggedUserObjects = users.filter(u => taggedUsers.includes(u.id))
      taggedUserObjects.forEach((user) => {
        // Replace @Name (not already followed by {id}) with @Name{id}
        const regex = new RegExp(`@${user.fullName}(?!\\{)`, 'g')
        contentWithIds = contentWithIds.replace(regex, `@${user.fullName}{${user.id}}`)
      })
      
      // Construct viewNameUrl for email notifications
      const viewNameUrl = `${window.location.origin}/workflows/edit/${workflowId}?workflowStageId=${workflowStageId}`
      
      // Upload each file as a workflow stage document
      for (const file of files) {
        await uploadWorkflowStageDocument(
          workflowId,
          workflowStageId,
          file.file,
          file.displayName || file.file.name,
          contentWithIds,  // Use converted content with {id} format
          'Pending',
          taggedUsers,  // Already normalized to IDs by ConversationPostBoxWithUploads
          viewNameUrl
        )
      }

      // If there are no files, create a reference-only entry to keep the comment
      if (files.length === 0) {
        const fallbackName = content.trim().length > 0 ? content.trim().slice(0, 50) : 'Comment';
        await uploadWorkflowStageDocument(
          workflowId,
          workflowStageId,
          null,
          fallbackName,
          contentWithIds,
          'Pending',
          taggedUsers,
          viewNameUrl
        )
      }

      showToast('Post submitted successfully.')

      // Reload workflow stage data
      if (workflowStageData?.loadData) {
        await workflowStageData.loadData()
      }
    } catch (error) {
      console.error('Failed to upload document:', error)
      showToast('Error uploading document.')
      throw error
    }
  }

  const sortedConversation = sortByDateDesc(documents, "createdAt")

  // Create a mock conversation object for the post box
  const mockConversation = {
    conversation: {
      id: workflowStageId
    }
  }

  return (
    <div className="full">
      <div className="w-full">
        <div className="full">
          <div className="pt-4 pb-0 w-full">
            <div className="space-y-4 w-full bg-white rounded-t-[16px]">
              <ConversationPostBoxWithUploads
                conversation={mockConversation}
                users={users}
                loadData={async () => {
                  if (workflowStageData?.loadData) {
                    await workflowStageData.loadData()
                  }
                }}
                entityId={workflowStageId}
                viewName="workflowStage"
                customPostHandler={handlePostComment}
              />
            </div>
          </div>
          {sortedConversation.length > 0 && (
            <div className="divide-y divide-gray-200 pt-6 bg-white">
              {sortedConversation.map((comment) => (
                <div key={comment.id} className="even:bg-[#F9F9F9] odd:bg-white">
                  <ConversationComment
                    comment={comment}
                    setComments={setDocuments}
                    approvalMode={true}
                    onApprove={handleApprove}
                    onReject={handleReject}
                    onPending={handlePending}
                  />
                </div>
              ))}
            </div>
          )}
        </div>
      </div>
    </div>
  )
}
