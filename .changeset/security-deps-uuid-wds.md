---
"@wpengine/wp-graphql-content-blocks": patch
---

Patch development-only security advisories by overriding transitive `uuid` (→ ^11.1.1) and `webpack-dev-server` (→ ^5.2.4). These bumps affect build/test tooling only and do not change any code shipped to consumers of the plugin.
