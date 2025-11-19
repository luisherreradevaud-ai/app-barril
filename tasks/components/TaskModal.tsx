'use client'

import React, { useState, useEffect } from 'react'
import TaskBigModal from './TaskBigModal'
import { useForm, FormProvider } from 'react-hook-form'
import { createTask, updateTask, deleteTask, fetchTaskById, type CreateTaskPayload, type UpdateTaskPayload, type DueDateReminder } from '@/app/lib/api/tasks'
import { fetchUsers } from '@/app/lib/api/users'
import { fetchLabels, saveLabel as saveLabelAPI, deleteLabel as deleteLabelAPI } from '@/app/lib/api/labels'
import { showToast } from '@/app/lib/functions/toast'
import { btnPrimary, btnSecondary, btnDanger, btnAction } from '@/app/lib/styles/Buttons'
import { inputText, inputTextarea } from '@/app/lib/styles/Forms'
import LoadingSpinner from '@/app/lib/components/LoadingSpinner'
import { PlusIcon, XMarkIcon, TrashIcon, PaperClipIcon, ExclamationTriangleIcon } from '@heroicons/react/24/outline'
import LabelManager from './LabelManager'
import DateReminderManager from './DateReminderManager'
import ChecklistManager, { type Checklist } from './ChecklistManager'
import UserAssignmentManager from './UserAssignmentManager'
import Conversation from '../Conversation/Conversation'
import Modal from '@/app/lib/components/Modal'
import TaskFileManager from './TaskFileManager'

interface TaskModalProps {
  isOpen: boolean
  onClose: () => void
  taskId?: string | null
  entityId: string
  onSave: () => void
}


