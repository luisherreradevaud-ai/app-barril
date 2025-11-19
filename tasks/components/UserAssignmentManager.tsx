'use client'

import React, { useState } from 'react'
import { PlusIcon, UserIcon, XMarkIcon } from '@heroicons/react/24/outline'
import Select from 'react-select'
import { btnAction, btnPrimaryFullWidthMedium, btnSecondaryFullWidthMedium } from '@/app/lib/styles/Buttons'

interface UserAssignmentManagerProps {
  allUsers: any[]
  selectedUsers: string[]
  onSelectionChange: (userIds: string[]) => void
  onlyButton?: boolean
  onlyDisplay?: boolean
}

export default function UserAssignmentManager({
  allUsers = [],
  selectedUsers = [],
  onSelectionChange,
  onlyButton = false,
  onlyDisplay = false,
}: UserAssignmentManagerProps) {
  const [isOpen, setIsOpen] = useState(false)

  const safeUsers = Array.isArray(allUsers) ? allUsers : []
  const safeSelectedUsers = Array.isArray(selectedUsers) ? selectedUsers : []

  const hasAssignments = safeSelectedUsers.length > 0

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
          {hasAssignments ? (
            <>
              <div className="flex -space-x-1">
                {safeSelectedUsers.slice(0, 3).map(userId => {
                  const user = safeUsers.find(u => u.id === userId)
                  if (!user) return null
                  return (
                    <div
                      key={userId}
                      className="w-5 h-5 rounded-full bg-[#6A1693] text-white flex items-center justify-center text-[10px] font-medium border border-white overflow-hidden"
                      title={user.fullName || user.email || 'Unknown User'}
                    >
                      {user.avatarUrl ? (
                        <img
                          src={user.avatarUrl}
                          alt={user.fullName || 'User'}
                          className="w-full h-full object-cover"
                        />
                      ) : (
                        user.fullName ? user.fullName.charAt(0).toUpperCase() : user.email?.charAt(0).toUpperCase() || '?'
                      )}
                    </div>
                  )
                })}
                {safeSelectedUsers.length > 3 && (
                  <div className="w-5 h-5 rounded-full bg-gray-400 text-white flex items-center justify-center text-[10px] font-medium border border-white">
                    +{safeSelectedUsers.length - 3}
                  </div>
                )}
              </div>
            </>
          ) : (
            'Assign'
          )}
        </button>

        {/* Dropdown */}
        {isOpen && (
          <>
            {/* Backdrop */}
            <div className="fixed inset-0 z-10" onClick={() => setIsOpen(false)} />

            {/* Dropdown content */}
            <div className="absolute z-20 mt-2 w-72 bg-white border border-gray-200 shadow-lg p-4">
              <div className="space-y-3">
                <div>
                  <label className="block text-xs font-semibold text-gray-700 mb-2">Assign Users</label>
                  <Select
                    className="text-sm"
                    classNamePrefix="text-sm"
                    isMulti
                    options={safeUsers.map(user => ({
                      value: user.id,
                      label: user.fullName || user.email,
                      avatarUrl: user.avatarUrl,
                    }))}
                    value={safeSelectedUsers.map(userId => {
                      const user = safeUsers.find(u => u.id === userId)
                      return {
                        value: userId,
                        label: user?.fullName || user?.email || 'Unknown',
                        avatarUrl: user?.avatarUrl,
                      }
                    })}
                    onChange={opts => onSelectionChange(opts ? opts.map(opt => opt.value) : [])}
                    placeholder="Select users..."
                    formatOptionLabel={(option: any) => (
                      <div className="flex items-center gap-2">
                        {option.avatarUrl ? (
                          <img
                            src={option.avatarUrl}
                            alt={option.label}
                            className="w-5 h-5 rounded-full object-cover"
                          />
                        ) : (
                          <div className="w-5 h-5 rounded-full bg-[#6A1693] text-white flex items-center justify-center text-xs font-medium">
                            {option.label?.charAt(0)?.toUpperCase() || '?'}
                          </div>
                        )}
                        <span>{option.label}</span>
                      </div>
                    )}
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
        {hasAssignments && (
          <div className="flex items-center gap-2">
            <UserIcon className="w-3 h-3 text-gray-400" />
            <div className="flex -space-x-2">
              {safeSelectedUsers.map(userId => {
                const user = safeUsers.find(u => u.id === userId)
                if (!user) return null
                return (
                  <div
                    key={userId}
                    className="w-6 h-6 rounded-full bg-[#6A1693] text-white flex items-center justify-center text-xs font-medium border-2 border-white overflow-hidden"
                    title={user.fullName || user.email || 'Unknown User'}
                  >
                    {user.avatarUrl ? (
                      <img
                        src={user.avatarUrl}
                        alt={user.fullName || 'User'}
                        className="w-full h-full object-cover"
                      />
                    ) : (
                      user.fullName ? user.fullName.charAt(0).toUpperCase() : user.email?.charAt(0).toUpperCase() || '?'
                    )}
                  </div>
                )
              })}
            </div>
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
          <UserIcon className="w-4 h-4" />
          Assign
        </button>

        {/* Dropdown */}
        {isOpen && (
          <>
            {/* Backdrop */}
            <div className="fixed inset-0 z-10" onClick={() => setIsOpen(false)} />

            {/* Dropdown content */}
            <div className="absolute z-20 mt-2 w-72 bg-white border border-gray-200 rounded-lg shadow-lg p-4">
              <div className="space-y-3">
                <div>
                  <label className="block text-xs font-semibold text-gray-700 mb-2">Assign Users</label>
                  <Select
                    className="text-sm"
                    classNamePrefix="text-sm"
                    isMulti
                    options={safeUsers.map(user => ({
                      value: user.id,
                      label: user.fullName || user.email,
                      avatarUrl: user.avatarUrl,
                    }))}
                    value={safeSelectedUsers.map(userId => {
                      const user = safeUsers.find(u => u.id === userId)
                      return {
                        value: userId,
                        label: user?.fullName || user?.email || 'Unknown',
                        avatarUrl: user?.avatarUrl,
                      }
                    })}
                    onChange={opts => onSelectionChange(opts ? opts.map(opt => opt.value) : [])}
                    placeholder="Select users..."
                    formatOptionLabel={(option: any) => (
                      <div className="flex items-center gap-2">
                        {option.avatarUrl ? (
                          <img
                            src={option.avatarUrl}
                            alt={option.label}
                            className="w-5 h-5 rounded-full object-cover"
                          />
                        ) : (
                          <div className="w-5 h-5 rounded-full bg-[#6A1693] text-white flex items-center justify-center text-xs font-medium">
                            {option.label?.charAt(0)?.toUpperCase() || '?'}
                          </div>
                        )}
                        <span>{option.label}</span>
                      </div>
                    )}
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
            </div>
          </>
        )}
      </div>

      {/* Display summary when users are assigned */}
      {hasAssignments && (
        <div className="mt-3 flex items-center gap-2">
          <UserIcon className="w-3 h-3 text-gray-400" />
          <div className="flex -space-x-2">
            {safeSelectedUsers.map(userId => {
              const user = safeUsers.find(u => u.id === userId)
              if (!user) return null
              return (
                <div
                  key={userId}
                  className="w-6 h-6 rounded-full bg-[#6A1693] text-white flex items-center justify-center text-xs font-medium border-2 border-white overflow-hidden"
                  title={user.fullName || user.email || 'Unknown User'}
                >
                  {user.avatarUrl ? (
                    <img
                      src={user.avatarUrl}
                      alt={user.fullName || 'User'}
                      className="w-full h-full object-cover"
                    />
                  ) : (
                    user.fullName ? user.fullName.charAt(0).toUpperCase() : user.email?.charAt(0).toUpperCase() || '?'
                  )}
                </div>
              )
            })}
          </div>
        </div>
      )}
    </>
  )
}
