This module provides examples of working Rules. These may be useful when
learning Rules or as a form of documentation for Rules Events, Conditions,
Actions, DataFilters, etc. 


Guidelines for contributing examples to the rules_examples module:

1) Example must not be a duplicate. Please search existing examples first,
   before submitting a new example.

2) Examples are distributed as exports of a Reaction Rule configurations. Use
   the UI to export your configuration.

3) Configurations must be disabled by default, by setting the status key to be
   false. This ensures that sites will not be impacted by newly-active Rules.
   Each example should need to be explicitly enabled if desired.

4) Configurations must contain a URL to the documentation or issue where the
   configuration is described. This URL should be in the description field.
   Example Rules without documentation will not be accepted. Documentation may
   be added by anyone at:
       https://www.drupal.org/docs/8/modules/d8-rules-essentials/examples

5) Configuration must include the tag 'rules_examples' so that the examples
   provided by this module may be easily identified in the UI.

6) Configurations must have dependencies added. This has to be done manually.
   For example, if a configuration requires an evevent/condition/or action
   provided by the rules_ban module, then the dependencies block in the
   configuration should look like this:

       dependencies:
         module:
           - rules_ban
         enforced:
           module:
             - rules_ban

7) Configurations must be distributed in rules_examples/config/optional so that
   they are not installed unless dependencies are met, and are not uninstalled
   when rules_examples is uninstalled unless there is an explicit dependency on
   the rules_examples module.
