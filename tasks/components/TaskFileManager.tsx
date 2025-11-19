'use client'

import React, { useState, useCallback } from 'react'
import { useDropzone } from 'react-dropzone'
import {
  CloudArrowUpIcon,
  TrashIcon,
  DocumentTextIcon,
  XMarkIcon,
  PencilSquareIcon,
  CheckIcon,
} from '@heroicons/react/24/outline'
import { addFilesToTask, deleteFileFromTask, type TaskFile } from '@/app/lib/api/taskFiles'
import { updateFile } from '@/app/lib/api/files'
import { showToast } from '@/app/lib/functions/toast'
import LoadingSpinner from '@/app/lib/components/LoadingSpinner'
import { btnPrimaryFullWidthMedium, btnSecondaryMedium } from '../../styles/Buttons'

interface TaskFileManagerProps {
  taskId: string
  existingFiles: TaskFile[]
  onFilesChanged: () => void
  showUploadArea?: boolean
}

export default function TaskFileManager({
  taskId,
  existingFiles = [],
  onFilesChanged,
  showUploadArea = false,
}: TaskFileManagerProps) {
  const [pendingFiles, setPendingFiles] = useState<File[]>([])
  const [isUploading, setIsUploading] = useState(false)
  const [deletingFileId, setDeletingFileId] = useState<string | null>(null)
  const [editingFileId, setEditingFileId] = useState<string | null>(null)
  const [editingFileName, setEditingFileName] = useState('')

  const formatBytes = (bytes: number) => {
    if (bytes === 0) return '0 B'
    const k = 1024
    const sizes = ['B', 'KB', 'MB', 'GB', 'TB']
    const i = Math.floor(Math.log(bytes) / Math.log(k))
    return `${parseFloat((bytes / Math.pow(k, i)).toFixed(2))} ${sizes[i]}`
  }

  const handleUpload = async () => {
    if (pendingFiles.length === 0) return

    setIsUploading(true)
    try {
      const result = await addFilesToTask(taskId, pendingFiles)
      if (result) {
        showToast('Files uploaded successfully')
        setPendingFiles([])
        onFilesChanged()
      } else {
        showToast('Failed to upload files')
      }
    } catch (error) {
      console.error('Error uploading files:', error)
      showToast('Failed to upload files')
    } finally {
      setIsUploading(false)
    }
  }

  const handleDeleteExistingFile = async (fileId: string) => {
    setDeletingFileId(fileId)
    try {
      const success = await deleteFileFromTask(taskId, fileId)
      if (success) {
        showToast('File deleted successfully')
        onFilesChanged()
      } else {
        showToast('Failed to delete file')
      }
    } catch (error) {
      console.error('Error deleting file:', error)
      showToast('Failed to delete file')
    } finally {
      setDeletingFileId(null)
    }
  }

  const handleDeletePendingFile = (index: number) => {
    setPendingFiles(pendingFiles.filter((_, i) => i !== index))
  }

  const handleStartEdit = (file: TaskFile) => {
    setEditingFileId(file.id)
    setEditingFileName(file.name)
  }

  const handleSaveEdit = async () => {
    if (!editingFileId || !editingFileName.trim()) return

    try {
      const result = await updateFile(editingFileId, {
        name: editingFileName.trim(),
      })

      if (result) {
        showToast('File renamed successfully')
        setEditingFileId(null)
        setEditingFileName('')
        onFilesChanged()
      } else {
        showToast('Failed to rename file')
      }
    } catch (error) {
      console.error('Error renaming file:', error)
      showToast('Failed to rename file')
    }
  }

  const handleCancelEdit = () => {
    setEditingFileId(null)
    setEditingFileName('')
  }

  const onDrop = useCallback((acceptedFiles: File[]) => {
    if (!acceptedFiles?.length) return
    setPendingFiles((prev) => [...prev, ...acceptedFiles])
  }, [])

  const {
    getRootProps,
    getInputProps,
    isDragActive,
    open: openFileDialog,
  } = useDropzone({
    onDrop,
    multiple: true,
    noClick: true,
    disabled: !showUploadArea,
  })

  const totalFiles = existingFiles.length + pendingFiles.length
  const hasFiles = totalFiles > 0

  return (
    <div className="space-y-4">
      {/* Existing Files */}
      {existingFiles.length > 0 && (
        <div>
          <div className="space-y-2">
            {existingFiles.map((file) => {
              const isEditing = editingFileId === file.id
              return (
                <div
                  key={file.id}
                  className="flex items-center gap-3 p-3 rounded-lg border border-gray-200"
                >
                  <DocumentTextIcon className="h-5 w-5 text-gray-400 flex-shrink-0" />

                  {isEditing ? (
                    <>
                      <div className="flex-1 min-w-0">
                        <input
                          type="text"
                          value={editingFileName}
                          onChange={(e) => setEditingFileName(e.target.value)}
                          onKeyDown={(e) => {
                            if (e.key === 'Enter') handleSaveEdit()
                            if (e.key === 'Escape') handleCancelEdit()
                          }}
                          className="w-full px-2 py-1 text-sm border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-[#6A1693]"
                          autoFocus
                        />
                      </div>
                      <button
                        type="button"
                        onClick={handleSaveEdit}
                        className="p-1.5 text-green-600 hover:bg-green-50 rounded transition-colors"
                        aria-label="Save"
                      >
                        <CheckIcon className="h-4 w-4" />
                      </button>
                      <button
                        type="button"
                        onClick={handleCancelEdit}
                        className="p-1.5 text-gray-600 hover:bg-gray-50 rounded transition-colors"
                        aria-label="Cancel"
                      >
                        <XMarkIcon className="h-4 w-4" />
                      </button>
                    </>
                  ) : (
                    <>
                      <div className="flex-1 min-w-0">
                        <p className="text-sm font-medium text-gray-900 truncate">{file.name}</p>
                        {file.size && (
                          <p className="text-xs text-gray-500">{formatBytes(file.size)}</p>
                        )}
                      </div>
                      <button
                        type="button"
                        onClick={() => handleStartEdit(file)}
                        className="p-1.5 text-gray-600 hover:bg-gray-50 rounded transition-colors"
                        aria-label={`Edit ${file.name}`}
                      >
                        <PencilSquareIcon className="h-4 w-4" />
                      </button>
                      <button
                        type="button"
                        onClick={() => handleDeleteExistingFile(file.id)}
                        disabled={deletingFileId === file.id}
                        className="p-1.5 text-red-600 hover:bg-red-50 rounded transition-colors disabled:opacity-50"
                        aria-label={`Delete ${file.name}`}
                      >
                        {deletingFileId === file.id ? (
                          <LoadingSpinner />
                        ) : (
                          <TrashIcon className="h-4 w-4" />
                        )}
                      </button>
                    </>
                  )}
                </div>
              )
            })}
          </div>
        </div>
      )}

      {/* Upload Area - Only shown when showUploadArea is true */}
      {showUploadArea && (
        <>
          {/* Drop Zone */}
          <div
            {...getRootProps()}
            className={`
              flex flex-col items-center justify-center text-center p-6 rounded-lg border-2 border-dashed transition-colors
              ${isDragActive ? 'border-[#6A1693] bg-purple-50' : 'border-gray-300 bg-white'}
              cursor-pointer
            `}
            onClick={() => openFileDialog()}
          >
            <input {...getInputProps()} />
            <CloudArrowUpIcon className="h-8 w-8 text-[#6A1693] mb-2" />
            <p className="text-sm font-medium text-gray-700">
              {isDragActive ? 'Drop files here...' : 'Click or drag files to upload'}
            </p>
            <p className="text-xs text-gray-500 mt-1">
              {hasFiles ? `${totalFiles} file${totalFiles > 1 ? 's' : ''} total` : 'No files yet'}
            </p>
          </div>

          {/* Pending Files */}
          {pendingFiles.length > 0 && (
            <div>
              <h4 className="text-sm font-semibold text-gray-700 mb-2">Adding Files</h4>
              <div className="space-y-2">
                {pendingFiles.map((file, index) => (
                  <div
                    key={index}
                    className="flex items-center gap-3 p-3 rounded-lg border border-gray-300"
                  >
                    <DocumentTextIcon className="h-5 w-5 text-gray-400 flex-shrink-0" />
                    <div className="flex-1 min-w-0">
                      <p className="text-sm font-medium text-gray-900 truncate">{file.name}</p>
                      <p className="text-xs text-gray-500">{formatBytes(file.size)}</p>
                    </div>
                    <button
                      type="button"
                      onClick={() => handleDeletePendingFile(index)}
                      className="p-1.5 text-red-600 hover:bg-red-50 rounded transition-colors"
                      aria-label={`Remove ${file.name}`}
                    >
                      <XMarkIcon className="h-4 w-4" />
                    </button>
                  </div>
                ))}
              </div>
            </div>
          )}

          {/* Upload Button */}
          {pendingFiles.length > 0 && (
            <div className="flex gap-2">
              <button
                type="button"
                onClick={handleUpload}
                disabled={isUploading}
                className={btnPrimaryFullWidthMedium}
              >
                {isUploading ? (
                  <>
                    <LoadingSpinner />
                    Uploading...
                  </>
                ) : (
                  <>
                    <CloudArrowUpIcon className="h-4 w-4" />
                    Upload {pendingFiles.length} file{pendingFiles.length > 1 ? 's' : ''}
                  </>
                )}
              </button>
              <button
                type="button"
                onClick={() => setPendingFiles([])}
                disabled={isUploading}
                className={btnSecondaryMedium}
              >
                Clear
              </button>
            </div>
          )}
        </>
      )}
    </div>
  )
}
