# Changelog

All notable changes to this project are documented in this file.

## v1.0.5 - 2026-04-26

### Security and authorization
- Tightened clearance authorization so staff can only act on checklist items assigned to their own office role.
- Restricted approve, reject, and bulk-approve operations to role-scoped checklist items instead of broad admin-wide updates.
- Added a dedicated checklist-item authorization helper in the staff clearance controller for consistent access control.

### Staff clearance workflow
- Updated checklist item update flow to enforce role ownership before status changes.
- Improved unauthorized handling for mismatched clearance-item access.

### Staff clearance UI
- Refined the staff clearance table layout with improved readability and metadata presentation.
- Added role-aware UI behavior so action buttons and selection checkboxes only appear when the user can act.
- Added collapsible checklist display with "show more" behavior for long checklist lists.
- Enhanced student, department, office, location, and request-date visual presentation.
- Improved empty-state labels for remarks and action cells.
