gitlab-client
=============

[![Build Status](https://travis-ci.org/Seretos/gitlab-client.svg?branch=master)](https://travis-ci.org/Seretos/gitlab-client.svg?branch=master)
[![Coverage Status](https://coveralls.io/repos/github/Seretos/gitlab-client/badge.svg)](https://coveralls.io/github/Seretos/gitlab-client?branch=master)

this package provide two console commands to automate creation of releases/tags with gitlab

Installation
------------

globally install the phar:

```php
wget https://github.com/Seretos/gitlab-client/releases/download/v0.1.1/gitlab-client.phar
chmod +x gitlab-client.phar
sudo mv gitlab-client.phar /usr/local/bin/gitlab-client
```

or add the require to your composer project:

```php
composer require seretos/gitlab-client
```

Usage
-----
if globally installed:

```php
gitlab-client list
```

or on composer installation:

```php
php vendor\bin\gitlab-client list
```

build:child command
-------------------

example:

```php
gitlab-client build:child --server-url http://your.gitlab.api/api/v3/ --auth-token yourUserToken --repository yourRepositoryName --branch yourBranch
```

this command create new branches/tags from the given branch name.

for example. if you execute this command with the branch master, the command show which branches exists. if a branch 0 exists,
the command generate a new branch 1 from master. if 1 exists, the command create the branch with name 2 and so on.
if you execute this command with branch 1,2 or some one else single numeric branch, the command generate a new branch like 1.0,0.2.
if you execute the command from an branch like 1.0, the command create a tag named v1.0.0 or v1.0.1...

protect:branch command:
-----------------------

example:

```php
gitlab-client protect:branch --server-url http://your.gitlab.api/api/v3/ --auth-token yourUserToken --repository yourRepositoryName --branch yourBranch
```

this command set the given branch to protected.

coverage:check command:
-----------------------

example:

```php
phpunit --bootstrap vendor/autoload.php --configuration phpunit.xml.dist --coverage-clover build/logs/clover.xml
gitlab-client coverage:check --clover-file path/to/your/clover.xml --percentage 100
```

this command checks, that the code coverage is greater then the given percentage.

copy:members command:
---------------------

```php
gitlab-client copy:members --server-url http://your.gitlab.api/api/v3/ --auth-token yourUserToken --source-group yourSourceGroup --destination-group yourDestinationGroup
```

this command add all users which found in the source group, but not exist in the destination group

example usage in gitlab-ci.yml:
------------------------------

```yml
test:
    script:
        - phpunit --bootstrap vendor/autoload.php --configuration phpunit.xml.dist --coverage-clover build/logs/clover.xml
        - gitlab-client coverage:check --clover-file path/to/your/clover.xml --percentage 100
release:
    script:
        - gitlab-client protect:branch --server-url http://$CI_SERVER_NAME/api/v3/ --auth-token yourToken --repository $CI_PROJECT_NAME --branch $CI_BUILD_REF_NAME
        - gitlab-client build:child --server-url http://$CI_SERVER_NAME/api/v3/ --auth-token yourToken --repository $CI_PROJECT_NAME --branch $CI_BUILD_REF_NAME
    only:
        - /^(master|\d+(.\d+)?)$/
```
