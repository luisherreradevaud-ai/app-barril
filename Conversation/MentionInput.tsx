'use client'
import React, { useEffect, useMemo, useRef, useState } from "react";
import { TOKEN_REGEX } from "./constants";
import { PURPLE_PRIMARY } from "../../variables/Colors";

interface User {
  id: string;
  fullName: string;
  avatarUrl: string;
}

interface MentionInputProps {
  users: any[];
  value: string;
  onChange: (value: string) => void;
  onTagsChange?: (tags: User[]) => void;
  placeholder?: string;
  className?: string;
  rows?: number;
}

export function MentionInput(
  { 
    users, 
    value,
    onChange, 
    onTagsChange, 
    placeholder, 
    className, 
    rows = 2 
  }: MentionInputProps
) {
  const textareaRef = useRef<HTMLTextAreaElement | null>(null);
  const [isOpen, setIsOpen] = useState(false);
  const [highlightIndex, setHighlightIndex] = useState(0);
  const [triggerIndex, setTriggerIndex] = useState<number | null>(null);
  const [caret, setCaret] = useState(0);
  const taggedUsersRef = useRef<User[]>([]);

  const query = useMemo(() => {
    if (triggerIndex == null) return "";
    if (caret < triggerIndex) return "";
    const slice = value.slice(triggerIndex + 1, caret);
    if (/\s/.test(slice)) return "";
    return slice;
  }, [value, triggerIndex, caret]);

  const filtered = useMemo(() => {
    if (triggerIndex == null) return [];
    const q = query.trim().toLowerCase();
    return users.filter((u) => u.fullName.toLowerCase().includes(q)).slice(0, 8);
  }, [users, query, triggerIndex]);

  useEffect(() => {
    const nextOpen = triggerIndex != null && filtered.length > 0;
    setIsOpen((prev) => (prev !== nextOpen ? nextOpen : prev));
    setHighlightIndex((idx) => {
      if ((!isOpen && nextOpen) || idx >= filtered.length) return 0;
      return idx;
    });
  }, [triggerIndex, filtered.length, isOpen]);

  const lastTagsKeyRef = useRef("");
  useEffect(() => {
    const tokens: { id: string; fullName: string }[] = [];
    
    // Check for old format with IDs
    value.replace(TOKEN_REGEX, (match, fullName1, id1, fullName2, id2) => {
      const fullName = fullName1 || fullName2;
      let id = id1 || id2;
      
      if (fullName && id) {
        tokens.push({ id, fullName });
      }
      return "";
    });
    
    // Check for plain @Name format (new clean format)
    // Match @Name where Name can contain letters, spaces, apostrophes, hyphens, accents, etc.
    // Improved regex to handle names like "María O'Brien-Smith"
    users.forEach(user => {
      const escapedName = user.fullName.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
      const userRegex = new RegExp(`@${escapedName}(?!\\{)`, 'g');
      if (userRegex.test(value) && !tokens.some(t => t.id === user.id)) {
        tokens.push({ id: user.id, fullName: user.fullName });
      }
    });
    
    const idSet = new Set(tokens.map(t => t.id));
    const nextTags = users.filter(u => idSet.has(u.id));
    taggedUsersRef.current = nextTags;
    
    const key = nextTags.map(u => u.id).join("|");
    if (key !== lastTagsKeyRef.current) {
      lastTagsKeyRef.current = key;
      onTagsChange?.(nextTags);
    }
  }, [value, users, onTagsChange]);

  const updateCaretFromTextarea = () => {
    const el = textareaRef.current;
    if (!el) return;
    setCaret(el.selectionStart);
  };

  const handleChange = (e: React.ChangeEvent<HTMLTextAreaElement>) => {
    const newVal = e.target.value;
    onChange(newVal);
    const el = textareaRef.current;
    const curCaret = el ? el.selectionStart : newVal.length;
    setCaret(curCaret);
    const left = newVal.slice(0, curCaret);
    let atIndex = left.lastIndexOf("@");
    if (atIndex === -1) {
      setTriggerIndex(null);
      return;
    }
    const charBefore = atIndex > 0 ? left[atIndex - 1] : "";
    const isTrigger = atIndex === 0 || /[\s\n\t\r.,;:!?\-()\[\]{}]/.test(charBefore);
    if (!isTrigger) {
      setTriggerIndex(null);
      return;
    }
    const after = left.slice(atIndex + 1);
    if (/\s/.test(after)) {
      setTriggerIndex(null);
      return;
    }
    setTriggerIndex(atIndex);
  };

  const closeDropdown = () => {
    setIsOpen(false);
    setTriggerIndex(null);
  };

  const insertUser = (user: { id: string; fullName: string }) => {
    if (triggerIndex == null) return;
    const before = value.slice(0, triggerIndex);
    const after = value.slice(caret);
    // Clean format: just @Name visible to user
    const insertion = `@${user.fullName} `;
    const next = before + insertion + after;
    onChange(next);
    
    requestAnimationFrame(() => {
      const el = textareaRef.current;
      if (el) {
        const newPos = before.length + insertion.length;
        el.focus();
        el.setSelectionRange(newPos, newPos);
        setCaret(newPos);
      }
    });
    closeDropdown();
  };

  const onKeyDown = (e: React.KeyboardEvent<HTMLTextAreaElement>) => {
    if (!isOpen) return;
    if (e.key === "ArrowDown") {
      e.preventDefault();
      setHighlightIndex((i) => (i + 1) % filtered.length);
    } else if (e.key === "ArrowUp") {
      e.preventDefault();
      setHighlightIndex((i) => (i - 1 + filtered.length) % filtered.length);
    } else if (e.key === "Enter") {
      e.preventDefault();
      const choice = filtered[highlightIndex];
      if (choice) insertUser(choice);
    } else if (e.key === "Escape") {
      e.preventDefault();
      closeDropdown();
    }
  };

  return (
    <div className="relative">
      <textarea
        ref={textareaRef}
        className={className || "w-full min-h-[140px] resize-y rounded-2xl border bg-white p-4 shadow-sm outline-none focus:ring-2 focus:ring-black/10"}
        rows={rows}
        value={value}
        onChange={handleChange}
        onKeyDown={onKeyDown}
        onClick={updateCaretFromTextarea}
        onKeyUp={updateCaretFromTextarea}
        placeholder={placeholder}
      />
      {isOpen && filtered.length > 0 && (
        <div className="absolute left-2 right-2 -bottom-1 translate-y-full z-20">
          <div className="rounded-2xl border border-gray-300 bg-white shadow-xl overflow-hidden">
            <ul role="listbox" className="max-h-64 overflow-auto">
              {filtered.map((u, idx) => (
                <li
                  key={u.id}
                  role="option"
                  aria-selected={idx === highlightIndex}
                  onMouseDown={(e) => e.preventDefault()}
                  onClick={() => insertUser(u)}
                  className={
                    "flex items-center gap-3 px-3 py-2 cursor-pointer text-sm " +
                    (idx === highlightIndex ? "bg-gray-100" : "hover:bg-gray-50")
                  }
                >
                  {u.avatarUrl ? (
                    <img src={u.avatarUrl} alt={u.fullName} className="h-6 w-6 rounded-full" />
                  ) : (
                    <div className={"bg-[" + PURPLE_PRIMARY + "] flex items-center justify-center w-6 h-6 text-white font-bold rounded-full text-xs"}>
                      {u.fullName[0]?.toUpperCase()}
                    </div>
                  )}
                  
                  <div className="flex-1 min-w-0">
                    <p className="truncate">{u.fullName}</p>
                    <p className="text-xs text-gray-500 truncate">{u.id}</p>
                  </div>
                  <span className="text-xs text-gray-400">@</span>
                </li>
              ))}
            </ul>
          </div>
        </div>
      )}
    </div>
  );
}
