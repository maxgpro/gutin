// Centralized UI defaults for pagination
// Usage: component props can override these, but these act as app-wide defaults.
export const paginationConfig = {
    // Number of sibling pages to show around the current page
    siblingCount: 1,
    // Always show first and last page with ellipsis when needed
    showEdges: true,
    // Query param name used for page in URLs
    pageParam: 'page',
    // Available per-page options
    perPageOptions: [6, 12, 24, 48] as const,
    // Default per page value
    defaultPerPage: 6,
    // Query param name for per page
    perPageParam: 'per_page',
} as const;
