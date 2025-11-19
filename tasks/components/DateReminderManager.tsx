'use client'

import React, { useState } from 'react'
import { CalendarIcon, BellIcon, XMarkIcon } from '@heroicons/react/24/outline'
import Select from 'react-select'
import type { DueDateReminder } from '@/app/lib/api/tasks'
import { btnAction, btnPrimaryFullWidthMedium, btnSecondaryFullWidthMedium } from '@/app/lib/styles/Buttons'

interface DateReminderManagerProps {
  startDate: string | null
  dueDate: string | null
  dueDateReminder: DueDateReminder | null
  onStartDateChange: (date: string | null) => void
  onDueDateChange: (date: string | null) => void
  onReminderChange: (reminder: DueDateReminder | null) => void
  onlyButton?: boolean
  onlyDisplay?: boolean
}

const dueDateReminderOptions = [
  { value: 'atTimeDueDate', label: 'At time of due date' },
  { value: '1 hour before', label: '1 hour before' },
  { value: '2 hours before', label: '2 hours before' },
  { value: '1 day before', label: '1 day before' },
  { value: '2 days before', label: '2 days before' },
]

export default function DateReminderManager({
  startDate,
  dueDate,
  dueDateReminder,
  onStartDateChange,
  onDueDateChange,
  onReminderChange,
  onlyButton = false,
  onlyDisplay = false,
}: DateReminderManagerProps) {
  const [isOpen, setIsOpen] = useState(false)

  const formatDisplayDate = (dateString: string | null) => {
    if (!dateString) return null
    const date = new Date(dateString)
    return date.toLocaleDateString('en-US', {
      month: 'short',
      day: 'numeric',
      year: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    })
  }

  // Format date for datetime-local input (YYYY-MM-DDTHH:mm)
  const formatForInput = (dateString: string | null) => {
    if (!dateString) return ''
    try {
      const date = new Date(dateString)
      // Check if date is valid
      if (isNaN(date.getTime())) return ''

      // Format to YYYY-MM-DDTHH:mm
      const year = date.getFullYear()
      const month = String(date.getMonth() + 1).padStart(2, '0')
      const day = String(date.getDate()).padStart(2, '0')
      const hours = String(date.getHours()).padStart(2, '0')
      const minutes = String(date.getMinutes()).padStart(2, '0')

      return `${year}-${month}-${day}T${hours}:${minutes}`
    } catch (error) {
      console.error('Error formatting date:', error)
      return ''
    }
  }

  const hasData = startDate || dueDate || dueDateReminder

  // Only render button
  if (onlyButton) {
    return (
      <div className="relative inline-block">
        <button
          type="button"
          onClick={() => setIsOpen(!isOpen)}
          className='font-bold text-sm flex gap-2 p-2 px-4 rounded-[16px] items-center cursor-pointer hover:shadow transition-[300ms] text-gray-600'
        >
          <CalendarIcon className="w-4 h-4" />
          {hasData ? (
            <>
              {dueDate && (
                <span className="text-xs">
                  Due {new Date(dueDate).toLocaleDateString('en-US', { month: 'short', day: 'numeric' })}
                </span>
              )}
              {!dueDate && startDate && (
                <span className="text-xs">
                  Starts {new Date(startDate).toLocaleDateString('en-US', { month: 'short', day: 'numeric' })}
                </span>
              )}
            </>
          ) : (
            'Dates'
          )}
        </button>

        {/* Dropdown */}
        {isOpen && (
          <>
            {/* Backdrop */}
            <div className="fixed inset-0 z-10" onClick={() => setIsOpen(false)} />

            {/* Dropdown content */}
            <div className="absolute z-20 mt-2 w-64 bg-white border border-gray-200 shadow-lg p-4 space-y-4">
            {/* Start Date */}
            <div>
              <label className="block text-xs font-semibold text-gray-700 mb-2">Start Date</label>
              <div className="flex items-center gap-2">
                <input
                  type="datetime-local"
                  value={formatForInput(startDate)}
                  onChange={e => onStartDateChange(e.target.value || null)}
                  className="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#6A1693]"
                />
                {startDate && (
                  <button
                    type="button"
                    onClick={() => onStartDateChange(null)}
                    className="p-2 text-gray-400 hover:text-gray-600 rounded hover:bg-gray-100"
                  >
                    <XMarkIcon className="w-4 h-4" />
                  </button>
                )}
              </div>
            </div>

            {/* Due Date */}
            <div>
              <label className="block text-xs font-semibold text-gray-700 mb-2">Due Date</label>
              <div className="flex items-center gap-2">
                <input
                  type="datetime-local"
                  value={formatForInput(dueDate)}
                  onChange={e => onDueDateChange(e.target.value || null)}
                  className="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#6A1693]"
                />
                {dueDate && (
                  <button
                    type="button"
                    onClick={() => onDueDateChange(null)}
                    className="p-2 text-gray-400 hover:text-gray-600 rounded hover:bg-gray-100"
                  >
                    <XMarkIcon className="w-4 h-4" />
                  </button>
                )}
              </div>
            </div>

            {/* Reminder */}
            <div>
              <label className="block text-xs font-semibold text-gray-700 mb-2">Reminder</label>
              <Select
                className="text-sm"
                classNamePrefix="text-sm"
                isClearable
                options={dueDateReminderOptions}
                value={dueDateReminderOptions.find(opt => opt.value === dueDateReminder) || null}
                onChange={opt => onReminderChange((opt?.value as DueDateReminder) || null)}
                placeholder="Select reminder..."
                menuPortalTarget={typeof document !== 'undefined' ? document.body : null}
                styles={{
                  menuPortal: (base) => ({ ...base, zIndex: 9999 })
                }}
              />
            </div>

              {/* Close button */}
              <div className="pt-2 border-t border-gray-200">
                <button
                  type="button"
                  onClick={() => setIsOpen(false)}
                  className={btnPrimaryFullWidthMedium}
                >
                  Done
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
        {hasData && (
          <div className="space-y-1 text-xs text-gray-600 w-full">
            {startDate && (
              <div className="flex items-center gap-2">
                <CalendarIcon className="w-3 h-3" />
                <span>Start: {formatDisplayDate(startDate)}</span>
              </div>
            )}
            {dueDate && (
              <div className="flex items-center gap-2">
                <CalendarIcon className="w-3 h-3" />
                <span>Due: {formatDisplayDate(dueDate)}</span>
              </div>
            )}
            {dueDateReminder && (
              <div className="flex items-center gap-2">
                <BellIcon className="w-3 h-3" />
                <span>Reminder: {dueDateReminderOptions.find(opt => opt.value === dueDateReminder)?.label}</span>
              </div>
            )}
          </div>
        )}
      </>
    )
  }

  // Default: render both button and display (original behavior)
  return (
    <>
      {/* Button with dropdown */}
      <div className="relative inline-block">
        <button
          type="button"
          onClick={() => setIsOpen(!isOpen)}
          className="px-3 py-2 text-sm font-medium text-[#6A1693] hover:bg-gray-50 rounded-lg border border-gray-300 flex items-center gap-2"
        >
          <CalendarIcon className="w-4 h-4" />
          Dates
        </button>

        {/* Dropdown */}
        {isOpen && (
          <>
            {/* Backdrop */}
            <div className="fixed inset-0 z-10" onClick={() => setIsOpen(false)} />

            {/* Dropdown content */}
            <div className="absolute z-20 mt-2 w-64 bg-white border border-gray-200 rounded-lg shadow-lg p-4 space-y-4">
            {/* Start Date */}
            <div>
              <label className="block text-xs font-semibold text-gray-700 mb-2">Start Date</label>
              <div className="flex items-center gap-2">
                <input
                  type="datetime-local"
                  value={formatForInput(startDate)}
                  onChange={e => onStartDateChange(e.target.value || null)}
                  className="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#6A1693]"
                />
                {startDate && (
                  <button
                    type="button"
                    onClick={() => onStartDateChange(null)}
                    className="p-2 text-gray-400 hover:text-gray-600 rounded hover:bg-gray-100"
                  >
                    <XMarkIcon className="w-4 h-4" />
                  </button>
                )}
              </div>
            </div>

            {/* Due Date */}
            <div>
              <label className="block text-xs font-semibold text-gray-700 mb-2">Due Date</label>
              <div className="flex items-center gap-2">
                <input
                  type="datetime-local"
                  value={formatForInput(dueDate)}
                  onChange={e => onDueDateChange(e.target.value || null)}
                  className="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#6A1693]"
                />
                {dueDate && (
                  <button
                    type="button"
                    onClick={() => onDueDateChange(null)}
                    className="p-2 text-gray-400 hover:text-gray-600 rounded hover:bg-gray-100"
                  >
                    <XMarkIcon className="w-4 h-4" />
                  </button>
                )}
              </div>
            </div>

            {/* Reminder */}
            <div>
              <label className="block text-xs font-semibold text-gray-700 mb-2">Reminder</label>
              <Select
                className="text-sm"
                classNamePrefix="text-sm"
                isClearable
                options={dueDateReminderOptions}
                value={dueDateReminderOptions.find(opt => opt.value === dueDateReminder) || null}
                onChange={opt => onReminderChange((opt?.value as DueDateReminder) || null)}
                placeholder="Select reminder..."
                menuPortalTarget={typeof document !== 'undefined' ? document.body : null}
                styles={{
                  menuPortal: (base) => ({ ...base, zIndex: 9999 })
                }}
              />
            </div>

              {/* Close button */}
              <div className="pt-2 border-t border-gray-200">
                <button
                  type="button"
                  onClick={() => setIsOpen(false)}
                  className={btnPrimaryFullWidthMedium}
                >
                  Done
                </button>
              </div>
            </div>
          </>
        )}
      </div>

      {/* Display summary when dates are set */}
      {hasData && (
        <div className="mt-3 space-y-1 text-xs text-gray-600 w-full">
          {startDate && (
            <div className="flex items-center gap-2">
              <CalendarIcon className="w-3 h-3" />
              <span>Start: {formatDisplayDate(startDate)}</span>
            </div>
          )}
          {dueDate && (
            <div className="flex items-center gap-2">
              <CalendarIcon className="w-3 h-3" />
              <span>Due: {formatDisplayDate(dueDate)}</span>
            </div>
          )}
          {dueDateReminder && (
            <div className="flex items-center gap-2">
              <BellIcon className="w-3 h-3" />
              <span>Reminder: {dueDateReminderOptions.find(opt => opt.value === dueDateReminder)?.label}</span>
            </div>
          )}
        </div>
      )}
    </>
  )
}
