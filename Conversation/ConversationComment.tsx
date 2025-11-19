'use client'
import React, { useEffect, useMemo, useState } from 'react'
import { formatDateAndTime, timeSince } from '../../functions/dates'
import { Calendar, EllipsisVerticalIcon, ThumbsUpIcon, TrashIcon, XIcon, CheckIcon } from 'lucide-react'
import { DocumentTextIcon, ExclamationTriangleIcon } from '@heroicons/react/24/outline'
import { Menu, MenuButton, MenuItem, MenuItems } from '@headlessui/react'
import { btnDanger, btnSecondary } from '../../styles/Buttons'
import Modal from '../Modal'
import { showToast } from '../../functions/toast'
import { deleteConversationComment } from '../../api/conversations'
import { capitalizeFirstUnicode, extractKeywords } from '../../functions/text-analysis'
import { RenderedMentions } from './RenderedMentions'

type CommentFile = {
  fileId: string
  id?: string
  name?: string
  url: string
  type?: string // e.g. "image/png", "application/pdf"
  size?: number // in bytes (opcional)
}

export default function ConversationComment({
  comment,
  setComments,
  approvalMode = false,
  onApprove,
  onReject,
  onPending,
}: {
  comment: any
  setComments: (comments: any) => void
  approvalMode?: boolean
  onApprove?: (commentId: string) => Promise<void>
  onReject?: (commentId: string) => Promise<void>
  onPending?: (commentId: string) => Promise<void>
}) {
  const [isDeleteModalOpen, setIsDeleteModalOpen] = useState<boolean>(false)
  const [isDeleting, setIsDeleting] = useState<boolean>(false)
  const [isApproving, setIsApproving] = useState<boolean>(false)
  const [isRejecting, setIsRejecting] = useState<boolean>(false)
  const [isUpdating, setIsUpdating] = useState<boolean>(false)

  const keywords = useMemo(() => extractKeywords(comment.content), [comment.content])

  const formatBytes = (bytes?: number) => {
    if (!bytes && bytes !== 0) return ''
    if (bytes === 0) return '0 B'
    const k = 1024
    const sizes = ['B', 'KB', 'MB', 'GB', 'TB']
    const i = Math.floor(Math.log(bytes) / Math.log(k))
    return `${parseFloat((bytes / Math.pow(k, i)).toFixed(2))} ${sizes[i]}`
  }

  const isImage = (mime?: string, url?: string) =>
    (mime && mime.startsWith('image/')) || (!!url && /\.(png|jpe?g|gif|webp|bmp|svg)$/i.test(url))


  const normalizedFiles: CommentFile[] = useMemo(() => {
    if (Array.isArray(comment?.files) && comment.files.length > 0) {
      return comment.files as CommentFile[]
    }
    if (comment?.fileId || comment?.url) {
      return [
        {
          id: comment.fileId,
          name: comment.name,
          url: comment.url,
          type: comment.type,
          size: comment.size,
          fileId: comment.fileId
        },
      ]
    }
    return []
  }, [comment])

  const handleDelete = async () => {
    setIsDeleting(true)
    try {
      const apiResponse = await deleteConversationComment(comment.id)
      if (apiResponse) {
        setComments((prev: any) => prev.filter((c: any) => c.id !== comment.id))
        showToast('Comment deleted successfully.')
      } else {
        throw new Error('Delete failed.')
      }
    } catch (err) {
      console.error(err)
      showToast('Delete failed.')
    } finally {
      setIsDeleting(false)
      setIsDeleteModalOpen(false)
    }
  }

  const handleApprove = async () => {
    if (!onApprove) return
    setIsApproving(true)
    try {
      await onApprove(comment.id)
      setComments((prev: any) =>
        prev.map((c: any) => (c.id === comment.id ? { ...c, status: 'Approved' } : c))
      )
      showToast('Document approved successfully.')
    } catch (err) {
      console.error(err)
      showToast('Failed to approve document.')
    } finally {
      setIsApproving(false)
    }
  }

  const handleReject = async () => {
    if (!onReject) return
    setIsRejecting(true)
    try {
      await onReject(comment.id)
      setComments((prev: any) =>
        prev.map((c: any) => (c.id === comment.id ? { ...c, status: 'Rejected' } : c))
      )
      showToast('Document rejected.')
    } catch (err) {
      console.error(err)
      showToast('Failed to reject document.')
    } finally {
      setIsRejecting(false)
    }
  }

  const handlePending = async () => {
    if (!onPending) return
    setIsUpdating(true)
    try {
      await onPending(comment.id)
      setComments((prev: any) =>
        prev.map((c: any) => (c.id === comment.id ? { ...c, status: 'Pending' } : c))
      )
      showToast('Status changed to Pending.')
    } catch (err) {
      console.error(err)
      showToast('Failed to change status.')
    } finally {
      setIsUpdating(false)
    }
  }

  const handleStatusChange = async (newStatus: 'Pending' | 'Approved' | 'Rejected') => {
    if (newStatus === comment.status) return

    if (newStatus === 'Approved') {
      await handleApprove()
    } else if (newStatus === 'Rejected') {
      await handleReject()
    } else if (newStatus === 'Pending') {
      await handlePending()
    }
  }

  return (
    <>
        <div className="w-full flex justify-between p-4 py-6">
          <div className="flex">
            <div className="text-xs">
              {!comment.authorAvatarUrl ? (
                <span className="w-5 h-5 rounded-full mr-2 bg-gray-200 flex items-center justify-center text-xs font-bold">
                  {(comment?.authorName ?? 'N').charAt(0)}
                </span>
              ) : (
                <>
                  <img
                    src={comment.authorAvatarUrl}
                    alt={comment.authorName + ' avatar'}
                    className="w-5 h-5 rounded-full mr-2"
                    width={10}
                    height={10}
                    onError={(e) => (e.currentTarget.style.display = 'none')}
                  />
                </>
              )}
            </div>

            <div className="max-w-[600px]">
              <div className="flex text-sm items-center gap-1">
                <div className="font-bold">{comment?.authorName ?? 'No name'}</div>
                <div className="text-sm text-gray-400">•</div>
                <div className="text-xs text-gray-500">{timeSince(comment.createdAt)}</div>
              </div>

              <div className="text-sm text-gray-700 mb-1 mt-4 whitespace-pre-line">
                <RenderedMentions
                  text={comment.content}
                />
              </div>

              {/* ====== FILES / ATTACHMENTS ====== */}
              {normalizedFiles.length > 0 && (
                <div className="mt-4">

                  {/* Grid de archivos */}
                  <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    {normalizedFiles.map((f, idx) => {
                      const key = f.id ?? `${f.url}-${idx}`
                      const showImage = isImage(f.type, f.name)
                      console.log('f',f)
                      console.log('f.type',f.type)
                      console.log('f.url',f.url)
                      console.log('show image',showImage)
                      return (
                        <a
                          key={key}
                          href={f.fileId}
                          target="_blank"
                          rel="noopener noreferrer"
                          className="group flex items-center gap-3 rounded-lg border border-gray-200 p-3 hover:bg-gray-50 transition"
                          title={f.name ?? f.url}
                        >
                          <div className="shrink-0">
                            {showImage ? (
                              // eslint-disable-next-line @next/next/no-img-element
                              <img
                                src={f.fileId}
                                alt={f.name ?? 'attachment'}
                                className="h-12 w-12 rounded object-cover ring-1 ring-gray-200"
                                loading="lazy"
                              />
                            ) : (
                              <div className="h-12 w-12 rounded flex items-center justify-center ring-1 ring-gray-200">
                                <DocumentTextIcon className="h-6 w-6 text-gray-600" />
                              </div>
                            )}
                          </div>

                          <div className="min-w-0">
                            <div className="text-sm font-medium text-[#6A1693] truncate group-hover:underline">
                              {f.name ?? f.url.split('/').pop()}
                            </div>
                            <div className="text-xs text-gray-500">
                              {(f.type || '').split('/').pop() || 'file'}
                              {typeof f.size === 'number' && ` • ${formatBytes(f.size)}`}
                            </div>
                          </div>
                        </a>
                      )
                    })}
                  </div>
                </div>
              )}

              {/* ====== APPROVAL MODE ====== */}
              {approvalMode && normalizedFiles.length > 0 && (
                <div className="mt-4">
                  <Menu as="div" className="relative inline-block text-left">
                    <MenuButton
                      className={`
                        inline-flex items-center gap-2 px-3 py-1 pr-2 text-xs cursor-pointer rounded-full
                        focus:outline-none focus:ring-2 focus:ring-purple-500
                        disabled:opacity-50 disabled:cursor-not-allowed
                        ${comment.status === 'Pending' ? 'bg-white text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50' : ''}
                        ${comment.status === 'Approved' ? 'bg-green-600 text-white border-green-600' : ''}
                        ${comment.status === 'Rejected' ? 'bg-[#cc0000] text-white border-[#cc0000]' : ''}
                      `}
                      disabled={isApproving || isRejecting || isUpdating}
                    >
                      {comment.status === 'Pending' && <span className="w-2 h-2 rounded-full bg-gray-400"></span>}
                      {comment.status === 'Approved' && <span className="w-2 h-2 rounded-full bg-white"></span>}
                      {comment.status === 'Rejected' && <span className="w-2 h-2 rounded-full bg-white"></span>}
                      {comment.status || 'Pending'}
                      <svg className={`h-4 w-4 ${comment.status === 'Pending' ? 'text-gray-400' : 'text-white'}`} fill="currentColor" viewBox="0 0 20 20">
                        <path fillRule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clipRule="evenodd" />
                      </svg>
                    </MenuButton>
                    <MenuItems
                      transition
                      className="absolute left-0 z-10 mt-2 w-40 origin-top-left bg-white shadow-lg ring-1 ring-gray-300 ring-opacity-5 transition focus:outline-none data-[closed]:scale-95 data-[closed]:transform data-[closed]:opacity-0 data-[enter]:duration-100 data-[leave]:duration-75 data-[enter]:ease-out data-[leave]:ease-in rounded-none"
                    >
                      <div className="px-4 py-2 text-xs font-bold text-gray-500 border-b border-gray-200">
                        Mark as:
                      </div>
                      <div className="py-1">
                        <MenuItem>
                          <button
                            type="button"
                            className="group flex w-full items-center gap-2 px-4 py-2 text-sm text-gray-700 data-[focus]:bg-gray-100 data-[focus]:text-gray-900 cursor-pointer"
                            onClick={() => handleStatusChange('Pending')}
                            disabled={isApproving || isRejecting || isUpdating}
                          >
                            <span className="w-2 h-2 rounded-full bg-gray-400"></span>
                            Pending
                          </button>
                        </MenuItem>
                        <MenuItem>
                          <button
                            type="button"
                            className="group flex w-full items-center gap-2 px-4 py-2 text-sm text-gray-700 data-[focus]:bg-gray-100 data-[focus]:text-gray-900 cursor-pointer"
                            onClick={() => handleStatusChange('Approved')}
                            disabled={isApproving || isRejecting || isUpdating}
                          >
                            <span className="w-2 h-2 rounded-full bg-green-600"></span>
                            Approved
                          </button>
                        </MenuItem>
                        <MenuItem>
                          <button
                            type="button"
                            className="group flex w-full items-center gap-2 px-4 py-2 text-sm text-gray-700 data-[focus]:bg-gray-100 data-[focus]:text-gray-900 cursor-pointer"
                            onClick={() => handleStatusChange('Rejected')}
                            disabled={isApproving || isRejecting || isUpdating}
                          >
                            <span className="w-2 h-2 rounded-full bg-[#cc0000]"></span>
                            Rejected
                          </button>
                        </MenuItem>
                      </div>
                    </MenuItems>
                  </Menu>
                </div>
              )}

              <div className="flex gap-2 mt-8 mb-4 items-center hidden ">
                <div className="hidden text-xs font-bold">Core Concepts:</div>
                {keywords?.map((keyword: string, keywordIndex: number) => (
                  <div
                    key={keywordIndex}
                    className="text-xs/2 px-5 text-gray-500 border-1 border-gray-500 rounded-full py-1 whitespace-nowrap"
                  >
                    {capitalizeFirstUnicode(keyword)}
                  </div>
                ))}
              </div>

              <div className="mt-5 text-xs flex gap-3">
                <div className="flex gap-1 hidden">
                  <ThumbsUpIcon className="w-3 h-3" />
                  <div>Like</div>
                </div>

                <div className="text-xs text-gray-600 flex gap-1 items-center">
                  <Calendar className="w-3 h-3" />
                  <div className="mt-[2px]">
                    {new Date(comment.createdAt).toLocaleDateString('en-GB', {
                      year: 'numeric',
                      month: 'short',
                      day: 'numeric',
                      hour: '2-digit',
                      minute: '2-digit',
                      second: '2-digit'
                    })}
                  </div>
                </div>
              </div>
            </div>
          </div>

          {/* menú acciones */}
          <div className="">
            <div className="flex justify-end w-full">
              <Menu as="div" className="relative inline-block text-left">
                <div>
                  <MenuButton className="flex items-center rounded-full  text-gray-500 hover:text-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:ring-offset-gray-100 cursor-pointer">
                    <span className="sr-only">Open options</span>
                    <EllipsisVerticalIcon className="h-4 w-4" aria-hidden="true" />
                  </MenuButton>
                </div>

                <MenuItems
                  transition
                  className="absolute right-0 z-10 mt-2 origin-top-right bg-white shadow-lg ring-1 ring-gray-300 ring-opacity-5 transition focus:outline-none data-[closed]:scale-95 data-[closed]:transform data-[closed]:opacity-0 data-[enter]:duration-100 data-[leave]:duration-75 data-[enter]:ease-out data-[leave]:ease-in"
                >
                  <div className="text-sm">
                    <MenuItem>
                      <button
                        type="button"
                        className="group flex w-full items-center px-4 py-3 text-sm text-gray-700 data-[focus]:bg-gray-100 data-[focus]:text-gray-900 cursor-pointer"
                        onClick={() => setIsDeleteModalOpen(true)}
                      >
                        <TrashIcon className="mr-3 h-5 w-5 text-red group-hover:text-gray-500" aria-hidden="true" />
                        Delete
                      </button>
                    </MenuItem>
                  </div>
                </MenuItems>
              </Menu>
            </div>
          </div>
        </div>

      <Modal
        open={isDeleteModalOpen}
        onClose={() => setIsDeleteModalOpen(false)}
        size="sm"
        header={<h2 className="text-lg font-semibold">Confirm Deletion</h2>}
      >
        <div className="flex flex-col items-center text-center text-sm text-gray-700 my-7 font-bold">
          <ExclamationTriangleIcon className="h-20 w-20 text-[#CC0000] mb-3" />
          Are you sure you want to delete this Comment?
        </div>

        <div className="mt-6 flex justify-end gap-3">
          <button type="button" className={btnSecondary} onClick={() => setIsDeleteModalOpen(false)}>
            Cancel
          </button>
          <button type="button" disabled={isDeleting} className={btnDanger} onClick={handleDelete}>
            {isDeleting ? 'Deleting…' : 'Delete'}
          </button>
        </div>
      </Modal>
    </>
  )
}
