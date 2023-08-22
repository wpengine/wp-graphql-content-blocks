---
"@wpengine/wp-graphql-content-blocks": patch
---

Refactored `register_block_types` to remove usages of `register_graphql_interfaces_to_types` to improve performance.
<<<<<<< HEAD
**MINOR BREAKING** Removed `Anchor::register_to_block` public static method.
=======

Deprecated `Anchor::register_to_block` public static method.
>>>>>>> 989f243 (Chore: Deprecate `register_to_block` instead.)
