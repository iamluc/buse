BUSE
====

About
-----
buse helps managing and executing git command in several repositories at the same time.

buse is currently 'work in progress'. Command and arguments could change.

Install
-------

```sh
git clone https://github.com/iamluc/buse.git
cd buse
composer install
```

Use
---

```sh
bin/buse.php

# or if you have build the phar (see build section)
bin/buse.phar
```

It should display the help message.
To have description of a specific command, just type:
```
$> bin/buse.php help the_command
```

All commands take as first argument an optional path.
i.e.

```
$> bin/buse.php status ../other/path
```

## status

Get status of your repositories

```
$> bin/buse.php status ..
buse: master                                                                                                                     
repo2: master / not clean (1 staged, 3 modified)
repo3: dev / not synchronized (1 ahead, 1 behind)
```

## fetch

Fetch your repositories.

## pull

Pull your repositories.

## push

Push your repositories.

## exec

Execute a git command in your repositories (do not specify 'git' in your command).

```
$> bin/buse.php exec "log --pretty=oneline -1" ..
buse:
xxxxxxxxxxxx Last commit message buse

repo2:
xxxxxxxxxxxx Last commit message repo2

repo3:
xxxxxxxxxxxx Last commit message repo3
```

## tag

Get and create tags.

## config

Get and set configuration.

To exclude repositories, update your config typing:

```
$> bin/buse.php config .. "repositories.exclude" "repo1,repo2"
```

Build
-----

```sh
bin/compile.php
```
