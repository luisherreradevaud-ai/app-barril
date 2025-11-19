'use client'

import React, { useState } from 'react'
import { PlusIcon, XMarkIcon, TrashIcon, CheckCircleIcon } from '@heroicons/react/24/outline'
import Modal from '@/app/lib/components/Modal'
import { btnDanger, btnSecondary, btnAction, btnPrimaryFullWidthMedium, btnSecondaryFullWidthMedium, btnActionSm, btnSecondaryMedium } from '@/app/lib/styles/Buttons'

export type ChecklistItem = {
  id: string
  text: string
  completed: boolean
}

export type Checklist = {
  id: string
  title: string
  items: ChecklistItem[]
}

interface ChecklistManagerProps {
  checklists: Checklist[]
  onChange: (checklists: Checklist[]) => void
  onlyButton?: boolean
  onlyDisplay?: boolean
}

export default function ChecklistManager({ checklists = [], onChange, onlyButton = false, onlyDisplay = false }: ChecklistManagerProps) {
  const [isOpen, setIsOpen] = useState(false)
  const [newChecklistTitle, setNewChecklistTitle] = useState('')
  const [editingTitleId, setEditingTitleId] = useState<string | null>(null)
  const [editingTitleValue, setEditingTitleValue] = useState('')
  const [editingItemId, setEditingItemId] = useState<string | null>(null)
  const [editingItemValue, setEditingItemValue] = useState('')
  const [deleteConfirmOpen, setDeleteConfirmOpen] = useState(false)
  const [checklistToDelete, setChecklistToDelete] = useState<string | null>(null)

  const safeChecklists = Array.isArray(checklists) ? checklists : []

  const totalItems = safeChecklists.reduce((sum, checklist) => sum + checklist.items.length, 0)
  const completedItems = safeChecklists.reduce(
    (sum, checklist) => sum + checklist.items.filter(item => item.completed).length,
    0
  )

  const handleCreateChecklist = () => {
    if (!newChecklistTitle.trim()) return

    const newChecklist: Checklist = {
      id: crypto.randomUUID(),
      title: newChecklistTitle.trim(),
      items: [],
    }

    onChange([...safeChecklists, newChecklist])
    setNewChecklistTitle('')
    setIsOpen(false)
  }

  const handleStartEditTitle = (checklist: Checklist) => {
    setEditingTitleId(checklist.id)
    setEditingTitleValue(checklist.title)
  }

  const handleSaveTitle = () => {
    if (!editingTitleId || !editingTitleValue.trim()) return

    onChange(
      safeChecklists.map(c =>
        c.id === editingTitleId ? { ...c, title: editingTitleValue.trim() } : c
      )
    )
    setEditingTitleId(null)
    setEditingTitleValue('')
  }

  const handleCancelEditTitle = () => {
    setEditingTitleId(null)
    setEditingTitleValue('')
  }

  const handleStartEditItem = (item: ChecklistItem) => {
    setEditingItemId(item.id)
    setEditingItemValue(item.text)
  }

  const handleSaveItem = (checklistId: string) => {
    if (!editingItemId || editingItemValue.trim() === '') return

    handleUpdateItemInExisting(checklistId, editingItemId, 'text', editingItemValue.trim())
    setEditingItemId(null)
    setEditingItemValue('')
  }

  const handleCancelEditItem = () => {
    setEditingItemId(null)
    setEditingItemValue('')
  }

  const handleDeleteChecklist = (checklistId: string) => {
    setChecklistToDelete(checklistId)
    setDeleteConfirmOpen(true)
  }

  const confirmDelete = () => {
    if (!checklistToDelete) return
    onChange(safeChecklists.filter(c => c.id !== checklistToDelete))
    setDeleteConfirmOpen(false)
    setChecklistToDelete(null)
  }

  const cancelDelete = () => {
    setDeleteConfirmOpen(false)
    setChecklistToDelete(null)
  }

  const handleAddItemToExisting = (checklistId: string) => {
    onChange(
      safeChecklists.map(checklist => {
        if (checklist.id === checklistId) {
          return {
            ...checklist,
            items: [
              ...checklist.items,
              { id: crypto.randomUUID(), text: '', completed: false },
            ],
          }
        }
        return checklist
      })
    )
  }

  const handleUpdateItemInExisting = (
    checklistId: string,
    itemId: string,
    field: 'text' | 'completed',
    value: string | boolean
  ) => {
    onChange(
      safeChecklists.map(checklist => {
        if (checklist.id === checklistId) {
          return {
            ...checklist,
            items: checklist.items.map(item =>
              item.id === itemId ? { ...item, [field]: value } : item
            ),
          }
        }
        return checklist
      })
    )
  }

  const handleRemoveItemFromExisting = (checklistId: string, itemId: string) => {
    onChange(
      safeChecklists.map(checklist => {
        if (checklist.id === checklistId) {
          return {
            ...checklist,
            items: checklist.items.filter(item => item.id !== itemId),
          }
        }
        return checklist
      })
    )
  }


  const getChecklistProgress = (checklist: Checklist) => {
    const total = checklist.items.length
    const completed = checklist.items.filter(item => item.completed).length
    return { completed, total }
  }

  // Render a single checklist
  const renderChecklist = (checklist: Checklist, variant: 'display' | 'default') => {
    const { completed, total } = getChecklistProgress(checklist)
    const percentage = total > 0 ? Math.round((completed / total) * 100) : 0

    return (
      <div className="p-3">
        {/* Checklist Header */}
        <div className="flex items-center justify-between mb-2 h-8">
          <div className={`flex items-center w-8`}>
            <CheckCircleIcon className="w-5 h-5 text-gray-400" />
          </div>

          {/* Editable Title */}
          {editingTitleId === checklist.id ? (
            <div className="flex items-center gap-2 flex-1">
              <input
                type="text"
                value={editingTitleValue}
                onChange={e => setEditingTitleValue(e.target.value)}
                onKeyDown={e => {
                  if (e.key === 'Enter') handleSaveTitle()
                  if (e.key === 'Escape') handleCancelEditTitle()
                }}
                onBlur={handleSaveTitle}
                className="flex-1 px-2 py-1 border border-gray-300 rounded text-sm font-semibold focus:outline-none focus:ring-1 focus:ring-[#6A1693]"
                autoFocus
              />
            </div>
          ) : (
            <button
              type="button"
              onClick={() => handleStartEditTitle(checklist)}
              className={`flex-1 text-left font-semibold text-sm text-gray-900 hover:text-[#6A1693] ${variant === 'default' ? 'ml-2' : ''}`}
            >
              {checklist.title}
            </button>
          )}

          <button
            type="button"
            onClick={() => handleDeleteChecklist(checklist.id)}
            className="p-1 text-gray-400 hover:text-gray-400 rounded hover:bg-gray-100"
          >
            <TrashIcon className="w-4 h-4" />
          </button>
        </div>

        {/* Progress Bar */}
        <div className="mb-2">
          <div className="flex items-center justify-between text-xs text-gray-600 mb-1">
            <div className={`font-medium ${variant === 'display' ? 'w-8' : 'w-24'}`}>{percentage}%</div>
            <div className="w-full bg-gray-200 rounded-full h-2">
              <div
                className="bg-[#6A1693] h-2 rounded-full transition-all"
                style={{ width: `${percentage}%` }}
              />
            </div>
          </div>
        </div>

        {/* Add Item Button */}
        {variant === 'default' && (
          <button
            type="button"
            onClick={() => handleAddItemToExisting(checklist.id)}
            className="text-xs text-[#6A1693] hover:text-[#5a1279] font-medium flex items-center gap-1 mt-2"
          >
            <PlusIcon className="w-3 h-3" />
            Add Item
          </button>
        )}

        {/* Checklist Items */}
        <div className={`space-y-2 ${variant === 'default' ? 'mt-3' : 'mt-4'}`}>
          {checklist.items.map(item => (
            <div key={item.id} className="flex items-center gap-2">
              <input
                type="checkbox"
                checked={item.completed}
                onChange={e =>
                  handleUpdateItemInExisting(
                    checklist.id,
                    item.id,
                    'completed',
                    e.target.checked
                  )
                }
                className={`w-3 h-3 text-[#6A1693] rounded ${variant === 'display' ? 'accent-[#6A1693]' : ''}`}
              />

              {editingItemId === item.id ? (
                <>
                  <input
                    type="text"
                    value={editingItemValue}
                    onChange={e => setEditingItemValue(e.target.value)}
                    onKeyDown={e => {
                      if (e.key === 'Enter') handleSaveItem(checklist.id)
                      if (e.key === 'Escape') handleCancelEditItem()
                    }}
                    onBlur={() => handleSaveItem(checklist.id)}
                    placeholder="Item text..."
                    className="flex-1 px-2 py-1 border border-gray-300 rounded text-sm focus:outline-none focus:ring-1 focus:ring-[#6A1693]"
                    autoFocus
                  />
                </>
              ) : (
                <>
                  <button
                    type="button"
                    onClick={() => handleStartEditItem(item)}
                    className={`flex-1 text-left px-2 py-1 text-sm hover:bg-gray-50 rounded ${variant === 'display' ? 'text-gray-700' : 'text-gray-900'}`}
                  >
                    {item.text || 'Click to add text...'}
                  </button>
                  <button
                    type="button"
                    onClick={() => handleRemoveItemFromExisting(checklist.id, item.id)}
                    className="text-gray-400 hover:text-red-600"
                  >
                    <XMarkIcon className="w-4 h-4" />
                  </button>
                </>
              )}
            </div>
          ))}
        </div>

        {/* Add Item Button for display variant */}
        {variant === 'display' && (
          <div className="mt-4">
            <button
              type="button"
              onClick={() => handleAddItemToExisting(checklist.id)}
              className="rounded-[16px] px-4 py-2 shadow-sm text-xs flex gap-1 hover:shadow cursor-pointer hover:bg-gray-50"
            >
              <PlusIcon className="w-3 h-3" />
              Add Item
            </button>
          </div>
        )}
      </div>
    )
  }

  // Only render button
  if (onlyButton) {
    return (
      <div className="relative inline-block">
        <button
          type="button"
          onClick={() => setIsOpen(!isOpen)}
          className='font-bold text-sm flex gap-2 p-2 px-4 rounded-[16px] items-center cursor-pointer hover:shadow transition-[300ms] text-gray-600'
        >
          <PlusIcon className="w-4 h-4" />
          {totalItems > 0 ? (
            <span className="text-xs">
              {completedItems}/{totalItems}
            </span>
          ) : (
            'Checklist'
          )}
        </button>

        {/* Dropdown */}
        {isOpen && (
          <>
            {/* Backdrop */}
            <div className="fixed inset-0 z-10" onClick={() => setIsOpen(false)} />

            {/* Dropdown content - Simple form to add checklist name */}
            <div className="absolute z-20 mt-2 w-64 bg-white border border-gray-200 shadow-lg p-4">
              <div className="space-y-3">
                <div>
                  <label className="block text-xs font-semibold text-gray-700 mb-2">Checklist Name</label>
                  <input
                    type="text"
                    value={newChecklistTitle}
                    onChange={e => setNewChecklistTitle(e.target.value)}
                    onKeyDown={e => {
                      if (e.key === 'Enter') handleCreateChecklist()
                      if (e.key === 'Escape') setIsOpen(false)
                    }}
                    placeholder="Enter checklist name..."
                    className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#6A1693]"
                    autoFocus
                  />
                </div>

                <button
                  type="button"
                  onClick={handleCreateChecklist}
                  className={`${btnPrimaryFullWidthMedium} disabled:opacity-50`}
                  disabled={!newChecklistTitle.trim()}
                >
                  Create Checklist
                </button>
              </div>
            </div>
          </>
        )}
      </div>
    )
  }

  // Only render display
  if (onlyDisplay) {
    return (
      <>
        {safeChecklists.length > 0 && (
          <div className="mt-3 space-y-3 w-full">
            {safeChecklists.map(checklist => (
              <React.Fragment key={checklist.id}>
                {renderChecklist(checklist, 'display')}
              </React.Fragment>
            ))}
          </div>
        )}

        {/* Delete Confirmation Modal */}
        <Modal
          open={deleteConfirmOpen}
          onClose={cancelDelete}
          size="sm"
          header={<h3 className="text-lg font-semibold text-gray-900">Delete Checklist</h3>}
          footer={
            <div className="flex gap-2 justify-end">
              <button onClick={cancelDelete} className={btnSecondary}>
                Cancel
              </button>
              <button onClick={confirmDelete} className={btnDanger}>
                Delete
              </button>
            </div>
          }
        >
          <p className="text-gray-600 text-sm">
            Are you sure you want to delete this checklist? This action cannot be undone.
          </p>
        </Modal>
      </>
    )
  }

  // Default: render both button and display (original behavior)
  return (
    <>
      {/* Checklist button with dropdown */}
      <div className="relative inline-block">
        <button
          type="button"
          onClick={() => setIsOpen(!isOpen)}
          className="px-3 py-2 text-sm font-medium text-[#6A1693] hover:bg-gray-50 rounded-lg border border-gray-300 flex items-center gap-2"
        >
          <PlusIcon className="w-4 h-4" />
          Checklist
        </button>

        {/* Dropdown */}
        {isOpen && (
          <>
            {/* Backdrop */}
            <div className="fixed inset-0 z-10" onClick={() => setIsOpen(false)} />

            {/* Dropdown content - Simple form to add checklist name */}
            <div className="absolute z-20 mt-2 w-64 bg-white border border-gray-200 rounded-lg shadow-lg p-4">
              <div className="space-y-3">
                <div>
                  <label className="block text-xs font-semibold text-gray-700 mb-2">Checklist Name</label>
                  <input
                    type="text"
                    value={newChecklistTitle}
                    onChange={e => setNewChecklistTitle(e.target.value)}
                    onKeyDown={e => {
                      if (e.key === 'Enter') handleCreateChecklist()
                      if (e.key === 'Escape') setIsOpen(false)
                    }}
                    placeholder="Enter checklist name..."
                    className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#6A1693]"
                    autoFocus
                  />
                </div>

                <button
                  type="button"
                  onClick={handleCreateChecklist}
                  className={`${btnPrimaryFullWidthMedium} disabled:opacity-50`}
                  disabled={!newChecklistTitle.trim()}
                >
                  Create Checklist
                </button>
              </div>
            </div>
          </>
        )}
      </div>

      {/* Checklists display */}
      {safeChecklists.length > 0 && (
        <div className="mt-3 space-y-3 w-full">
          {safeChecklists.map(checklist => (
            <React.Fragment key={checklist.id}>
              {renderChecklist(checklist, 'default')}
            </React.Fragment>
          ))}
        </div>
      )}

      {/* Delete Confirmation Modal */}
      <Modal
        open={deleteConfirmOpen}
        onClose={cancelDelete}
        size="sm"
        header={<h3 className="text-lg font-semibold text-gray-900">Delete Checklist</h3>}
        footer={
          <div className="flex gap-2 justify-end">
            <button onClick={cancelDelete} className={btnSecondary}>
              Cancel
            </button>
            <button onClick={confirmDelete} className={btnDanger}>
              Delete
            </button>
          </div>
        }
      >
        <p className="text-gray-600">
          Are you sure you want to delete this checklist? This action cannot be undone.
        </p>
      </Modal>
    </>
  )
}
