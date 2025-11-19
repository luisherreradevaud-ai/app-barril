'use client'

import React, { useState, useEffect } from 'react'
import TaskCard from './TaskCard'
import { fetchTasksAssignedToMe, type TaskListItem, TaskListUser, TaskListLabel } from '@/app/lib/api/tasks'
import LoadingSpinner from '@/app/lib/components/LoadingSpinner'

export default function MyTasksViewer() {
  const [tasks, setTasks] = useState<TaskListItem[]>([])
  const [users, setUsers] = useState<TaskListUser[]>([])
  const [labels, setLabels] = useState<TaskListLabel[]>([])
  const [isLoading, setIsLoading] = useState(true)

  useEffect(() => {
    loadTasks()
  }, [])

  const loadTasks = async () => {
    try {
      const data = await fetchTasksAssignedToMe()
      if (data) {
        setTasks(data.tasks)
        setUsers(data.users)
        setLabels(data.labels)
      }
    } catch (error) {
      console.error('Error loading tasks:', error)
    } finally {
      setIsLoading(false)
    }
  }

  if (isLoading) {
    return (
      <div className="flex items-center justify-center py-8">
        <LoadingSpinner />
      </div>
    )
  }

  if (tasks.length === 0) {
    return (
      <div className="text-center py-8 text-gray-500 text-sm">
        No tasks assigned to you
      </div>
    )
  }

  return (
    <div className="container mx-auto">
      <div className="gap-4">
        {tasks.map(task => (
          <div key={task.id} className="mb-2">
            <TaskCard
              task={task}
              entityId={task.entityId}
              onSave={loadTasks}
              allUsers={users}
            />
          </div>
        ))}
      </div>
    </div>
  )
}

