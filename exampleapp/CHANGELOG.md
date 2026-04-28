# Changelog

All notable changes to this project are documented in this file.

## v1.0.10 - 2026-04-28

### Central app hotfix
- Fixed merge-conflict markers that were breaking the super-admin tenants and subscriptions pages.
- Aligned the central pricing catalog to Premium so the plan UI and request flow match the v1.0.7 release.
- Kept legacy Enterprise plan requests compatible while normalizing them to Premium.

## v1.0.7 - 2026-04-27

### Plan enforcement and student limits
- Enforced strict student caps per subscribed plan across all tenants: `Basic` (500), `Standard` (2000), and `Premium` (unlimited).
- Added server-side plan-limit checks in both admin student creation and self-registration flows to block over-cap student creation.
- Corrected tenant limit handling so plans with `max_students = null` are treated as unlimited instead of blocked.

### Plan catalog consistency
- Updated tenant plan request flow to use canonical pricing per selected plan and keep `Premium` fixed at `₱20,000.00` on submission.
- Aligned central app plan-request validation and normalization to accept `Premium` while preserving legacy `Enterprise` compatibility.
- Updated central plan seed data and super-admin plan UI labels to use `Premium` naming consistently.

### Tenant-aware exports
- Updated report PDF/CSV export branding to use the active tenant name instead of a hardcoded school name.
- Rebuilt report export controller logic to resolve tenant identity consistently for generated reports.

## v1.0.6 - 2026-04-26

### Update system reliability
- Fixed the Admin App Update page showing an older `Latest Version` after a new GitHub release was already published.
- Added stale-response protection in the update service so the displayed latest version will never be lower than the running current version.
- Added force-refresh support for update status checks to bypass cached data when needed.

### Admin update page UX
- Added a `Refresh Latest Version` button on the update screen to immediately re-check GitHub release/tag data.
- Wired the update controller to support manual refresh via query flag and fresh status retrieval.

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
