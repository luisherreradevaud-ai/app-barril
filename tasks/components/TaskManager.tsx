'use client'

import React, { useState, useEffect } from 'react'
import { PlusIcon } from '@heroicons/react/24/outline'
import TaskCard from './TaskCard'
import { fetchTasksByEntity, createTask, type TaskListItem, TaskListUser, TaskListLabel } from '@/app/lib/api/tasks'
import LoadingSpinner from '@/app/lib/components/LoadingSpinner'
import { btnPrimaryMedium, btnSecondary, btnSecondaryMedium } from '../../styles/Buttons'

export default function TasksManager(
    {
        entityId = null
    } : {
        entityId: string
    }
) {
  const [tasks, setTasks] = useState<TaskListItem[]>([])
  const [users, setUsers] = useState<TaskListUser[]>([])
  const [labels, setLabels] = useState<TaskListLabel[]>([])
  const [isLoading, setIsLoading] = useState(true)
  const [isCreatingTask, setIsCreatingTask] = useState(false)
  const [newTaskName, setNewTaskName] = useState('')

  useEffect(() => {
    loadTasks()
  }, [])

  const loadTasks = async () => {
    //setIsLoading(true)
    try {
      const data = await fetchTasksByEntity(entityId)
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

  const handleCreateTask = () => {
    setIsCreatingTask(true)
    setNewTaskName('')
  }

  const handleSaveNewTask = async () => {
    if (!newTaskName.trim()) return

    const taskData = {
      name: newTaskName,
      entityId: entityId,
      users: [],
      labels: []
    }

    await createTask(taskData)
    setIsCreatingTask(false)
    setNewTaskName('')
    loadTasks()
  }

  if (isLoading) {
    return (
      <div className="flex items-center justify-center min-h-screen">
        <LoadingSpinner />
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
                        entityId={entityId}
                        onSave={loadTasks}
                        allUsers={users}
                    />
                </div>
            ))}

            {isCreatingTask ? (
                <div>
                    <textarea
                        value={newTaskName}
                        onChange={(e) => setNewTaskName(e.target.value)}
                        placeholder="Task name..."
                        className="mb-2 p-4 self-stretch rounded-[16px] bg-white shadow-[0px_2px_4px_0px_rgba(0,0,0,0.08)] w-full text-sm bg-gray-50"
                        rows={2}
                        autoFocus
                    />
                    <div className="flex gap-2 mt-3">
                        <button
                            onClick={handleSaveNewTask}
                            className={btnPrimaryMedium}
                        >
                            Save
                        </button>
                        <button
                            onClick={() => setIsCreatingTask(false)}
                            className={btnSecondaryMedium}
                        >
                            Cancel
                        </button>
                    </div>
                </div>
            ) : (
                <div className=" flex justify-center">
                  <button
                    onClick={handleCreateTask}
                    className="flex items-center justify-center gap-1 h-8 py-1 px-2 font-['Roboto'] text-sm font-semibold leading-normal text-[#6A1693] cursor-pointer"
                  >
                    <svg xmlns="http://www.w3.org/2000/svg" width={16} height={16} fill="none" viewBox="0 0 24 24" stroke="#6A1693" strokeWidth={2}>
                      <path strokeLinecap="round" strokeLinejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    Add
                  </button>
                </div>
            )}


            </div>
    </div>
  )
}
