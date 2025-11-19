// Matches both formats: @[Name](user:id) and @Name{id}
// Updated to handle any characters in names (including accents, apostrophes, etc.)
export const TOKEN_REGEX = /@(?:\[(.+?)\]\(user:([^)]+)\)|([^{]+?)\{([^}]+)\})/g;
