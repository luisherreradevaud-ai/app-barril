'use client'

import React, { useCallback, useEffect, useRef, useState } from 'react'
import Image from 'next/image'
import { useDropzone } from 'react-dropzone'
import { UserCircleIcon } from 'lucide-react'
import {
  CloudArrowUpIcon,
  TrashIcon,
  DocumentTextIcon,
  XMarkIcon,
  CheckIcon,
} from '@heroicons/react/24/outline'

import { showToast } from '../../functions/toast'
import { postComment } from '../../api/conversations'
import { btnPrimary, btnActionSm, btnActionWhiteSm } from '../../styles/Buttons'
import { MentionInput } from './MentionInput'

// ───────────────────────────────────────────────────────────────────────────────
// Types
// ───────────────────────────────────────────────────────────────────────────────
export type UploadableFile = {
  id: string
  file: File
  previewUrl?: string // only for images
  createdAt: number
  displayName?: string // editable label shown in the table
}

// ───────────────────────────────────────────────────────────────────────────────
// Single component
// ───────────────────────────────────────────────────────────────────────────────
export default function ConversationPostBoxWithUploads({
  conversation,
  loadData,
  users,
  entityId,
  viewName,
  customPostHandler
}: {
  conversation: any
  loadData: () => void
  users: any[]
  entityId: string
  viewName: string
  customPostHandler?: (files: any[], content: string, taggedUsers: any[]) => Promise<void>
}) {
  // Post box state
  const [metaDescription, setMetaDescription] = useState('')
  const [userData, setUserData] = useState<any | null>(null)
  const [isPosting, setIsPosting] = useState<boolean>(false)
  const [uploading, setUploading] = useState(false)
  const [taggedUsers, setTaggedUsers] = useState<any[]>([])

  // Ref for outside-click detection
  const boxRef = useRef<HTMLDivElement | null>(null)

  // Upload table state
  const [documents, setDocuments] = useState<UploadableFile[]>([])
  const [editingId, setEditingId] = useState<string | null>(null)
  const [tempName, setTempName] = useState<string>('')

  // ── Helpers (upload table)
  const formatBytes = (bytes: number) => {
    if (bytes === 0) return '0 B'
    const k = 1024
    const sizes = ['B', 'KB', 'MB', 'GB', 'TB']
    const i = Math.floor(Math.log(bytes) / Math.log(k))
    return `${parseFloat((bytes / Math.pow(k, i)).toFixed(2))} ${sizes[i]}`
  }

  const addFiles = (files: File[]) => {
    const mapped: UploadableFile[] = files.map((file) => ({
      id: crypto.randomUUID(),
      file,
      previewUrl: file.type.startsWith('image/') ? URL.createObjectURL(file) : undefined,
      createdAt: Date.now(),
      displayName: file.name,
    }))
    setDocuments((prev) => [...prev, ...mapped])
  }

  const removeItem = (id: string) => {
    const item = documents.find((d) => d.id === id)
    if (item?.previewUrl) URL.revokeObjectURL(item.previewUrl)
    setDocuments((prev) => prev.filter((d) => d.id !== id))
    if (editingId === id) {
      setEditingId(null)
      setTempName('')
    }
  }

  const clearAll = () => {
    documents.forEach((d) => d.previewUrl && URL.revokeObjectURL(d.previewUrl))
    setDocuments([])
    setEditingId(null)
    setTempName('')
  }

  const startEdit = (id: string) => {
    const current = documents.find((d) => d.id === id)
    setEditingId(id)
    setTempName(current?.displayName ?? current?.file.name ?? '')
  }

  const saveEdit = () => {
    if (!editingId) return
    setDocuments((prev) =>
      prev.map((d) =>
        d.id === editingId
          ? { ...d, displayName: (tempName || d.file.name).trim() || d.file.name }
          : d
      )
    )
    setEditingId(null)
    setTempName('')
  }

  const cancelEdit = () => {
    setEditingId(null)
    setTempName('')
  }

  const onDrop = useCallback(
    (acceptedFiles: File[]) => {
      if (!acceptedFiles?.length) return
      addFiles(acceptedFiles)
    },
    [] // no need to depend on documents
  )

  const { getRootProps, getInputProps, isDragActive, open: openFileDialog, isFileDialogActive } =
    useDropzone({
      onDrop,
      multiple: true,
      noClick: true,
    })

  const totalSize = documents.reduce((sum, d) => sum + d.file.size, 0)

  useEffect(() => {
    if (typeof window === 'undefined') return
    const raw = localStorage.getItem('userData')
    if (raw) {
      try {
        const parsed = JSON.parse(raw)
        if (parsed && parsed.avatarUrl !== undefined) setUserData(parsed)
      } catch (e) {
        console.warn(e)
      }
    }
  }, [])

  useEffect(() => {
    return () => {
      documents.forEach((d) => d.previewUrl && URL.revokeObjectURL(d.previewUrl))
    }
  }, [documents])

  // Open compose box when clicked inside
  const handlePostDivClick = () => setIsPosting(true)

  // Close compose when clicking outside (and on Escape)
  useEffect(() => {
    if (!isPosting) return

    const handleOutside = (e: MouseEvent | TouchEvent) => {
      const el = boxRef.current
      if (el && !el.contains(e.target as Node)) {
        setIsPosting(false)
      }
    }

    const handleEsc = (e: KeyboardEvent) => {
      if (e.key === 'Escape') setIsPosting(false)
    }

    // capture phase helps even if children stop propagation
    document.addEventListener('mousedown', handleOutside, true)
    document.addEventListener('touchstart', handleOutside, true)
    document.addEventListener('keydown', handleEsc, true)

    return () => {
      document.removeEventListener('mousedown', handleOutside, true)
      document.removeEventListener('touchstart', handleOutside, true)
      document.removeEventListener('keydown', handleEsc, true)
    }
  }, [isPosting])

  const handleUpload = async () => {
    const normalizedTaggedUsers = taggedUsers.map((u) => u.id)

    if (metaDescription.trim() === '') return

    setUploading(true)

    // If custom handler is provided, use it (for workflow documents)
    if (customPostHandler) {
      try {
        const files = documents
        await customPostHandler(files, metaDescription, normalizedTaggedUsers)

        setUploading(false)
        setIsPosting(false)
        setMetaDescription('')
        clearAll()
        loadData()
      } catch (err) {
        setUploading(false)
        showToast('Unexpected error while posting.')
        console.error(err)
      }
      return
    }

    let viewNameUrl = 'https://vendorsify.com/home'
    if (typeof window !== 'undefined') {
      viewNameUrl = window.location.href
    }

    // Convert plain @Name mentions to @Name{id} format for backend storage
    let contentWithIds = metaDescription
    taggedUsers.forEach((user) => {
      // Replace @Name (not already followed by {id}) with @Name{id}
      const regex = new RegExp(`@${user.fullName}(?!\\{)`, 'g')
      contentWithIds = contentWithIds.replace(regex, `@${user.fullName}{${user.id}}`)
    })

    try {
      const files = documents
      const apiResponse = await postComment(
        conversation.conversation.id,
        contentWithIds,
        files,
        normalizedTaggedUsers,
        viewNameUrl
      )

      setUploading(false)
      setIsPosting(false)
      setMetaDescription('')
      clearAll()

      if (apiResponse) {
        showToast('Post successful.')
        loadData()
      } else {
        showToast('Error adding the document.')
        console.error(apiResponse)
      }
    } catch (err) {
      setUploading(false)
      showToast('Unexpected error while posting.')
      console.error(err)
    }
  }

  return (
    <div className="w-full">
      <div
        ref={boxRef}
        className={
          'flex w-full gap-3 items-start border-1 border-gray-300 rounded-[16px] p-4 cursor-text shadow transition-[2000ms] ' +
          (isPosting ? ' shadow-lg -translate-y-2' : '')
        }
        onClick={handlePostDivClick}
      >
        {/* Left avatar (collapsed) */}
        {!isPosting && (
          <div className="mt-1 shrink-0">
            {userData?.avatarUrl ? (
              <div className="relative rounded-full overflow-hidden w-16 h-16">
                {/* eslint-disable-next-line @next/next/no-img-element */}
                <img
                  src={userData.avatarUrl}
                  alt={userData.fullName ?? 'User avatar'}
                  style={{ objectFit: 'cover' }}
                />
              </div>
            ) : (
              <UserCircleIcon className="w-8 h-8 text-[#6A1693]" />
            )}
          </div>
        )}

        {/* Main content */}
        <div className="w-full">
          {/* Header cuando está posteando */}
          {userData?.fullName && isPosting && (
            <div className="text-gray-500 text-sm mb-3 flex items-center justify-start gap-2">
              <div>
                {userData?.avatarUrl ? (
                  <div className="relative rounded-full overflow-hidden w-7 h-7">
                    <Image
                      src={userData.avatarUrl}
                      alt={userData.fullName ?? 'User avatar'}
                      fill
                      style={{ objectFit: 'cover' }}
                    />
                  </div>
                ) : (
                  <UserCircleIcon className="w-8 h-8 text-[#6A1693]" />
                )}
              </div>
              <div>
                <span className="font-gray-900 font-bold">{userData.fullName}</span> says...
              </div>
            </div>
          )}

          {/* Mention Input */}
          <MentionInput
            users={users}
            value={metaDescription}
            onChange={setMetaDescription}
            onTagsChange={setTaggedUsers}
            placeholder="Write something..."
            className={
              'w-full rounded-[16px] gap-[10px] p-[10px] mt-1 block text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-[#6A1693]/30' +
              (isPosting ? ' border-1 border-gray-300' : '')
            }
            rows={2}
          />

          {/* Drag & Drop multi-file uploader (inline, no componente hijo) */}
          {isPosting && (
            <div className="mt-3">
              {/* ======= TABLE (only when files exist) ======= */}
              {documents.length > 0 && (
                <div className="overflow-x-auto rounded-t-xl border-b-0">
                  <table className="min-w-full bg-white">
                    <tbody className="divide-y divide-gray-200">
                      {documents.map((item) => {
                        const isImage = item.file.type.startsWith('image/')
                        const isEditing = editingId === item.id
                        return (
                          <tr key={item.id} className={'odd:bg-white even:bg-[#F9F9F9]'}>
                            <td className="px-3 py-3">
                              {isImage ? (
                                // eslint-disable-next-line @next/next/no-img-element
                                <img
                                  src={item.previewUrl}
                                  alt={item.file.name}
                                  className="h-10 w-10 rounded object-cover ring-1 ring-gray-200"
                                />
                              ) : (
                                <div className="h-10 w-10 rounded flex items-center justify-center ring-1 ring-gray-200">
                                  <DocumentTextIcon className="h-6 w-6 text-gray-600" />
                                </div>
                              )}
                            </td>

                            {/* Editable name cell */}
                            <td className="px-3 py-3 text-sm font-medium text-gray-900">
                              {isEditing ? (
                                <div className="flex items-center gap-2">
                                  <input
                                    autoFocus
                                    value={tempName}
                                    onChange={(e) => setTempName(e.target.value)}
                                    onKeyDown={(e) => {
                                      if (e.key === 'Enter') saveEdit()
                                      if (e.key === 'Escape') cancelEdit()
                                    }}
                                    className="w-full max-w-xs rounded-lg border border-gray-300 px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                  />
                                  <button
                                    type="button"
                                    onClick={saveEdit}
                                    className={btnActionSm}
                                    aria-label="Save name"
                                  >
                                    <CheckIcon className="h-4 w-4" />
                                  </button>
                                  <button
                                    type="button"
                                    onClick={cancelEdit}
                                    className={btnActionSm}
                                    aria-label="Cancel editing"
                                  >
                                    <XMarkIcon className="h-4 w-4" />
                                  </button>
                                </div>
                              ) : (
                                <div
                                  className="flex items-center gap-2"
                                  onClick={() => startEdit(item.id)}
                                >
                                  <span
                                    className="truncate max-w-xs"
                                    title={item.displayName || item.file.name}
                                  >
                                    {item.displayName || item.file.name}
                                  </span>
                                </div>
                              )}
                            </td>
                            <td className="px-3 py-3 text-sm text-gray-700">
                              {formatBytes(item.file.size)}
                            </td>
                            <td className="px-3 py-3">
                              <div className="flex items-center justify-end gap-2">
                                <button
                                  type="button"
                                  onClick={() => removeItem(item.id)}
                                  className={btnActionWhiteSm}
                                  aria-label={`Remove ${item.file.name}`}
                                >
                                  <TrashIcon className="h-4 w-4" />
                                </button>
                              </div>
                            </td>
                          </tr>
                        )
                      })}
                    </tbody>
                  </table>
                </div>
              )}

              {/* ======= WHEN THERE ARE FILES: compact top drop area ======= */}
              {documents.length > 0 && (
                <div
                  {...getRootProps()}
                  className={`
                    text-[#6A1693] flex w-full p-4 justify-between sm:justify-center items-center gap-2 rounded-b-xl 
                    ${isDragActive ? 'border-indigo-600 bg-indigo-50' : 'border-gray-200 bg-white border-t-0'}
                    cursor-pointer transition mb-4
                  `}
                  onClick={() => openFileDialog()}
                  role="button"
                  aria-label="Upload"
                >
                  <input {...getInputProps()} />
                  <div className="flex items-center gap-2">
                    <CloudArrowUpIcon className="h-6 w-6 text-[#6A1693]" />
                    <span className="text-sm">
                      {isFileDialogActive
                        ? 'Uploading...'
                        : isDragActive
                        ? 'Drop files here…'
                        : 'Upload'}
                    </span>
                  </div>
                  <div className="ml-auto text-xs text-gray-500 hidden sm:block">
                    {`${documents.length} file${documents.length > 1 ? 's' : ''} • ${formatBytes(totalSize)}`}
                  </div>
                </div>
              )}

              {/* ======= WHEN THERE ARE NO FILES: empty-state is the dropzone ======= */}
              {documents.length === 0 && (
                <div
                  {...getRootProps()}
                  className={`
                    flex flex-col items-center justify-center text-center px-10 py-5 rounded-xl border-1 border-gray-400 border-dashed
                    ${isDragActive ? 'border-indigo-600 bg-indigo-50' : 'border-gray-200 bg-white'}
                    cursor-pointer transition
                  `}
                  onClick={() => openFileDialog()}
                  role="button"
                  aria-label="Upload"
                >
                  <input {...getInputProps()} />

                  <p className="text-sm text-gray-400">
                    {isFileDialogActive
                      ? 'Uploading...'
                      : isDragActive
                      ? 'Drop files here…'
                      : 'Drag & drop files or click to select.'}
                  </p>

                  <div className="mt-4 inline-flex items-center gap-2 text-[#6A1693]">
                    <CloudArrowUpIcon className="h-5 w-5" />
                    <span className="text-sm font-medium">Upload</span>
                  </div>
                </div>
              )}

            </div>
          )}
        </div>

        {/* Right action button */}
        {isPosting && (
          <button
            type="button"
            onClick={handleUpload}
            className={btnPrimary}
            disabled={uploading || metaDescription.trim() === ''}
            title={metaDescription.trim() === '' ? 'Write something to enable posting' : 'Post'}
          >
            {uploading ? 'Posting…' : 'Post'}
          </button>
        )}
      </div>
    </div>
  )
}
