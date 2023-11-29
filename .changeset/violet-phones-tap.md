---
"@wpengine/wp-graphql-content-blocks": patch
---

Interface Types are now registered with the Post Type's `graphql_single_name`, instead of the Post Type's `name`. Fixes a bug where invalid Types were registered.
