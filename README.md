CW Tool
================

This is a repository for cw tools module


Entity Wrapper Starter Kit
============================

This module contains a starter pack for creating Entity Wrappers for nodes. CWToolEntityWrapper.php and CWToolNodeWrapper.php can be extended as demonstrated in EXAMPLE_CWToolContentTypeWrapper.php.

A typical use case might be get a "second order" piece of information. E.g. a node has a region and a region has a timezone. Using an Entity Wrapper you could get the timezone directly from the node with $node->getTimezone().