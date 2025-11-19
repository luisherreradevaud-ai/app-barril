'use client';

import { Dialog, Transition } from '@headlessui/react';
import { Fragment } from 'react';
import { XMarkIcon } from '@heroicons/react/24/outline';

interface TaskBigModalProps {
  open: boolean;
  onClose(): void;
  children: React.ReactNode;
  headerActions?: React.ReactNode;
}

export default function TaskBigModal({
  open,
  onClose,
  children,
  headerActions,
}: TaskBigModalProps) {
  return (
    <Transition as={Fragment} show={open}>
      <Dialog as="div" className="relative z-25" onClose={onClose}>
        <div className="fixed inset-0 duration-600 backdrop-blur-[1px]" />

        {/* Contenedor centrado */}
        <div className="fixed inset-0 flex items-center justify-center">
          {/* Modal con margen de 16px en todo el viewport */}
          <div
            className={`
              fixed inset-8
              transform overflow-scroll bg-white shadow-2xl
              transition-all duration-600
              opacity-0 translate-y-4 sm:scale-95
              data-[open=true]:opacity-100
              data-[open=true]:translate-y-0
              data-[open=true]:sm:scale-100
               bg-gray-50
            `}
            data-open={open}
            onClick={(e) => e.stopPropagation()}
          >
            <div className="px-6 py-5">
              {/* Header with custom actions and close button */}
              <div className="flex items-center justify-end gap-2 px-6 py-4">
                {headerActions}
                <button
                  onClick={onClose}
                  className="p-2 text-[#6A1693] hover:text-[#50106F] transition-colors rounded-lg hover:bg-white/50 cursor-pointer"
                  title="Close"
                >
                  <XMarkIcon className="h-5 w-5" />
                </button>
              </div>
              {children}
            </div>
          </div>
        </div>
      </Dialog>
    </Transition>
  );
}
