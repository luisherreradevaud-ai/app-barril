import { fetchWithAuth } from "./common";

export async function fetchConversation(
    data: {
        viewName: string;
        entityId: string;
    }
) {
  try {
    const url = `${process.env.NEXT_PUBLIC_API_BASE_URL}/internal-conversations/view/${data.viewName}/${data.entityId}`;
    const response = await fetchWithAuth(url, { method: "GET" });
    if (!response) {
      throw new Error(`Error fetching events.`);
    } else {
      return response;
    }
  } catch (error: any) {
    console.error("Failed to fetch email templates:", error);
    return [];
  }
}

export async function postComment(
  conversationId: string,
  comment: string,
  files: any[],
  tags: any[],
  viewNameUrl: string
) {

  const apiUrl = `${process.env.NEXT_PUBLIC_API_BASE_URL}/internal-conversations/${conversationId}/comments-with-files`;
  const method = 'POST';

  const commentData = {
    content: comment,
    status: 'Active',
    tags: tags,
    viewNameUrl: viewNameUrl
  }

  const formData = new FormData()
  formData.append('comment', JSON.stringify(commentData))

  let i = 1
  for(const file of files) {
    console.log('file',file)
    formData.append('file' + i, file.file)
    formData.append('file' + i, JSON.stringify(
      {
        name: file.displayName,
        description: '',
        status: 'Active',
        metadata: {
          size: file.file.size,
          type: file.file.type
        }
      }
    ))
    i++
  }

  try {
    const response = await fetchWithAuth(apiUrl, {
      method: method,
      body: formData,
    });
    if(response) {
      return response;
    } else {
      return null;
    }
  } catch (error) {
    return null;
  }
}


export async function deleteConversationComment(
  id: string
) {
  const apiUrl = `${process.env.NEXT_PUBLIC_API_BASE_URL}/internal-conversations/comments/${id}`;
  try {
    const response = await fetchWithAuth(apiUrl, { method: "DELETE" });
    if (!response) {
      return true
    }
    return response; 
  } catch (error) {
    console.error(`Failed to delete vendor ${id}:`, error);
    return null
  }
}