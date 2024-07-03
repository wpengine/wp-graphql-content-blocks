---
"@wpengine/wp-graphql-content-blocks": major
---

MAJOR: Update Schema to reflect latest WordPress 6.5 changes.

- WHAT the breaking change is: Added new `rich-text` type
- WHY the change was made: WordPress 6.5 replaced some of the attribute types from string to `rich-text` causing breaking changes to the existing block fields.
- HOW a consumer should update their code: If users need to use WordPress >= 6.5 they need to update this plugin to the latest version and update their graphql schemas.
