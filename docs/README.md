# Overview

The Core Connector Textfile provides an _Authorization Data Provider_, which retrieves user attributes
used for access control from the bundle's config file.

## Configuration

```yaml
dbp_relay_core_connector_textfile:
  # used to define user groups
  groups:
    - name: DEVELOPERS
      users:
        - junior
        - senior

  # used to declare available attributes
  attributes:
    - name: ROLE_DEVELOPER
      default_value: false # default value: 'null' for scalar and '[]' for array attributes
    - name: ORGANIZATION_UNITS
      array: true # default value: 'false'

  # used to define values for the attributes
  attribute_mapping:
    - name: ROLE_DEVELOPER
      groups:
        - DEVELOPERS
      value: true
    - name ORGANIZATION_UNITS
      groups:
        - DEVELOPERS
      values:
        - 1
        - 2
    - name ORGANIZATION_UNITS
      users:
        - foo
      values:
        - 3

```

* The ```groups``` node is used to define a user groups with a group ```name``` 
and a list of ```users```(user identifiers).
* The ```attributes``` node is used to declare available user attributes given 
an attribute ```name``` and an optional ```default_value``` and ```array``` type declaration
* The ```attribute_mapping``` node is used to define attribute values:
Each mapping entry specifies a ```value``` (or ```values``` for array type attributes) 
for attribute ```name``` for one or many ```users``` and/or ```groups```.