export default function TaskModal({ isOpen, onClose, taskId, entityId, onSave }: TaskModalProps) {


  const [isLoading, setIsLoading] = useState(false)
  const [isSaving, setIsSaving] = useState(false)
  const [existingFiles, setExistingFiles] = useState<any[]>([])
  const [checklists, setChecklists] = useState<Checklist[]>([])
  const [links, setLinks] = useState<{ id: string; url: string; title?: string }[]>([])
  const [allUsers, setAllUsers] = useState<any[]>([])
  const [allLabels, setAllLabels] = useState<any[]>([])
  const [selectedUsers, setSelectedUsers] = useState<string[]>([])
  const [selectedLabels, setSelectedLabels] = useState<string[]>([])
  const [saveTimeout, setSaveTimeout] = useState<NodeJS.Timeout | null>(null)
  const [isDeleteModalOpen, setIsDeleteModalOpen] = useState(false)
  const [showFiles, setShowFiles] = useState(false)
  const [editingLinkId, setEditingLinkId] = useState<string | null>(null)
  const [editingLinkTitle, setEditingLinkTitle] = useState('')
  const [editingLinkUrl, setEditingLinkUrl] = useState('')
  const [taskStatus, setTaskStatus] = useState<string>('Pending')

  const methods = useForm<CreateTaskPayload>({
    defaultValues: {
      name: '',
      description: '',
      entityId: entityId,
      startDate: null,
      dueDate: null,
      dueDateReminder: null,
      checklist: [],
      links: [],
      users: [],
      labels: [],
    },
  })

  const {
    register,
    reset,
    setValue,
    watch,
    getValues,
    formState: { errors },
  } = methods

  useEffect(() => {
    if (isOpen) {
      loadUsers()
      loadLabels()
      if (taskId) {
        loadTask()
      } else {
        // Reset form for new task
        reset({
          name: '',
          description: '',
          entityId: entityId,
          startDate: null,
          dueDate: null,
          dueDateReminder: null,
          checklist: [],
          links: [],
          users: [],
          labels: [],
        })
        setChecklists([])
        setLinks([])
        setExistingFiles([])
        setSelectedUsers([])
        setSelectedLabels([])
        setTaskStatus('Pending')
      }
    }
  }, [isOpen, taskId, entityId])

  const loadUsers = async () => {
    try {
      const usersData = await fetchUsers()
      setAllUsers(usersData || [])
    } catch (error) {
      console.error('Error loading users:', error)
    }
  }

  const loadLabels = async () => {
    try {
      const labelsData = await fetchLabels()
      setAllLabels(labelsData || [])
    } catch (error) {
      console.error('Error loading labels:', error)
    }
  }

  const loadTask = async () => {
    if (!taskId) return

    setIsLoading(true)
    try {
      const response = await fetchTaskById(taskId)
      if (response) {
        console.log('task id response', response)
        const { task, labels: allLabelsData, users: allUsersData } = response

        // Set all available labels and users
        setAllLabels(allLabelsData || [])
        setAllUsers(allUsersData || [])

        // Set task data
        reset({
          name: task.name || '',
          description: task.description || '',
          entityId: task.entityId || entityId,
          startDate: task.startDate || null,
          dueDate: task.dueDate || null,
          dueDateReminder: task.dueDateReminder || null,
          checklist: task.checklist || [],
          links: task.links || [],
          users: task.users || [],
          labels: task.labels || [],
        })

        setChecklists(task.checklist || [])
        setLinks(task.links || [])
        setExistingFiles(task.files || [])

        // Extract user IDs if users is an array of objects
        const userIds = Array.isArray(task.users)
          ? task.users.map((u: any) => typeof u === 'string' ? u : u.id || u.userId)
          : []
        setSelectedUsers(userIds)

        // Extract label IDs if labels is an array of objects
        const labelIds = Array.isArray(task.labels)
          ? task.labels.map((l: any) => typeof l === 'string' ? l : l.id || l.labelId)
          : []
        setSelectedLabels(labelIds)

        // Set task status
        setTaskStatus(task.status || 'Pending')
      }
    } catch (error) {
      console.error('Error loading task:', error)
      showToast('Failed to load task')
    } finally {
      setIsLoading(false)
    }
  }

  // Auto-save function with debouncing
  const autoSave = async (overrides?: { users?: string[], labels?: string[], checklists?: any[], links?: any[], status?: string, startDate?: string | null, dueDate?: string | null, dueDateReminder?: DueDateReminder | null }) => {
    if (!taskId) return // Only auto-save for existing tasks

    const data = getValues()
    setIsSaving(true)

    try {
      const updatePayload: UpdateTaskPayload = {
        name: data.name,
        description: data.description,
        startDate: overrides?.startDate !== undefined ? overrides.startDate : data.startDate,
        dueDate: overrides?.dueDate !== undefined ? overrides.dueDate : data.dueDate,
        dueDateReminder: overrides?.dueDateReminder !== undefined ? overrides.dueDateReminder : data.dueDateReminder,
        checklist: overrides?.checklists ?? checklists,
        links: overrides?.links ?? links,
        users: overrides?.users ?? selectedUsers,
        labels: overrides?.labels ?? selectedLabels,
        status: overrides?.status ?? taskStatus,
      }

      const result = await updateTask(taskId, updatePayload)

      if (result) {
        onSave()
      }
    } catch (error) {
      console.error('Error auto-saving task:', error)
      showToast('Failed to save changes')
    } finally {
      setIsSaving(false)
    }
  }

  // Debounced auto-save
  const triggerAutoSave = (overrides?: { users?: string[], labels?: string[], checklists?: any[], links?: any[], status?: string, startDate?: string | null, dueDate?: string | null, dueDateReminder?: DueDateReminder | null }) => {
    if (!taskId) return // Only auto-save for existing tasks

    if (saveTimeout) {
      clearTimeout(saveTimeout)
    }

    const timeout = setTimeout(() => {
      autoSave(overrides)
    }, 1000) // 1 second debounce

    setSaveTimeout(timeout)
  }

  const handleStatusToggle = () => {
    const newStatus = taskStatus === 'Completed' ? 'Pending' : 'Completed'
    setTaskStatus(newStatus)
    triggerAutoSave({ status: newStatus })
  }

  useEffect(() => {
    return () => {
      if (saveTimeout) {
        clearTimeout(saveTimeout)
      }
    }
  }, [saveTimeout])


  const handleDelete = async () => {
    if (!taskId) return

    try {
      await deleteTask(taskId)
      showToast('Task deleted successfully')
      setIsDeleteModalOpen(false)
      onSave()
      onClose()
    } catch (error) {
      console.error('Error deleting task:', error)
      showToast('Failed to delete task')
      setIsDeleteModalOpen(false)
    }
  }

  const addLink = () => {
    // Don't allow adding a new link if already editing one
    if (editingLinkId) return

    const newLinkId = crypto.randomUUID()
    const newLinks = [...links, { id: newLinkId, url: '', title: '' }]
    setLinks(newLinks)
    // Auto-enter edit mode for new link
    setEditingLinkId(newLinkId)
    setEditingLinkTitle('')
    setEditingLinkUrl('')
  }

  const startEditLink = (link: { id: string; url: string; title?: string }) => {
    setEditingLinkId(link.id)
    setEditingLinkTitle(link.title || '')
    setEditingLinkUrl(link.url || '')
  }

  const saveLink = () => {
    if (!editingLinkId) return

    // If both title and url are empty, remove the link instead of saving
    if (!editingLinkTitle.trim() && !editingLinkUrl.trim()) {
      const updatedLinks = links.filter(link => link.id !== editingLinkId)
      setLinks(updatedLinks)
      triggerAutoSave({ links: updatedLinks })
      setEditingLinkId(null)
      setEditingLinkTitle('')
      setEditingLinkUrl('')
      return
    }

    // If title is empty but url exists, use url as title
    const finalTitle = editingLinkTitle.trim() || editingLinkUrl.trim()

    const updatedLinks = links.map(link =>
      link.id === editingLinkId
        ? { ...link, title: finalTitle, url: editingLinkUrl.trim() }
        : link
    )
    setLinks(updatedLinks)
    triggerAutoSave({ links: updatedLinks })
    setEditingLinkId(null)
    setEditingLinkTitle('')
    setEditingLinkUrl('')
  }

  const cancelEditLink = () => {
    // If canceling a new empty link, remove it
    if (editingLinkId) {
      const linkBeingEdited = links.find(l => l.id === editingLinkId)
      if (linkBeingEdited && !linkBeingEdited.url && !linkBeingEdited.title) {
        const updatedLinks = links.filter(link => link.id !== editingLinkId)
        setLinks(updatedLinks)
        triggerAutoSave({ links: updatedLinks })
        setEditingLinkId(null)
        setEditingLinkTitle('')
        setEditingLinkUrl('')
        return
      }
    }

    setEditingLinkId(null)
    setEditingLinkTitle('')
    setEditingLinkUrl('')
  }

  const removeLink = (id: string) => {
    const updatedLinks = links.filter(link => link.id !== id)
    setLinks(updatedLinks)
    triggerAutoSave({ links: updatedLinks })
  }

  if (isLoading) {
    return (
      <TaskBigModal open={isOpen} onClose={onClose}>
        <div className="flex items-center justify-center h-96">
          <LoadingSpinner />
        </div>
      </TaskBigModal>
    )
  }

  return (
    <TaskBigModal
      open={isOpen}
      onClose={onClose}
      headerActions={
        taskId ? (
          <button
            type="button"
            onClick={() => setIsDeleteModalOpen(true)}
            className="p-2 text-gray-400 hover:text-red-600 transition-colors rounded-lg hover:bg-white/50 cursor-pointer"
            title="Delete task"
          >
            <TrashIcon className="w-5 h-5" />
          </button>
        ) : null
      }
    >
      <FormProvider {...methods}>
        <div className="px-8 pb-8">

          <div className="grid grid-cols-8 gap-8">
            {/* Left Column - Main Content */}
            <div className="col-span-5 space-y-6">
              {/* Task Name with Checkbox */}
              <div className="flex items-center gap-3">
                <input
                  type="checkbox"
                  checked={taskStatus === 'Completed'}
                  onChange={handleStatusToggle}
                  className="w-4 h-4 rounded border-gray-300 focus:ring-[#6A1693] cursor-pointer flex-shrink-0 accent-[#6A1693]"
                />
                <div className="flex">
                  <textarea
                    className="font-bold text-xl w-full resize-none overflow-hidden"
                    placeholder="Enter task name..."
                    {...register('name', {
                      required: 'Task name is required',
                      onChange: triggerAutoSave
                    })}
                    rows={1}
                    onInput={(e) => {
                      const target = e.target as HTMLTextAreaElement
                      target.style.height = 'auto'
                      target.style.height = `${target.scrollHeight}px`
                    }}
                  />
                  {errors.name && <p className="text-red-600 text-xs mt-1">{errors.name.message}</p>}
                </div>
              </div>

              {/* Description */}
              <div>
                <label className="block text-sm font-semibold text-gray-700 mb-2">Description</label>
                <textarea
                  className={`${inputTextarea} resize-none overflow-hidden`}
                  rows={3}
                  placeholder="Add a more detailed description..."
                  {...register('description', {
                    onChange: triggerAutoSave
                  })}
                  onInput={(e) => {
                    const target = e.target as HTMLTextAreaElement
                    target.style.height = 'auto'
                    target.style.height = `${target.scrollHeight}px`
                  }}
                />
              </div>

              {/* Files */}


              {/* Checklist, Dates, Links, Users, and Labels */}
              <div>
                <div className="flex gap-2 mb-3 flex-wrap">
                  <UserAssignmentManager
                    allUsers={allUsers}
                    selectedUsers={selectedUsers}
                    onSelectionChange={(users) => {
                      setSelectedUsers(users)
                      triggerAutoSave({ users })
                    }}
                    onlyButton
                  />
                  <LabelManager
                    labels={allLabels}
                    selectedLabelIds={selectedLabels}
                    onLabelsChange={setAllLabels}
                    onSelectionChange={(labels) => {
                      setSelectedLabels(labels)
                      triggerAutoSave({ labels })
                    }}
                    onSaveLabel={saveLabelAPI}
                    onDeleteLabel={deleteLabelAPI}
                    onlyButton
                  />
                  <ChecklistManager
                    checklists={checklists}
                    onChange={(newChecklists) => {
                      setChecklists(newChecklists)
                      triggerAutoSave({ checklists: newChecklists })
                    }}
                    onlyButton
                  />
                  <DateReminderManager
                    startDate={watch('startDate')}
                    dueDate={watch('dueDate')}
                    dueDateReminder={watch('dueDateReminder')}
                    onStartDateChange={date => {
                      setValue('startDate', date)
                      triggerAutoSave({ startDate: date })
                    }}
                    onDueDateChange={date => {
                      setValue('dueDate', date)
                      triggerAutoSave({ dueDate: date })
                    }}
                    onReminderChange={reminder => {
                      setValue('dueDateReminder', reminder)
                      triggerAutoSave({ dueDateReminder: reminder })
                    }}
                    onlyButton
                  />

                  <button
                    type="button"
                    onClick={addLink}
                    className='font-bold text-sm flex gap-2 p-2 px-4 rounded-[16px] items-center cursor-pointer hover:shadow transition-[300ms] text-gray-600'
                  >
                    <PlusIcon className="w-4 h-4" />
                    Link
                  </button>
                  <button
                    type="button"
                    onClick={() => setShowFiles(!showFiles)}
                    className='font-bold text-sm flex gap-2 p-2 px-4 rounded-[16px] items-center cursor-pointer hover:shadow transition-[300ms] text-gray-600'
                  >
                    <PaperClipIcon className="w-4 h-4" />
                    {showFiles ? 'Hide Upload' : 'Add Files'}
                  </button>
                </div>
                              {taskId && (
                <div>
                  <TaskFileManager
                    taskId={taskId}
                    existingFiles={existingFiles}
                    onFilesChanged={() => {
                      loadTask()
                      onSave()
                    }}
                    showUploadArea={showFiles}
                  />
                </div>
              )}
                {/* Display Content */}
                <div className="space-y-3">
                  <ChecklistManager
                    checklists={checklists}
                    onChange={(newChecklists) => {
                      setChecklists(newChecklists)
                      triggerAutoSave({ checklists: newChecklists })
                    }}
                    onlyDisplay
                  />



                  {/* Links Display */}
                  {links.length > 0 && (
                    <div className="space-y-2 mt-2">
                      {links.map(link => (
                        <div key={link.id}>
                          {editingLinkId === link.id ? (
                            <div
                              className="flex items-center gap-2"
                              onBlur={(e) => {
                                // Only save if focus is leaving the entire container
                                if (!e.currentTarget.contains(e.relatedTarget as Node)) {
                                  saveLink()
                                }
                              }}
                            >
                              <input
                                type="text"
                                value={editingLinkTitle}
                                onChange={e => setEditingLinkTitle(e.target.value)}
                                onKeyDown={e => {
                                  if (e.key === 'Enter') saveLink()
                                  if (e.key === 'Escape') cancelEditLink()
                                }}
                                placeholder="Link title..."
                                className="w-1/3 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-[#6A1693]"
                                autoFocus
                              />
                              <input
                                type="url"
                                value={editingLinkUrl}
                                onChange={e => setEditingLinkUrl(e.target.value)}
                                onKeyDown={e => {
                                  if (e.key === 'Enter') saveLink()
                                  if (e.key === 'Escape') cancelEditLink()
                                }}
                                placeholder="https://..."
                                className="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-[#6A1693]"
                              />
                              <button
                                type="button"
                                onClick={() => removeLink(link.id)}
                                className="text-red-600 hover:text-red-700"
                              >
                                <XMarkIcon className="w-4 h-4" />
                              </button>
                            </div>
                          ) : (
                            <div className="flex items-center gap-2 group">
                              <a
                                href={link.url || '#'}
                                target="_blank"
                                rel="noopener noreferrer"
                                className="flex items-center gap-2 px-3 py-2 hover:bg-gray-50 transition-[300ms] rounded-lg flex-1"
                              >
                                <PaperClipIcon className="w-4 h-4 text-gray-400" />
                                <span className="text-sm font-medium text-[#6A1693]">
                                  {link.title || link.url || 'Untitled Link'}
                                </span>
                              </a>
                              <button
                                type="button"
                                onClick={() => startEditLink(link)}
                                className="text-gray-400 hover:text-[#6A1693] opacity-0 group-hover:opacity-100 transition-opacity"
                                title="Edit link"
                              >
                                <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                              </button>
                              <button
                                type="button"
                                onClick={() => removeLink(link.id)}
                                className="text-gray-400 hover:text-red-600 opacity-0 group-hover:opacity-100 transition-opacity"
                              >
                                <XMarkIcon className="w-4 h-4" />
                              </button>
                            </div>
                          )}
                        </div>
                      ))}
                    </div>
                  )}
                </div>
              </div>

              {/* Action Buttons */}
              <div className="pt-4 flex gap-3 justify-end items-center">
                {isSaving && (
                  <span className="text-sm text-gray-500 flex items-center gap-2">
                    <LoadingSpinner />
                    Saving...
                  </span>
                )}

              </div>
            </div>

            {/* Right Column - Internal Conversation */}
            <div className="col-span-3">
              <div className="sticky top-0">
                <h3 className="text-sm font-semibold text-gray-900 mb-4">Comments and activity</h3>
                {taskId && (
                  <Conversation
                    viewName="tasks"
                    entityId={taskId}
                  />
                )}
              </div>
            </div>
          </div>
        </div>
      </FormProvider>

      {/* Delete Confirmation Modal */}
      <Modal
        open={isDeleteModalOpen}
        onClose={() => setIsDeleteModalOpen(false)}
        size="sm"
        header={<h2 className="text-lg font-semibold">Delete Task</h2>}
      >
        <div className="flex flex-col items-center text-center text-sm text-gray-700 my-7 font-bold">
          <ExclamationTriangleIcon className="h-20 w-20 text-[#CC0000] mb-3" />
          Are you sure you want to delete this task? This action cannot be undone.
        </div>

        <div className="mt-6 flex justify-end gap-3">
          <button
            type="button"
            className={btnSecondary}
            onClick={() => setIsDeleteModalOpen(false)}
          >
            Cancel
          </button>
          <button
            type="button"
            className={btnDanger}
            onClick={handleDelete}
          >
            Delete
          </button>
        </div>
      </Modal>
    </TaskBigModal>
  )
}
