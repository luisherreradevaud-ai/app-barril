'use client'

import React, { useState } from 'react'
import { PlusIcon, PencilSquareIcon, XMarkIcon, CheckIcon } from '@heroicons/react/24/outline'
import { showToast } from '@/app/lib/functions/toast'
import { btnAction, btnPrimaryFullWidthMedium, btnSecondaryFullWidthMedium } from '@/app/lib/styles/Buttons'

export type Label = {
  id: string
  name: string
  hexCode: string
}

interface LabelManagerProps {
  labels: Label[]
  selectedLabelIds: string[]
  onLabelsChange: (labels: Label[]) => void
  onSelectionChange: (selectedIds: string[]) => void
  onSaveLabel: (label: Partial<Label>) => Promise<Label | null>
  onDeleteLabel: (id: string) => Promise<boolean>
  onlyButton?: boolean
  onlyDisplay?: boolean
}

export default function LabelManager({
  labels = [],
  selectedLabelIds = [],
  onLabelsChange,
  onSelectionChange,
  onSaveLabel,
  onDeleteLabel,
  onlyButton = false,
  onlyDisplay = false,
}: LabelManagerProps) {
  const [isOpen, setIsOpen] = useState(false)
  const [view, setView] = useState<'list' | 'edit'>('list')
  const [editingLabel, setEditingLabel] = useState<Partial<Label> | null>(null)
  const [isSaving, setIsSaving] = useState(false)

  // Ensure labels is always an array
  const safeLabels = Array.isArray(labels) ? labels : []
  const safeSelectedIds = Array.isArray(selectedLabelIds) ? selectedLabelIds : []
  const hasLabels = safeSelectedIds.length > 0

  const colorPresets = [
    '#EF4444', // red
    '#F97316', // orange
    '#F59E0B', // amber
    '#EAB308', // yellow
    '#84CC16', // lime
    '#22C55E', // green
    '#10B981', // emerald
    '#14B8A6', // teal
    '#06B6D4', // cyan
    '#0EA5E9', // sky
    '#3B82F6', // blue
    '#6366F1', // indigo
    '#8B5CF6', // violet
    '#A855F7', // purple
    '#D946EF', // fuchsia
    '#EC4899', // pink
    '#F43F5E', // rose
    '#6B7280', // gray
  ]

  const handleToggleLabel = (labelId: string) => {
    if (safeSelectedIds.includes(labelId)) {
      onSelectionChange(safeSelectedIds.filter(id => id !== labelId))
    } else {
      onSelectionChange([...safeSelectedIds, labelId])
    }
  }

  const handleAddLabel = () => {
    setEditingLabel({ name: '', hexCode: colorPresets[0] })
    setView('edit')
  }

  const handleEditLabel = (label: Label) => {
    setEditingLabel(label)
    setView('edit')
  }

  const handleSaveLabel = async () => {
    if (!editingLabel?.hexCode) {
      showToast('Label color is required')
      return
    }

    setIsSaving(true)
    try {
      const savedLabel = await onSaveLabel(editingLabel)
      if (savedLabel) {
        // Update local labels list
        if (editingLabel.id) {
          // Update existing
          onLabelsChange(safeLabels.map(l => (l.id === savedLabel.id ? savedLabel : l)))
        } else {
          // Add new
          onLabelsChange([...safeLabels, savedLabel])
        }
        showToast(editingLabel.id ? 'Label updated' : 'Label created')
        setView('list')
        setEditingLabel(null)
      } else {
        showToast('Failed to save label')
      }
    } catch (error) {
      console.error('Error saving label:', error)
      showToast('Failed to save label')
    } finally {
      setIsSaving(false)
    }
  }

  const handleDeleteLabel = async () => {
    if (!editingLabel?.id) return
    if (!confirm('Are you sure you want to delete this label?')) return

    setIsSaving(true)
    try {
      const success = await onDeleteLabel(editingLabel.id)
      if (success) {
        // Remove from local labels list
        onLabelsChange(safeLabels.filter(l => l.id !== editingLabel.id))
        // Remove from selected if it was selected
        onSelectionChange(safeSelectedIds.filter(id => id !== editingLabel.id))
        showToast('Label deleted')
        setView('list')
        setEditingLabel(null)
      } else {
        showToast('Failed to delete label')
      }
    } catch (error) {
      console.error('Error deleting label:', error)
      showToast('Failed to delete label')
    } finally {
      setIsSaving(false)
    }
  }

  const handleCancel = () => {
    setView('list')
    setEditingLabel(null)
  }

  // Only render button
  if (onlyButton) {
    return (
      <div className="relative inline-block">
        <button
          type="button"
          onClick={() => setIsOpen(!isOpen)}
          className='font-bold text-sm flex gap-1 p-2 px-4 rounded-[16px] items-center cursor-pointer hover:shadow transition-[300ms] text-gray-600'
        >
          <PlusIcon className="w-4 h-4" />
          {hasLabels ? (
            <>
              <div className="flex gap-1">
                {safeSelectedIds.slice(0, 2).map(labelId => {
                  const label = safeLabels.find(l => l.id === labelId)
                  if (!label) return null
                  return (
                    <div
                      key={label.id}
                      className="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium text-white"
                      style={{ backgroundColor: label.hexCode }}
                    >
                      {label.name || '(Unnamed)'}
                    </div>
                  )
                })}
                {safeSelectedIds.length > 2 && (
                  <div className="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-gray-400 text-white">
                    +{safeSelectedIds.length - 2}
                  </div>
                )}
              </div>
            </>
          ) : (
            'Labels'
          )}
        </button>

        {/* Dropdown */}
        {isOpen && (
          <>
            {/* Backdrop */}
            <div className="fixed inset-0 z-10" onClick={() => { setIsOpen(false); setView('list') }} />

            {/* Dropdown content */}
            <div className="absolute z-20 mt-2 w-64 bg-white border border-gray-200 shadow-lg overflow-hidden">
              {view === 'list' ? (
                /* View 1: Label List */
                <div>
                  <div className="max-h-64 overflow-y-auto">
                    {safeLabels.length === 0 ? (
                      <div className="px-4 py-8 text-center text-gray-500 text-sm">
                        No labels yet. Create your first label!
                      </div>
                    ) : (
                      <div className="divide-y divide-gray-100">
                        {safeLabels.map(label => (
                          <div
                            key={label.id}
                            className="flex items-center gap-3 px-3 py-2 hover:bg-gray-50 cursor-pointer"
                            onClick={() => handleToggleLabel(label.id)}
                          >
                            <input
                              type="checkbox"
                              checked={safeSelectedIds.includes(label.id)}
                              onChange={() => handleToggleLabel(label.id)}
                              className="w-4 h-4 text-[#6A1693] rounded"
                              onClick={e => e.stopPropagation()}
                            />
                            <div
                              className="w-4 h-4 rounded"
                              style={{ backgroundColor: label.hexCode }}
                            />
                            <span className="flex-1 text-sm text-gray-900">{label.name || '(Unnamed)'}</span>
                            <button
                              type="button"
                              onClick={e => {
                                e.stopPropagation()
                                handleEditLabel(label)
                              }}
                              className="p-1 text-gray-400 hover:text-gray-600 rounded hover:bg-gray-100"
                            >
                              <PencilSquareIcon className="w-4 h-4" />
                            </button>
                          </div>
                        ))}
                      </div>
                    )}
                  </div>

                  {/* Add Label Button */}
                  <div className="border-t border-gray-200 p-2">
                    <button
                      type="button"
                      onClick={handleAddLabel}
                      className="w-full flex items-center justify-center gap-2 px-3 py-2 text-sm font-medium text-[#6A1693] hover:bg-gray-50 rounded"
                    >
                      <PlusIcon className="w-4 h-4" />
                      Add Label
                    </button>
                  </div>
                </div>
              ) : (
                /* View 2: Add/Edit Label */
                <div className="p-4 space-y-4">
                  <div>
                    <label className="block text-xs font-semibold text-gray-700 mb-2">Label Name</label>
                    <input
                      type="text"
                      value={editingLabel?.name || ''}
                      onChange={e => setEditingLabel({ ...editingLabel, name: e.target.value })}
                      placeholder="Enter label name..."
                      className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#6A1693]"
                      autoFocus
                    />
                  </div>

                  <div>
                    <label className="block text-xs font-semibold text-gray-700 mb-2">Color</label>
                    <div className="grid grid-cols-6 gap-2">
                      {colorPresets.map(color => (
                        <button
                          key={color}
                          type="button"
                          onClick={() => setEditingLabel({ ...editingLabel, hexCode: color })}
                          className={`w-6 h-6 rounded border-2 transition-all ${
                            editingLabel?.hexCode === color
                              ? 'border-gray-900 scale-110'
                              : 'border-transparent hover:border-gray-300'
                          }`}
                          style={{ backgroundColor: color }}
                        />
                      ))}
                    </div>

                    {/* Custom color input */}
                    <div className="mt-3 flex items-center gap-2">
                      <input
                        type="color"
                        value={editingLabel?.hexCode || '#000000'}
                        onChange={e => setEditingLabel({ ...editingLabel, hexCode: e.target.value.toUpperCase() })}
                        className="w-8 h-6 rounded border border-gray-300 cursor-pointer"
                      />
                      <input
                        type="text"
                        value={editingLabel?.hexCode || ''}
                        onChange={e => {
                          const value = e.target.value.toUpperCase()
                          if (/^#[0-9A-F]{0,6}$/.test(value)) {
                            setEditingLabel({ ...editingLabel, hexCode: value })
                          }
                        }}
                        placeholder="#000000"
                        className="flex-1 px-2 py-1 border border-gray-300 rounded-lg text-xs font-mono"
                        maxLength={7}
                      />
                    </div>
                  </div>

                  {/* Preview */}
                  {editingLabel?.hexCode && (
                    <div className="flex items-center gap-2">
                      <span className="text-xs text-gray-500">Preview:</span>
                      <div
                        className="inline-flex items-center gap-2 px-2 py-1 rounded text-xs font-medium text-white"
                        style={{ backgroundColor: editingLabel.hexCode }}
                      >
                        {editingLabel.name || '(Unnamed)'}
                      </div>
                    </div>
                  )}

                  {/* Action Buttons */}
                  <div className="flex gap-2 pt-2">
                    <button
                      type="button"
                      onClick={handleCancel}
                      className={btnSecondaryFullWidthMedium}
                      disabled={isSaving}
                    >
                      Cancel
                    </button>
                    {editingLabel?.id && (
                      <button
                        type="button"
                        onClick={handleDeleteLabel}
                        className="px-2 py-1.5 text-xs font-medium text-white bg-red-600 rounded-lg hover:bg-red-700"
                        disabled={isSaving}
                      >
                        Delete
                      </button>
                    )}
                    <button
                      type="button"
                      onClick={handleSaveLabel}
                      className={btnPrimaryFullWidthMedium}
                      disabled={isSaving || !editingLabel?.hexCode}
                    >
                      {isSaving ? 'Saving...' : editingLabel?.id ? 'Update' : 'Create'}
                    </button>
                  </div>
                </div>
              )}
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
        {hasLabels && (
          <div className="flex flex-wrap items-center gap-2">
            {safeSelectedIds.map(labelId => {
              const label = safeLabels.find(l => l.id === labelId)
              if (!label) return null
              return (
                <div
                  key={label.id}
                  className="inline-flex items-center px-2 py-1 rounded text-xs font-medium text-white"
                  style={{ backgroundColor: label.hexCode }}
                >
                  {label.name || '(Unnamed)'}
                </div>
              )
            })}
          </div>
        )}
      </>
    )
  }

  return (
    <div className="relative">
      {/* Label selector button */}
      <button
        type="button"
        onClick={() => setIsOpen(!isOpen)}
        className="w-full px-3 py-2 text-left border border-gray-300 rounded-lg text-sm bg-white hover:bg-gray-50 flex items-center justify-between"
      >
        <span className={safeSelectedIds.length > 0 ? 'text-gray-900' : 'text-gray-500'}>
          {safeSelectedIds.length === 0
            ? 'Select labels...'
            : `${safeSelectedIds.length} label${safeSelectedIds.length > 1 ? 's' : ''} selected`}
        </span>
        <svg
          className={`w-4 h-4 text-gray-400 transition-transform ${isOpen ? 'rotate-180' : ''}`}
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
        >
          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 9l-7 7-7-7" />
        </svg>
      </button>

      {/* Selected labels display */}
      {safeSelectedIds.length > 0 && (
        <div className="mt-2 flex flex-wrap gap-2">
          {safeSelectedIds.map(labelId => {
            const label = safeLabels.find(l => l.id === labelId)
            if (!label) return null
            return (
              <div
                key={label.id}
                className="inline-flex items-center gap-2 px-2 py-1 rounded text-xs font-medium text-white"
                style={{ backgroundColor: label.hexCode }}
              >
                {label.name || '(Unnamed)'}
                <button
                  type="button"
                  onClick={() => handleToggleLabel(label.id)}
                  className="hover:bg-black/10 rounded-full p-0.5"
                >
                  <XMarkIcon className="w-3 h-3" />
                </button>
              </div>
            )
          })}
        </div>
      )}

      {/* Dropdown */}
      {isOpen && (
        <>
          {/* Backdrop */}
          <div className="fixed inset-0 z-10" onClick={() => setIsOpen(false)} />

          {/* Dropdown content */}
          <div className="absolute z-20 mt-2 w-full bg-white border border-gray-200 rounded-lg shadow-lg overflow-hidden">
            {view === 'list' ? (
              /* View 1: Label List */
              <div>
                <div className="max-h-64 overflow-y-auto">
                  {safeLabels.length === 0 ? (
                    <div className="px-4 py-8 text-center text-gray-500 text-sm">
                      No labels yet. Create your first label!
                    </div>
                  ) : (
                    <div className="divide-y divide-gray-100">
                      {safeLabels.map(label => (
                        <div
                          key={label.id}
                          className="flex items-center gap-3 px-3 py-2 hover:bg-gray-50 cursor-pointer"
                          onClick={() => handleToggleLabel(label.id)}
                        >
                          <input
                            type="checkbox"
                            checked={safeSelectedIds.includes(label.id)}
                            onChange={() => handleToggleLabel(label.id)}
                            className="w-4 h-4 text-[#6A1693] rounded"
                            onClick={e => e.stopPropagation()}
                          />
                          <div
                            className="w-4 h-4 rounded"
                            style={{ backgroundColor: label.hexCode }}
                          />
                          <span className="flex-1 text-sm text-gray-900">{label.name || '(Unnamed)'}</span>
                          <button
                            type="button"
                            onClick={e => {
                              e.stopPropagation()
                              handleEditLabel(label)
                            }}
                            className="p-1 text-gray-400 hover:text-gray-600 rounded hover:bg-gray-100"
                          >
                            <PencilSquareIcon className="w-4 h-4" />
                          </button>
                        </div>
                      ))}
                    </div>
                  )}
                </div>

                {/* Add Label Button */}
                <div className="border-t border-gray-200 p-2">
                  <button
                    type="button"
                    onClick={handleAddLabel}
                    className="w-full flex items-center justify-center gap-2 px-3 py-2 text-sm font-medium text-[#6A1693] hover:bg-gray-50 rounded"
                  >
                    <PlusIcon className="w-4 h-4" />
                    Add Label
                  </button>
                </div>
              </div>
            ) : (
              /* View 2: Add/Edit Label */
              <div className="p-4 space-y-4">
                <div>
                  <label className="block text-xs font-semibold text-gray-700 mb-2">Label Name</label>
                  <input
                    type="text"
                    value={editingLabel?.name || ''}
                    onChange={e => setEditingLabel({ ...editingLabel, name: e.target.value })}
                    placeholder="Enter label name..."
                    className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#6A1693]"
                    autoFocus
                  />
                </div>

                <div>
                  <label className="block text-xs font-semibold text-gray-700 mb-2">Color</label>
                  <div className="grid grid-cols-6 gap-2">
                    {colorPresets.map(color => (
                      <button
                        key={color}
                        type="button"
                        onClick={() => setEditingLabel({ ...editingLabel, hexCode: color })}
                        className={`w-8 h-8 rounded border-2 transition-all ${
                          editingLabel?.hexCode === color
                            ? 'border-gray-900 scale-110'
                            : 'border-transparent hover:border-gray-300'
                        }`}
                        style={{ backgroundColor: color }}
                      />
                    ))}
                  </div>

                  {/* Custom color input */}
                  <div className="mt-3 flex items-center gap-2">
                    <input
                      type="color"
                      value={editingLabel?.hexCode || '#000000'}
                      onChange={e => setEditingLabel({ ...editingLabel, hexCode: e.target.value.toUpperCase() })}
                      className="w-12 h-8 rounded border border-gray-300 cursor-pointer"
                    />
                    <input
                      type="text"
                      value={editingLabel?.hexCode || ''}
                      onChange={e => {
                        const value = e.target.value.toUpperCase()
                        if (/^#[0-9A-F]{0,6}$/.test(value)) {
                          setEditingLabel({ ...editingLabel, hexCode: value })
                        }
                      }}
                      placeholder="#000000"
                      className="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm font-mono"
                      maxLength={7}
                    />
                  </div>
                </div>

                {/* Preview */}
                {editingLabel?.hexCode && (
                  <div className="flex items-center gap-2">
                    <span className="text-xs text-gray-500">Preview:</span>
                    <div
                      className="inline-flex items-center gap-2 px-3 py-1 rounded text-sm font-medium text-white"
                      style={{ backgroundColor: editingLabel.hexCode }}
                    >
                      {editingLabel.name || '(Unnamed)'}
                    </div>
                  </div>
                )}

                {/* Action Buttons */}
                <div className="flex gap-2 pt-2">
                  <button
                    type="button"
                    onClick={handleCancel}
                    className="flex-1 px-3 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200"
                    disabled={isSaving}
                  >
                    Cancel
                  </button>
                  {editingLabel?.id && (
                    <button
                      type="button"
                      onClick={handleDeleteLabel}
                      className="px-3 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700"
                      disabled={isSaving}
                    >
                      Delete
                    </button>
                  )}
                  <button
                    type="button"
                    onClick={handleSaveLabel}
                    className="flex-1 px-3 py-2 text-sm font-medium text-white bg-[#6A1693] rounded-lg hover:bg-[#5a1279] disabled:opacity-50"
                    disabled={isSaving || !editingLabel?.hexCode}
                  >
                    {isSaving ? 'Saving...' : editingLabel?.id ? 'Update' : 'Create'}
                  </button>
                </div>
              </div>
            )}
          </div>
        </>
      )}
    </div>
  )
}
