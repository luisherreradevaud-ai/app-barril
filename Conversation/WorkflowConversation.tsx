'use client'
import React, { useEffect, useState } from 'react'
import Conversation from './Conversation'
import { updateStatusDocumentStage } from '../../api/workflows'
import LoadingSpinnerDark from '../LoadingSpinnerDark'

type WorkflowConversationProps = {
  workflowId: string
  workflowStageId: string
  workflowStageData: any
}

/**
 * WorkflowConversation wraps the generic Conversation component
 * and provides workflow-specific approval functionality.
 *
 * It transforms workflowStageDocuments into conversation comment format
 * and handles approve/reject actions via the workflows API.
 */
export default function WorkflowConversation({
  workflowId,
  workflowStageId,
  workflowStageData
}: WorkflowConversationProps) {

  const handleApprove = async (documentId: string) => {
    try {
      const response = await updateStatusDocumentStage(
        workflowId,
        workflowStageId,
        documentId,
        'Approved'
      )

      if (response) {
        // Reload the workflow stage data to get updated status
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
        // Reload the workflow stage data to get updated status
        if (workflowStageData?.loadData) {
          await workflowStageData.loadData()
        }
      }
    } catch (error) {
      console.error('Failed to reject document:', error)
      throw error
    }
  }

  return (
    <Conversation
      viewName="workflowStage"
      entityId={workflowStageId}
      approvalMode={true}
      onApprove={handleApprove}
      onReject={handleReject}
    />
  )
}
