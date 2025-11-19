'use client'

import React, { useState } from 'react'
import { ClockIcon, PaperClipIcon, CheckCircleIcon } from '@heroicons/react/24/outline'
import type { TaskListItem } from '@/app/lib/api/tasks'
import { updateTask } from '@/app/lib/api/tasks'
import TaskModal from './TaskModal'

interface TaskCardProps {
  task: TaskListItem
  entityId: string
  onSave: () => void
  allUsers?: Array<{ id: string; fullName: string; avatarUrl?: string | null }>
}

export default function TaskCard({ task, entityId, onSave, allUsers = [] }: TaskCardProps) {
  const [isModalOpen, setIsModalOpen] = useState(false)

  // Get user details from IDs
  const getUserDetails = () => {
    if (!task.users || task.users.length === 0) return []

    // If users are already objects with userName, return them
    if (typeof task.users[0] === 'object' && 'userName' in task.users[0]) {
      return task.users as Array<{ userName?: string }>
    }

    // If users are IDs, map them to user details
    if (typeof task.users[0] === 'string') {
      return (task.users as string[])
        .map(userId => allUsers.find(u => u.id === userId))
        .filter(Boolean)
    }

    return []
  }

  const userDetails = getUserDetails()

  const handleClick = async () => {
    setIsModalOpen(true)
  }

  const handleCloseModal = () => {
    setIsModalOpen(false)
  }

  const handleSaveTask = () => {
    onSave()
  }

  const handleStatusToggle = async (e: React.ChangeEvent<HTMLInputElement>) => {
    e.stopPropagation()
    const newStatus = task.status === 'Completed' ? 'Pending' : 'Completed'
    await updateTask(task.id, { status: newStatus })
    onSave()
  }

  const formatDueDate = (dateString: string | null | undefined) => {
    if (!dateString) return null
    const date = new Date(dateString)
    return date.toLocaleDateString('en-US', {
      month: 'short',
      day: 'numeric',
      year: 'numeric'
    })
  }

  const getChecklistProgress = () => {
    if (!task.checkList || !Array.isArray(task.checkList)) return null
    const total = task.checkList.length
    const completed = task.checkList.filter((item: any) => item.completed).length
    return { completed, total }
  }

  const checklistProgress = getChecklistProgress()

  return (
    <>
    <div
      onClick={handleClick}
      className="block
                p-4
                self-stretch
                rounded-xl
                bg-gray-50
                hover:shadow-[0px_2px_4px_0px_rgba(0,0,0,0.2)] cursor-pointer
                border-1
                border-gray-200
                transition-[300ms]
                hover:border-gray-300
                "
    >
      {/* Checkbox and Task Name */}
      <div className="flex items-start gap-3 mb-3">
        <input
          type="checkbox"
          checked={task.status === 'Completed'}
          onChange={handleStatusToggle}
          onClick={(e) => e.stopPropagation()}
          className="mt-1 w-4 h-4 rounded border-gray-300 focus:ring-[#6A1693] cursor-pointer accent-[#6A1693]"
        />
        <h3 className={`font-semibold text-base flex-1 ${task.status === 'Completed' ? 'text-gray-500' : 'text-gray-900'}`}>
          {task.name}
        </h3>
      </div>

      {/* Task Metadata */}
      <div className="flex flex-wrap items-center gap-4 text-sm text-gray-600">

        {userDetails && userDetails.length > 0 && (
          <div className="flex items-center gap-2">
            <div className="flex -space-x-2">
              {userDetails.slice(0, 3).map((user: any, index: number) => (
                <div
                  key={index}
                  className="w-6 h-6 rounded-full bg-[#6A1693] text-white flex items-center justify-center text-xs font-medium border-2 border-white overflow-hidden"
                  title={user?.fullName || 'Unknown User'}
                >
                  {user?.avatarUrl ? (
                    <img
                      src={user.avatarUrl}
                      alt={user.fullName || 'User'}
                      className="w-full h-full object-cover"
                    />
                  ) : (
                    user?.fullName ? user.fullName.charAt(0).toUpperCase() : '?'
                  )}
                </div>
              ))}
              {userDetails.length > 3 && (
                <div className="w-6 h-6 rounded-full bg-gray-300 text-gray-600 flex items-center justify-center text-xs font-medium border-2 border-white">
                  +{userDetails.length - 3}
                </div>
              )}
            </div>
          </div>
        )}
        
        {task.dueDate && (
          <div className="flex items-center gap-1">
            <ClockIcon className={`w-4 h-4 ${task.isOverdue ? 'text-red-500' : 'text-gray-400'}`} />
            <span className={task.isOverdue ? 'text-red-500 font-medium' : ''}>
              {formatDueDate(task.dueDate)}
            </span>
          </div>
        )}

        {/* File Count */}
        {task.fileCount > 0 && (
          <div className="flex items-center gap-1">
            <PaperClipIcon className="w-4 h-4 text-gray-400" />
            <span>{task.fileCount}</span>
          </div>
        )}

        {/* Checklist Progress */}
        {checklistProgress && (
          <div className="flex items-center gap-1">
            <CheckCircleIcon className="w-4 h-4 text-gray-400" />
            <span>
              {checklistProgress.completed}/{checklistProgress.total}
            </span>
          </div>
        )}
        
      </div>

      {/* Assigned Users */}
      
    </div>

    <TaskModal
      isOpen={isModalOpen}
      onClose={handleCloseModal}
      taskId={task.id}
      entityId={entityId}
      onSave={handleSaveTask}
    />
    </>
  )
}
