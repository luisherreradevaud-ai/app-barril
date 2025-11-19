import React from "react";
import Link from "next/link";
import { TOKEN_REGEX } from "./constants";
import { PURPLE_PRIMARY } from "../../variables/Colors";

interface RenderedMentionsProps {
  text: string;
}

export function RenderedMentions({ text }: RenderedMentionsProps) {
  const nodes: React.ReactNode[] = [];
  let lastIndex = 0;
  let match: RegExpExecArray | null;
  
  while ((match = TOKEN_REGEX.exec(text)) !== null) {
    // Support both formats: @[Name](user:id) and @Name{id}
    const [full, fullName1, id1, fullName2, id2] = match;
    const fullName = fullName1 || fullName2;
    const id = id1 || id2;
    const start = match.index;
    const end = start + full.length;
    
    if (start > lastIndex) {
      nodes.push(<span key={`t-${lastIndex}`}>{text.slice(lastIndex, start)}</span>);
    }
    
    // Display as clickable link showing only @Name (hiding the {id} portion completely)
    nodes.push(
      <Link
        key={`m-${start}`}
        href={`/users/details/${id}`}
        className={"text-[" + PURPLE_PRIMARY + "] font-medium hover:underline"}
      >
        @{fullName}
      </Link>
    );
    lastIndex = end;
  }
  
  if (lastIndex < text.length) {
    nodes.push(<span key={`t-${lastIndex}`}>{text.slice(lastIndex)}</span>);
  }
  
  return <>{nodes}</>;
}
