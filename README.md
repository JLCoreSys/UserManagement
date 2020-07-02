Core Systems User Management
===================


Introduction
------------

TBD

Installation
------------
```
// composer.json
...
"repositories": [
    ...
    {
          "type": "vcs",
          "url": "https://CoreSys@bitbucket.org/jlcoresys/reverse-discriminator.git"
        },
        {
          "type": "vcs",
          "url": "https://CoreSys@bitbucket.org/jlcoresys/user-management.git"
        }
    ...
]
...
```

```bash
composer require coresys/user-managment
```

```
// config/packages/security.yaml
encoders:
    CoreSys\UserManagement\Entity\User:
        algorithm: auto
```