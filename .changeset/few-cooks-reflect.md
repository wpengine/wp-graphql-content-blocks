---
"@wpengine/wp-graphql-content-blocks": minor
---

fix: prevent fatal errors by improving type-safety and returning early when parsing HTML.
The following methods have been deprecated for their stricter-typed counterparts:
 - `DOMHelpers::parseAttribute()` => `::parse_attribute()`
 - `DOMHelpers::parseFirstNodeAttribute()` => `::parse_first_node_attribute()`
 - `DOMHelpers::parseHTML()` => `::parse_html()`
 - `DOMHelpers::getElementsFromHTML()` => `::get_elements_from_html()`
 - `DOMHelpers::parseText()` => `::parse_text()`
 - `DOMHelpers::findNodes()`=> `::find_nodes()`
