---
"@wpengine/wp-graphql-content-blocks": major
---

Fix: use `use_block_editor_for_post_type` instead of `post_type_supports` when filtering the post types.
**BREAKING**: Potential schema changes on previously exposed blocks that do not support the block editor. Those blocks will no longer inherit the `editorBlocks` field.
