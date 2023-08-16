---
"@wpengine/wp-graphql-content-blocks": patch
---

Refactored `register_block_types` to remove usages of `register_graphql_interfaces_to_types` to improve performance.
**MINOR BREAKING** Removed `Anchor::register_to_block` public static method.
