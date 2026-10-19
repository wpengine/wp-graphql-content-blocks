---
"@wpengine/wp-graphql-content-blocks": patch
---

Fixed `textAlign` disappearing from `CoreHeading` and `CoreButton` on WordPress 7.0, where text alignment moved from a block attribute to a block support. Any block with `supports.typography.textAlign`, including `CoreParagraph`, now exposes `textAlign`, read from `style.typography.textAlign` and falling back to the top-level attribute for content saved before WordPress 7.0.
