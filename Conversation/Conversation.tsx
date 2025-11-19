import React, { useEffect, useRef, useState } from 'react'
import { fetchConversation } from '../../api/conversations';
import LoadingSpinnerDark from '../LoadingSpinnerDark';
import ConversationComment from './ConversationComment';
import ConversationPostBoxWithUploads from './ConversationPostBoxWithUploads';
import { fetchUsers } from '../../api/users';

function sortByDateDesc(arr, key) {
  return [...arr].sort((a, b) => {
    const dateA = new Date(a[key] as string).getTime();
    const dateB = new Date(b[key] as string).getTime();
    return dateB - dateA;
  });
}

export default function Conversation(
  {
      viewName,
      entityId,
      approvalMode = false,
      onApprove,
      onReject,
      onPending
  }: {
      viewName: string;
      entityId: string;
      approvalMode?: boolean;
      onApprove?: (commentId: string) => Promise<void>;
      onReject?: (commentId: string) => Promise<void>;
      onPending?: (commentId: string) => Promise<void>;
  }
) {

  const [isLoading, setIsLoading] = useState(false)
  const [error, setError] = useState('')
  const [conversation,setConversation] = useState(null)
  const [comments,setComments] = useState([])
  const [users,setUsers] = useState([])
  const [tick, setTick] = useState(0);


  const loadData = async () => {
    setError(null);
    try {
      const apiResponse = await fetchConversation({viewName,entityId});
      if (apiResponse) {
          setConversation(apiResponse)        
          setComments(apiResponse.comments)  
      } else {
          setError("Conversation not found.");
      }
      const apiResponseUsers = await fetchUsers()
      if (apiResponseUsers) {
          setUsers(apiResponseUsers)        
      } else {
          setError("Failed to fetch users.");
      }
    } catch (error) {
      console.error("Failed to fetch user data:", error);
      setError("Failed to load user data.");
    } finally {
      setIsLoading(false);
    }
  };

  useEffect(() => {
    loadData();
  },[]);

  useEffect(() => {
    const id = setInterval(() => setTick(t => t + 1), 60_000);
    return () => clearInterval(id);
  }, []);


  if(!conversation || isLoading) return <LoadingSpinnerDark/> 

  const sortedConversation = sortByDateDesc(comments, "createdAt");

  if(error) {
    return error
  }

  return (
    <div className="full">
      <div className="w-full">
        <div className="full">
          <div className="pt-4 pb-0 w-full">
            <div className="space-y-4 w-full bg-white rounded-t-[16px]">
              <ConversationPostBoxWithUploads
                conversation={conversation}
                users={users}
                loadData={loadData}
                entityId={entityId}
                viewName={viewName}
              />
            </div>
          </div>
          {sortedConversation.length > 0 && (
            <div className="divide-y divide-gray-200 pt-6 bg-white">
            { sortedConversation.map( (comment) => (
            <div key={comment.id} className="even:bg-[#F9F9F9] odd:bg-white ">
              <ConversationComment
                comment={comment}
                setComments={setComments}
                approvalMode={approvalMode}
                onApprove={onApprove}
                onReject={onReject}
                onPending={onPending}
              />
            </div>
            )) }
          </div>
          )}
          
        </div>
      </div>
    </div>
  )
}

