BUSE
====

About
-----
buse helps managing and executing git commands in several git repositories at the same time.

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
bin/buse

# or if you have build the phar (see build section)
bin/buse.phar
```

It should display the help message.
To have description of a specific command, just type:
```
$> bin/buse help the_command
```

## status

Get status of your repositories

```
$> bin/buse status
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

Execute a git command in your repositories.

Note:
- 'git' must not be included in the command
- you must write the command after '--'

```
$> bin/buse exec -- log --pretty=oneline -1
buse:
xxxxxxxxxxxx Last commit message buse

repo2:
xxxxxxxxxxxx Last commit message repo2

repo3:
xxxxxxxxxxxx Last commit message repo3
```

## tag

Get and create tags.

## clone

Clone repositories. *This command needs a configuration file `.buse.yml`*

## config

Get and set configuration.

To exclude repositories, update your config typing:

```
$> bin/buse config "global.ignore_repositories" "repo1,repo2"
```

Configuration
-------------

Buse will check if a file `.buse.yml` exists in the current directory.
Note: You can use the `--config` (ie. `buse status --config ~/my-dir`) option to change the directory of the config file.

A basic `.buse.yml` looks like:
```yaml
global:
    ignore_repositories:
        - workshop-serializer-todo
        - twgit

datatheke:
    repositories:
        datatheke: 'git@github.com:datatheke/datatheke.git'
        datatheke-cli: 'git@github.com:datatheke/datatheke-cli.git'

buse:
    repositories:
        buse: 'git@github.com:iamluc/buse.git'

other:
    repositories:
        super-project: ~
```

The `global` section contains the global configuration of buse.

Others sections are called "groups".

If you call buse with the `--group` option (ie. `buse status --group datatheke --group buse`),
the command will be executed only for the repositories defined in the selected groups.

Without any `--group` option, buse will search all git repositories in the working directory.

Build
-----

```sh
composer install --no-dev
bin/compile
```
